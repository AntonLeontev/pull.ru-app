<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\CreateOrderFromInsales as ActionsCreateOrderFromInsales;

class CreateOrderFromInsales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $request)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ActionsCreateOrderFromInsales $createOrder): void
    {
        $createOrder->handle($this->request);
    }
}
