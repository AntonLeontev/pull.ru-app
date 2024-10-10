<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Notifications\DiscountCardLinkNotification;
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
    public function __construct(public Client $client, public bool $withSms = false) {}

    /**
     * Execute the job.
     */
    public function handle(DicardsService $service)
    {
        try {
            $service->createCardForClient($this->client);
        } catch (\Throwable $th) {
            $message = sprintf(
                'Не удалось создать скидочную карту пользователя в дикардс: id %s, %s, %s, %s. Причина: %s',
                $this->client->id,
                $this->client->name,
                $this->client->surname,
                $this->client->phone,
                $th->getMessage(),
            );
            Log::channel('telegram')->critical($message);

            $this->fail($th);
        }

        if ($this->withSms) {
            try {
                $link = $service->getCardLink($this->client->discount_card);
            } catch (\Throwable $th) {
                $message = sprintf(
                    'Создали карту, но не удалось получить ссылку в дикардс: id пользователя %s, карта %s. Причина: %s',
                    $this->client->id,
                    $this->client->discount_card,
                    $th->getMessage(),
                );
                Log::channel('telegram')->critical($message);

                $this->fail($th);
            }

            $this->client->notify(new DiscountCardLinkNotification($link));
        }
    }
}
