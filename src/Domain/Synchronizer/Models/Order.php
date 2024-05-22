<?php

namespace Src\Domain\Synchronizer\Models;

use App\Events\OrderCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Domain\Payments\Models\OnlinePayment;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Enums\OrderPaymentType;
use Src\Domain\Synchronizer\Enums\OrderStatus;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'insales_id',
        'cdek_id',
        'fullfillment_id',
        'tries',
        'moy_sklad_id',
        'payment_status',
        'payment_type',
        'status',
        'delivery_info',
        'reciept_sent',
        'is_error',
    ];

    protected $casts = [
        'payment_status' => OrderPaymentStatus::class,
        'payment_type' => OrderPaymentType::class,
        'status' => OrderStatus::class,
        'reciept_sent' => 'boolean',
        'is_error' => 'boolean',
    ];

    protected $dispatchesEvents = [
        'created' => OrderCreated::class,
    ];

    public function onlinePayments(): HasMany
    {
        return $this->hasMany(OnlinePayment::class);
    }
}
