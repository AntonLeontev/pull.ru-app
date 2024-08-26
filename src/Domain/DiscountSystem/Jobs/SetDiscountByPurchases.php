<?php

namespace Src\Domain\DiscountSystem\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\DiscountSystem\Actions\SetDiscountByPurchasesAction;
use Src\Domain\Synchronizer\Models\Client;

class SetDiscountByPurchases implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Client $client) {}

    /**
     * Execute the job.
     */
    public function handle(SetDiscountByPurchasesAction $action): void
    {
        try {
            $action->handle($this->client);
        } catch (\Throwable $th) {
            throw new \Exception("Не удалось обновить процент скидки пользователя c id {$this->client->id}. ".$th->getMessage());
        }
    }
}
