<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService 
{
    /**
     * Process a new payment for an order.
     *
     * @param Order $order
     * @param string $paymentMethod
     * @param float $amountPaid
     * @param string|null $paymentProof
     * @return Payment
     */
    public static function processPayment(Order $order, string $paymentMethod, float $amountPaid, string $paymentProof = null): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount_paid' => $amountPaid,
            'payment_method' => $paymentMethod,
            'status' => Payment::STATUS_PENDING,
            'payment_proof' => $paymentProof,
        ]);
    }
}
