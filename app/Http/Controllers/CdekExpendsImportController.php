<?php

namespace App\Http\Controllers;

use App\Http\Requests\CdekExpendsImportStoreRequest;
use Illuminate\Support\Facades\Storage;
use Src\Domain\FinancialAccounting\Jobs\ImportOperationsFromCdekFile;
use Src\Domain\FinancialAccounting\Models\CdekExpendsImport;

class CdekExpendsImportController extends Controller
{
    public function create(CdekExpendsImportStoreRequest $request)
    {
        $path = Storage::disk('local')->putFile('cdek-imports', $request->file('file'));

        $import = CdekExpendsImport::create([
            'file' => $path,
        ]);

        dispatch(new ImportOperationsFromCdekFile($import))->onQueue('high');

        return back();
    }
}
