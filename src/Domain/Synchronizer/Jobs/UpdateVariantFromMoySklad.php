<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\UpdateVariantFromMoySklad as ActionsUpdateVariantFromMoySklad;

class UpdateVariantFromMoySklad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private array $request) {}

    /**
     * Execute the job.
     */
    public function handle(ActionsUpdateVariantFromMoySklad $updateAction): void
    {
        $updateAction->handle($this->request);
    }
}
