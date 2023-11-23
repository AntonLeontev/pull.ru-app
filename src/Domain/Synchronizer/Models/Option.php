<?php

namespace Src\Domain\Synchronizer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'insales_id',
        'moy_sklad_id',
    ];
}
