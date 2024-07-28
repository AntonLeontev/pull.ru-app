<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\Cloudpayments\CloudPaymentsService;
use App\Services\Cloudpayments\Entities\Amounts;
use App\Services\Cloudpayments\Entities\CustomerReceipt;
use App\Services\Cloudpayments\Entities\Item;
use App\Services\Cloudpayments\Entities\PurveyorData;
use App\Services\Cloudpayments\Enums\AgentSign;
use App\Services\Cloudpayments\Enums\TaxationSystem;
use App\Services\InSales\InSalesApi;
use App\Services\InSales\InsalesApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Actions\ResolveDiscount;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Models\Order;

class SendRecieptForDeliveredCdekOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order) {}

    /**
     * Execute the job.
     */
    public function handle(ResolveDiscount $resolveDiscount, CloudPaymentsService $service)
    {
        if ($this->order->payment_status === OrderPaymentStatus::paid) {
            return;
        }

        if ($this->order->reciept_sent) {
            Log::channel('telegram')->warning('Попытка повторной отправки фискального чека по заказу '.$this->order->number);

            return;
        }

        $ISOrder = InSalesApi::getOrder($this->order->insales_id)->json();

        $ISOrder = objectize($ISOrder);

        $resolveDiscount->handle($ISOrder);

        $organizations = $this->getOrganizationsByBrands($ISOrder->order_lines);

        $items = [];

        foreach ($ISOrder->order_lines as $line) {
            $items[] = new Item(
                $line->title,
                $line->full_sale_price,
                $line->quantity,
                0,
                $line->full_total_price,
                AgentSign::agent,
                PurveyorData::fromConfigOrganization($organizations[$line->product_id])
            );
        }

        if ($ISOrder->full_delivery_price > 0) {
            $items[] = new Item(
                'Доставка',
                $ISOrder->full_delivery_price,
                1,
                0,
                $ISOrder->full_delivery_price,
            );
        }

        $amounts = new Amounts($ISOrder->total_price);

        $customerReceipt = new CustomerReceipt(
            $items,
            $amounts,
            TaxationSystem::usn_income_outcome,
            $ISOrder->client->email,
        );

        $service->receipt($customerReceipt, $ISOrder->number);

        $this->order->update(['reciept_sent' => true, 'payment_status' => OrderPaymentStatus::paid]);
        InsalesApiService::updateOrderPaymentState($this->order->insales_id, OrderPaymentStatus::paid->value);
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
