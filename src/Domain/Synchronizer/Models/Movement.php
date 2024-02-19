<?php

namespace Src\Domain\Synchronizer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cdek_id',
        'moy_sklad_id',
    ];

    protected $casts = [

    ];
}
