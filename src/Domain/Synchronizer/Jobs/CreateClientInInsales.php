<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\InSales\Exceptions\InsalesRateLimitException;
use App\Services\InSales\InsalesApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Models\Client;

class CreateClientInInsales implements ShouldQueue
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
    public function handle(InsalesApiService $insalesApiService)
    {
        try {
            $client = $insalesApiService->createClientFromClient($this->client);

            $this->client->update(['insales_id' => $client->id]);
        } catch (InsalesRateLimitException $e) {
            return $this->release(300);
        } catch (\Throwable $th) {
            $message = sprintf(
                'Не удалось создать пользователя в инсейлс: id %s, %s, %s, %s, %s',
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
