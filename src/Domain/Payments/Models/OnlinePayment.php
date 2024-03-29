<?php

namespace Src\Domain\Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Domain\Payments\Enums\OnlinePaymentStatus;
use Src\Domain\Synchronizer\Models\Order;

class OnlinePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'transaction_id',
        'payment_amount',
        'user_email',
    ];

    protected $casts = [
        'status' => OnlinePaymentStatus::class,
        // 'amount',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
