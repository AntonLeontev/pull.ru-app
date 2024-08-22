<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\MoySklad\MSApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Models\Client;

class CreateClientInMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(public Client $client) {}

    /**
     * Execute the job.
     */
    public function handle(MSApiService $service)
    {
        try {
            $client = $service->createCounterpartyFromClient($this->client);

            $this->client->update(['moy_sklad_id' => $client->id]);
        } catch (\Throwable $th) {
            $message = sprintf(
                'Не удалось создать пользователя в мс: id %s, %s, %s, %s, %s',
                $this->client->id,
                $this->client->name,
                $this->client->surname,
                $this->client->phone,
                $this->client->email,
            );
            Log::channel('telegram')->critical($message);

            $this->fail($th);
        }
    }
}
