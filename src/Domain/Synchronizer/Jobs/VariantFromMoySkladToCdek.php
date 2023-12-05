<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\VariantFromMoySkladToCdek as ActionsVariantFromMoySkladToCdek;

class VariantFromMoySkladToCdek implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private array $variant)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ActionsVariantFromMoySkladToCdek $action): void
    {
        $action->handle($this->variant);
    }
}
