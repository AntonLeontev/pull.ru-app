<?php

namespace Src\Domain\Synchronizer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Domain\Payments\Models\OnlinePayment;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Enums\OrderStatus;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'insales_id',
        'cdek_id',
        'moy_sklad_id',
        'payment_status',
        'status',
    ];

    protected $casts = [
        'payment_status' => OrderPaymentStatus::class,
        'status' => OrderStatus::class,
    ];

    public function onlinePayments(): HasMany
    {
        return $this->hasMany(OnlinePayment::class);
    }
}
