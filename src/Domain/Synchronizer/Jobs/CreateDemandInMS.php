<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\InSales\InSalesApi;
use App\Services\MoySklad\Entities\Counterparty;
use App\Services\MoySklad\Entities\CustomerOrder;
use App\Services\MoySklad\Entities\Variant as MSVariant;
use App\Services\MoySklad\MSApiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Models\Client;
use Src\Domain\Synchronizer\Models\Order;
use Src\Domain\Synchronizer\Models\Variant;

class CreateDemandInMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(): void
    {
        try {
            $insalesOrder = InSalesApi::getOrder($this->order->insales_id);
            $clientInsalesId = $insalesOrder->json('client.id');
            $client = Client::where('insales_id', $clientInsalesId)->first();
            $counterparty = Counterparty::make($client->moy_sklad_id);

            $customerOrder = CustomerOrder::make($this->order->moy_sklad_id);
            $positions = [];

            foreach ($insalesOrder->json('order_lines') as $line) {
                $position = [];
                $variant = Variant::where('insales_id', $line['variant_id'])->first();

                $position['quantity'] = $line['quantity'];
                $position['price'] = $line['full_sale_price'] * 100;
                $position['assortment'] = MSVariant::make($variant->moy_sklad_id);
                $position['reserve'] = $line['reserved_quantity'];

                $positions[] = $position;
            }

            MSApiService::createDemandFromDeliveredOrder($counterparty, $customerOrder, $positions);
        } catch (Exception $e) {
            Log::channel('telegram')
                ->error("Ошибка при создании отгрузки в МС.\nЗаказ: ".$this->order->number.'. Ошибка: '.$e->getMessage());
            $this->fail($e);
        }
    }
}
