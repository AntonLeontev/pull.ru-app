<?php

namespace App\Console\Commands;

use App\Services\CDEK\CdekApi;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Exceptions\FullfillmentOrderNotCreated;
use Src\Domain\Synchronizer\Exceptions\FullfillmentOrderNotCreatedOnce;
use Src\Domain\Synchronizer\Models\Order;

class SyncOrdersStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:orders-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Забирает статусы заказов из сдека';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::whereIn('status', ['approved', 'dispatched'])->get();

        foreach ($orders as $order) {
            $this->syncOrder($order);
        }
    }

    private function syncOrder(Order $order): void
    {
        if ($order->fullfillment_id === null) {
            $cdekNumber = CdekApi::getOrder($order->cdek_id)->json('entity.cdek_number');

            $order->update(['fullfillment_id' => $cdekNumber]);
        }

        try {
            $ffState = $this->tryGetState($order);
        } catch (FullfillmentOrderNotCreatedOnce $e) {
            return;
        } catch (FullfillmentOrderNotCreated $e) {
            Log::channel('telegram')->warning("Заказ {$order->number} оформлен {$order->created_at->format('d.m.y H:i')} но не добавлен в ФФ");

            return;
        }

        if ($ffState === 'pending_queued') {
            return;
        }
        if (in_array($ffState, ['pending_error', 'pending', 'partly_reserved'])) {
            Log::channel('telegram')->warning("Заказ №{$order->number}. Ошибки в ФФ");

            return;
        }

        $newStatus = OrderStatus::fromCdek($ffState);

        if ($newStatus === $order->status) {
            return;
        }

        DB::transaction(function () use ($order, $newStatus) {
            $this->setAppStatus($order, $newStatus);
            $this->setMSStatus($order, $newStatus);
            $this->setInsalesStatus($order, $newStatus);
        });
    }

    private function setAppStatus(Order $order, OrderStatus $newStatus): void
    {
        $order->update(['status' => $newStatus]);
    }

    private function setMSStatus(Order $order, OrderStatus $newStatus): void
    {
        MoySkladApi::updateCustomerOrder($order->moy_sklad_id, ['state' => $newStatus->toMS()]);
    }

    private function setInsalesStatus(Order $order, OrderStatus $newStatus): void
    {
        InSalesApi::updateOrderState($order->insales_id, $newStatus->toInsales());
    }

    private function tryGetState(Order $order): string
    {
        $response = FullfillmentApi::getOrderByExtId($order->fullfillment_id);

        if ($response->json('total_items') === 0) {
            $order->increment('tries');

            if ($order->tries > 1) {
                throw new FullfillmentOrderNotCreated();
            } else {
                throw new FullfillmentOrderNotCreatedOnce();
            }
        }

        return $response->json('_embedded.order.0.state');
    }
}
