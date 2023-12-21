<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\CreateProductFromInsales as CreateAction;

class CreateProductFromInsales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private array $request, private bool $withBlocking = true)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(CreateAction $createProduct): void
    {
        $createProduct->handle($this->request, $this->withBlocking);
    }
}
