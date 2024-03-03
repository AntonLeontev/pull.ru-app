<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Domain\Synchronizer\Jobs\SyncOrderStatus;
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
            dispatch(new SyncOrderStatus($order));
        }
    }
}
