<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\CDEK\CdekApi;
use App\Services\InSales\Exceptions\InsalesRateLimitException;
use App\Services\InSales\InsalesApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Src\Domain\Synchronizer\Models\Order;

class SetKeepFreeDateToInsales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order) {}

    /**
     * Execute the job.
     */
    public function handle(InsalesApiService $service)
    {
        $keep = CdekApi::getOrder($this->order->cdek_id)->json('entity.keep_free_until');

        if (is_null($keep)) {
            return;
        }

        $date = Carbon::parse($keep);

        try {
            $service->updateKeepFreeDateInOrder($this->order->insales_id, $date->format('d.m.Y'));
        } catch (InsalesRateLimitException $e) {
            return $this->release(300);
        }
    }
}
