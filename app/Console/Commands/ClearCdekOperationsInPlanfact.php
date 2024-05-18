<?php

namespace App\Console\Commands;

use App\Services\Planfact\PlanfactApi;
use Illuminate\Console\Command;

class ClearCdekOperationsInPlanfact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pf-clear-cdek-operations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // if (! $this->confirm('Are you sure?')) {
        // 	return;
        // }

        $cdekAccount = config('services.planfact.accounts.cdek');

        if (empty($cdekAccount)) {
            return;
        }

        $operations = PlanfactApi::getOperations($cdekAccount)->collect('data.items');

        foreach ($operations as $operation) {
            PlanfactApi::deleteOperation($operation['operationId']);
        }
    }
}
