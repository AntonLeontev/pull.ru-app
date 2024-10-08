<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\UpdateProductFromMoySklad as ActionsUpdateProductFromMoySklad;

class UpdateProductFromMoySklad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private array $request) {}

    /**
     * Execute the job.
     */
    public function handle(ActionsUpdateProductFromMoySklad $updateAction): void
    {
        $updateAction->handle($this->request);
    }
}
