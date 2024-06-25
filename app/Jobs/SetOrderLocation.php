<?php

namespace App\Jobs;

use App\Services\InSales\Exceptions\InsalesRateLimitException;
use App\Services\InSales\InSalesApi;
use App\Services\InSales\InsalesApiService;
use App\Services\Ip2location\Ip2LocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Models\Order;

class SetOrderLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(Ip2LocationService $ip2LocationService): void
    {
        try {
            $ip = collect(
                InSalesApi::getOrder($this->order->insales_id)
                    ->json('fields_values')
            )->where('handle', 'ip_address')
                ->pluck('value')
                ->first();

            $locationDTO = $ip2LocationService->location($ip);

            InsalesApiService::updateLocationByIp($this->order->insales_id, $locationDTO);
        } catch (\Throwable $th) {
            if ($th instanceof InsalesRateLimitException) {
                $this->release(300);
            } else {
                Log::channel('telegram')->error('Ошибка в SetOrderLocation Job. Заказ '.$this->order->number.'. '.$th->getMessage());
                $this->fail($th);
            }
        }
    }
}
