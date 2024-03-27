<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\MoySklad\MoySkladApi;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Models\Order;

class CancelOrderFromInsales
{
    public function __construct(public ResolveDiscount $resolveDiscount)
    {
    }

    public function handle(array $request): void
    {
        $request = objectize($request);

        $order = Order::where('insales_id', $request->id)->first();

        $order->update(['status' => OrderStatus::canceled]);

        MoySkladApi::updateCustomerOrder($order->moy_sklad_id, ['state' => OrderStatus::canceled->toMS()]);
    }
}
