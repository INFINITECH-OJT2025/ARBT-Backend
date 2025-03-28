<?php
namespace App\Http\Controllers;
use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:gcash',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Calculate total price
            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $subtotal += $product->price * $item['quantity'];
            }
            $shipping_fee = 50.00;
            $total_amount = $subtotal + $shipping_fee;

            // Create the order
            $order = Order::create([
                'user_id' => $request->user_id,
                'total' => $total_amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // Store order items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            // Handle payment proof (if uploaded)
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
            }

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'user_id' => $request->user_id,
                'amount_paid' => $total_amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending', // Change this to 'completed' once the payment is verified
                'payment_proof' => $paymentProofPath,
            ]);

            DB::commit();

            return response()->json(['message' => 'Order placed successfully!', 'order' => $order], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Checkout failed', 'details' => $e->getMessage()], 500);
        }
    }
}
