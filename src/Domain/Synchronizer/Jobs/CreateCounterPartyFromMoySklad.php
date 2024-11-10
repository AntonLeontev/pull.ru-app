<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\MoySklad\MoySkladApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateCounterPartyFromMoySklad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private array $request) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach (data_get($this->request, 'events') as $event) {
            $counterpartyId = str(data_get($event, 'meta.href'))->afterLast('/')->value();

            $counterparty = MoySkladApi::getCounterparty($counterpartyId)->object();

            if ($counterparty->companyType !== 'individual') {
                return;
            }

            telegram_log('В МС создан контрагент.', [
                'email' => $counterparty->email ?? '',
                'name' => $counterparty->name,
                'phone' => $counterparty->phone ?? '',
            ]);
        }
    }
}
