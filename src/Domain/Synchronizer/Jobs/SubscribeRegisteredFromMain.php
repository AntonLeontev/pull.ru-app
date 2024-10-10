<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\Unisender\UnisenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Models\Client;

class SubscribeRegisteredFromMain implements ShouldQueue
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
    public function handle(UnisenderService $uni)
    {
        try {
            $uni->subscribeFromPopupRegister(
                $this->client->email,
                $this->client->phone,
                trim($this->client->name.' '.$this->client->surname),
            );
        } catch (\Throwable $th) {
            $message = sprintf(
                'Не удалось подписать пользователя зареганного на главной на юнисендер: id %s, %s, %s, %s',
                $this->client->id,
                $this->client->name,
                $this->client->phone,
                $this->client->email,
            );
            Log::channel('telegram')->critical($message);

            $this->fail($th);
        }
    }
}