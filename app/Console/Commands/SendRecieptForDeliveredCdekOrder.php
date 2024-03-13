<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Domain\Synchronizer\Jobs\SendRecieptForDeliveredCdekOrder as JobsSendRecieptForDeliveredCdekOrder;
use Src\Domain\Synchronizer\Models\Order;

class SendRecieptForDeliveredCdekOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reciept {order_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->argument('order_id')) {
            $id = $this->ask('Order id?');
            $order = Order::find($id);
        } else {
            $order = Order::find($this->argument('order_id'));
        }

        if (is_null($order)) {
            $this->info('Нет такого id');

            return;
        }

        dispatch(new JobsSendRecieptForDeliveredCdekOrder($order));
    }
}
