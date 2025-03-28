<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'amount_paid',
        'payment_method',
        'status',
        'payment_proof'
    ];

    protected $casts = [
        'amount_paid' => 'float', // ✅ Ensures `amount_paid` is a float
    ];

    // ✅ Define Payment Status Constants
    public const STATUS_PENDING = 'Pending';
    public const STATUS_PAID = 'Completed';
    public const STATUS_FAILED = 'Failed';

    // ✅ Define Payment Methods
    public const PAYMENT_METHOD_COD = 'cod';
    public const PAYMENT_METHOD_GCASH = 'gcash';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
