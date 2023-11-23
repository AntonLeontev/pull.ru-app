<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Domain\Synchronizer\Actions\SyncOptionsFromInsales as ActionsSyncOptionsFromInsales;

class SyncOptionsFromInsales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-options';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизирует характеристики модификаций из инсейлс';

    /**
     * Execute the console command.
     */
    public function handle(ActionsSyncOptionsFromInsales $sync)
    {
        $sync->handle();
    }
}
