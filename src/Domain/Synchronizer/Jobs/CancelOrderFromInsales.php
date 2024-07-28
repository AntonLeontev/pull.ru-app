<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\CancelOrderFromInsales as ActionsCancelOrderFromInsales;

class CancelOrderFromInsales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $request) {}

    /**
     * Execute the job.
     */
    public function handle(ActionsCancelOrderFromInsales $cancelOrder): void
    {
        $cancelOrder->handle($this->request);
    }
}
