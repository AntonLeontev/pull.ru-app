<?php

namespace Src\Domain\FinancialAccounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\FinancialAccounting\Enums\CdekExpendsImportStatus;

class CdekExpendsImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'file',
        'status',
    ];

    protected $casts = [
        'status' => CdekExpendsImportStatus::class,
    ];
}
