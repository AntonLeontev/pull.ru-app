<?php

namespace Src\Domain\FinancialAccounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CdekTransaction extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'ext_id',
    ];

    protected $casts = [

    ];
}
