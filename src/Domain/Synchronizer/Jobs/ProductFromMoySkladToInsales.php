<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\ProductFromMoySkladToInsales as ActionsProductFromMoySkladToInsales;
use Src\Domain\Synchronizer\Models\Variant;

class ProductFromMoySkladToInsales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Variant $dbVariant, private array $MSProduct) {}

    /**
     * Execute the job.
     */
    public function handle(ActionsProductFromMoySkladToInsales $action): void
    {
        $action->handle($this->dbVariant, $this->MSProduct);
    }
}
