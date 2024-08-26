<?php

namespace Src\Domain\DiscountSystem\Jobs;

use App\Services\MoySklad\MoySkladApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Src\Domain\Synchronizer\Models\Client;

class ScheduleDiscountUpdatingFromRetailDemand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $id) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $agentId = MoySkladApi::getRetailDemand($this->id, ['expand' => 'agent'])->json('agent.id');

        if ($agentId === config('services.moySklad.default_customer_id')) {
            return;
        }

        $client = Client::where('moy_sklad_id', $agentId)->first();
        if (is_null($client)) {
            $this->fail(new \Exception("Обновление скидки пользователя не удалось: не найден пользователь с МС ID $agentId"));
        }

        dispatch(new SetDiscountByPurchases($client))->delay(Carbon::tomorrow()->setTime(10, 0, 0));
    }
}
