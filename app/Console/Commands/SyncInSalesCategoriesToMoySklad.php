<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Domain\Synchronizer\Actions\SyncCategoriesFromInsales;

class SyncInSalesCategoriesToMoySklad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Переносит категории из Инcейлс в Мой Склад';

    /**
     * Execute the console command.
     */
    public function handle(SyncCategoriesFromInsales $sync)
    {
        $sync->handle();
    }
}
