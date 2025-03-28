<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{    // ✅ Fetch all payments with related order & user
    public function index(): JsonResponse
    {
        $payments = Payment::with(['order', 'user'])->get();
        return response()->json($payments);
    }

    public function processPayment(Request $request): JsonResponse
    {
        DB::beginTransaction();
    
        try {
            \Log::info('🔍 Incoming Payment Request', ['payload' => $request->all()]);
    
            // ✅ Validate request
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'total' => 'required|numeric|min:1',
                'payment_method' => 'required|in:gcash',
                'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // ✅ Ensure valid image
            ]);
    
            \Log::info('✅ Request validated', $validated);
    
            // ✅ Find user
            $user = User::find($validated['user_id']);
            if (!$user) {
                \Log::warning('⚠️ User not found', ['user_id' => $validated['user_id']]);
                return response()->json(['error' => 'User not found.'], 404);
            }
    
            // ✅ Check for duplicate payments (pending orders)
            $existingOrder = Order::where('user_id', $validated['user_id'])
                ->where('status', 'pending')
                ->first();
    
            if ($existingOrder) {
                \Log::warning('⚠️ Duplicate order detected', ['order_id' => $existingOrder->id]);
                return response()->json(['error' => 'Pending order already exists.'], 400);
            }
    
            // ✅ Create Order
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'total' => $validated['total'],
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
            ]);
    
            \Log::info('✅ Order Created', ['order_id' => $order->id]);
    
            // ✅ Add Order Items
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
    
                if (!$product) {
                    DB::rollBack();
                    \Log::error('❌ Product not found', ['product_id' => $item['product_id']]);
                    return response()->json(['error' => 'Product not found.'], 400);
                }
    
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'name' => $product->name,  // Added to avoid null in DB
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }
    
            \Log::info('✅ Order Items Added', ['order_id' => $order->id]);
    
            // ✅ Handle Payment Proof Upload (if provided)
            $paymentProofPath = null;
    
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public'); // ✅ Store in `storage/app/public/payment_proofs`
            }
    
            // ✅ Check if Payment Already Exists for This Order
            if (Payment::where('order_id', $order->id)->exists()) {
                DB::rollBack();
                \Log::warning('⚠️ Payment already exists for order', ['order_id' => $order->id]);
                return response()->json(['error' => 'Payment already exists for this order.'], 400);
            }
    
            // ✅ Create Payment Record
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $validated['user_id'],
                'amount_paid' => $validated['total'],
                'payment_method' => $validated['payment_method'],
                'payment_proof' => $paymentProofPath, // ✅ Store file path in DB
                'status' => Payment::STATUS_PENDING,
            ]);
    
            \Log::info('✅ Payment Recorded', ['payment_id' => $payment->id]);
    
            // ✅ Update Order Status to "Processing"
            $order->update(['status' => 'processing']);
    
            DB::commit();
    
            \Log::info('✅ Payment Process Completed Successfully', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
            ]);
    
            return response()->json([
                'message' => 'Payment processed and order created successfully!',
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'payment_proof_url' => $paymentProofPath ? asset("storage/$paymentProofPath") : null, // ✅ Return file URL
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ Payment Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return response()->json([
                'error' => 'Payment processing failed.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    

        

    public function fetchPayments()
    {

        
        try {
            $payments = Payment::with(['order.user']) // ✅ Fetch Order & User details
                ->orderBy('created_at', 'desc')
                ->get();
    


                
                $formattedPayments = $payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'order_id' => $payment->order_id,
                        'user_name' => $payment->order->user->name ?? 'Unknown User',
                        'amount_paid' => $payment->amount_paid,
                        'payment_method' => $payment->payment_method,
                        'status' => $payment->status,
                        'payment_proof' => $payment->payment_proof ? url('storage/' . $payment->payment_proof) : null,
                        'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                        'items' => $payment->order->orderItems->map(function ($item) {
                            return [
                                'name' => $item->product->name ?? 'Unknown',
                                'price' => $item->price,
                                'quantity' => $item->quantity,
                            ];
                        }),
             
                ];

                
            });
    
            return response()->json($formattedPayments, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch payments', 'message' => $e->getMessage()], 500);
        }
    }













    
// public function acceptPayment($id): JsonResponse
// {
//     $payment = Payment::find($id);
//     if (!$payment) {
//         return response()->json(['message' => 'Payment not found'], 404);
//     }

//     $payment->status = 'Completed';
//     $payment->save();

//     // ✅ Update order status when payment is accepted
//     $order = Order::find($payment->order_id);
//     if ($order) {
//         $order->status = 'completed'; // ✅ Ensure consistency
//         $order->save();
//     }

//     return response()->json(['message' => 'Payment accepted successfully']);
// }

public function declinePayment($id): JsonResponse
{
    $payment = Payment::find($id);
    if (!$payment) {
        return response()->json(['message' => 'Payment not found'], 404);
    }

    // ✅ Ensure status is assigned as "Failed"
    $payment->status = 'Failed';
    $payment->save();

    // ✅ Update order status to "canceled"
    $order = Order::find($payment->order_id);
    if ($order) {
        $order->status = 'canceled';
        $order->save();
    }

    return response()->json(['message' => 'Payment marked as Failed successfully']);
}













public function acceptPayment($id): JsonResponse
{
    $payment = Payment::find($id);
    if (!$payment) {
        return response()->json(['message' => 'Payment not found'], 404);
    }

    // Update payment status to 'Completed'
    $payment->status = 'Completed';
    $payment->save();

    // ✅ Update order status when payment is accepted
    $order = Order::find($payment->order_id);
    if ($order) {
        $order->status = 'completed'; // Ensure consistency
        $order->save();

        // ✅ Loop through each order item and reduce the product quantity
        foreach ($order->orderItems as $orderItem) {
            $product = Product::find($orderItem->product_id);

            if ($product) {
                // Reduce product stock based on the quantity ordered
                $product->quantity -= $orderItem->quantity;

                // Ensure quantity does not go below 0
                if ($product->quantity < 0) {
                    $product->quantity = 0;
                }

                // Save the updated product stock
                $product->save();
            }
        }
    }

    return response()->json(['message' => 'Payment accepted successfully and product quantity updated']);
}






}















