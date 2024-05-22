<?php

namespace Src\Domain\FinancialAccounting\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Src\Domain\FinancialAccounting\Imports\CdekExpendsImport as ImportsCdekExpendsImport;
use Src\Domain\FinancialAccounting\Models\CdekExpendsImport;

class ImportOperationsFromCdekFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public CdekExpendsImport $importModel)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ImportsCdekExpendsImport $import): void
    {
        Excel::import($import, $this->importModel->file);
    }
}
