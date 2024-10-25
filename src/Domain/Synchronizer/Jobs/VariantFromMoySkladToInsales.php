<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\InSales\Exceptions\InsalesRateLimitException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\VariantFromMoySkladToInsales as ActionsVariantFromMoySkladToInsales;

class VariantFromMoySkladToInsales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private array $variant) {}

    /**
     * Execute the job.
     */
    public function handle(ActionsVariantFromMoySkladToInsales $action): void
    {
		try {
			$action->handle($this->variant);
		} catch (InsalesRateLimitException $e) {
			$this->release(300);
		}
    }
}
