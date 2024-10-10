<?php

namespace Src\Domain\Synchronizer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'insales_id',
        'moy_sklad_id',
        'birthday',
        'discount_card',
        'discount_percent',
        'is_registered',
    ];

    protected $casts = [
        'is_registered' => 'boolean',
    ];
}
