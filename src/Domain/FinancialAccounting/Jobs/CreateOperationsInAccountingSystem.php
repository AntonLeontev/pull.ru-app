<?php

namespace Src\Domain\FinancialAccounting\Jobs;

use App\Services\CDEK\CdekApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\FinancialAccounting\Actions\CreateOperationsFromOrder;
use Src\Domain\FinancialAccounting\DTO\CdekOrderDTO;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Models\Order;

class CreateOperationsInAccountingSystem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $uuid)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(CreateOperationsFromOrder $createOperationsFromOrder): void
    {
        $response = CdekApi::getOrder($this->uuid);

        if ($response->json('entity.sender.company') === 'Фулфилмент' && is_null($response->json('entity.number'))) {
            return;
        }

        $orderDto = CdekOrderDTO::fromResponse($response);

        if ($orderDto->isReturn) {
            $order = Order::where('cdek_id', $orderDto->directOrderUuid)->first();

            // TODO delete
            if ($order->status == OrderStatus::returned) {
                Log::channel('telegram')->info('Дубль заказа возврата: '.$order->number);

                return;
            }

            $orderDto->number = $order?->number;
        }

        $createOperationsFromOrder->handle($orderDto);
    }
}
