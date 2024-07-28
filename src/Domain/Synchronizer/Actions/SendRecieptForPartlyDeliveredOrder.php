<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\CdekApi;
use App\Services\Cloudpayments\CloudPaymentsService;
use App\Services\Cloudpayments\Entities\Amounts;
use App\Services\Cloudpayments\Entities\CustomerReceipt;
use App\Services\Cloudpayments\Entities\Item;
use App\Services\Cloudpayments\Entities\PurveyorData;
use App\Services\Cloudpayments\Enums\AgentSign;
use App\Services\Cloudpayments\Enums\TaxationSystem;
use App\Services\InSales\InSalesApi;
use App\Services\InSales\InsalesApiService;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Models\Order;
use Src\Domain\Synchronizer\Models\Variant;

class SendRecieptForPartlyDeliveredOrder
{
    public function __construct(public CloudPaymentsService $service) {}

    public function handle(Order $order)
    {
        if ($order->reciept_sent) {
            Log::channel('telegram')->warning('Попытка повторной отправки фискального чека по заказу '.$order->number);

            return;
        }

        $ISOrder = InSalesApi::getOrder($order->insales_id)->json();
        $ISOrder = objectize($ISOrder);

        $cdekOrderRequest = CdekApi::getOrder($order->cdek_id);

        $organizations = $this->getOrganizationsByBrands($ISOrder->order_lines);

        $items = [];

        foreach ($cdekOrderRequest->json('entity.packages.0.items') as $item) {
            if ($item['delivery_amount'] == 0) {
                continue;
            }

            $product = Variant::find($item['ware_key'])->product;

            $items[] = new Item(
                $item['name'],
                $item['payment']['value'] / $item['delivery_amount'],
                $item['delivery_amount'],
                0,
                $item['payment']['value'],
                AgentSign::agent,
                PurveyorData::fromConfigOrganization($organizations[$product->insales_id])
            );
        }

        if ($cdekOrderRequest->json('entity.delivery_recipient_cost.value') > 0) {
            $items[] = new Item(
                'Доставка',
                $cdekOrderRequest->json('entity.delivery_recipient_cost.value'),
                1,
                0,
                $cdekOrderRequest->json('entity.delivery_recipient_cost.value'),
            );
        }

        $amounts = new Amounts($cdekOrderRequest->json('entity.delivery_detail.payment_sum'));

        $customerReceipt = new CustomerReceipt(
            $items,
            $amounts,
            TaxationSystem::usn_income_outcome,
            $ISOrder->client->email,
        );

        $this->service->receipt($customerReceipt, $ISOrder->number);

        $order->update(['reciept_sent' => true, 'payment_status' => OrderPaymentStatus::paid]);
        InsalesApiService::updateOrderPaymentState($order->insales_id, OrderPaymentStatus::paid->value);
    }

    private function getOrganizationsByBrands(array $lines)
    {
        $productsIds = collect();

        foreach ($lines as $line) {
            $productsIds->push($line->product_id);
        }

        $organizations = [];

        foreach ($productsIds->unique() as $id) {
            $characteristics = collect(InSalesApi::getProduct($id)->json('characteristics'));

            $brand = $characteristics->first(fn ($el) => $el['property_id'] == config('services.inSales.brand_property_id'));

            $organization = organization_by_brand_id($brand['id']);
            $organizations[$id] = $organization;
        }

        return $organizations;
    }
}
