<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'total', 'status', 'payment_method'];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // public function payment(): HasOne
    // {
    //     return $this->hasOne(Payment::class);
    // }

    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ Ensure Order has Payments
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }


    public function getLatestPaymentStatusAttribute()
    {
        return $this->payment ? $this->payment->status : 'Pending';
    }
}