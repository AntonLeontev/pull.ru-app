<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\Dicards\DicardsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Models\Client;

class CreateDicardsCard implements ShouldQueue
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
    public function handle(DicardsService $service)
    {
        try {
            $service->createCardForClient($this->client);
        } catch (\Throwable $th) {
            $message = sprintf(
                'Не удалось создать скидочную карту пользователя в дикардс: id %s, %s, %s, %s',
                $this->client->id,
                $this->client->name,
                $this->client->surname,
                $this->client->phone,
            );
            Log::channel('telegram')->critical($message);

            $this->fail($th);
        }
    }
}
