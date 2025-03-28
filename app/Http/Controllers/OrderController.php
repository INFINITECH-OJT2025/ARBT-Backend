<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class OrderController extends Controller
{
    public function placeOrder(Request $request)
    {
        // Retrieve token from Authorization header
        $token = $request->bearerToken();

        if (!$token) {
            \Log::error('❌ User not authenticated - No token provided.');
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Find the user based on the provided token
        $user = User::where('api_token', $token)->first(); // Assuming 'api_token' is stored in the user table

        if (!$user) {
            \Log::error('❌ User not authenticated - Invalid token.');
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // If user is found, proceed with the order
        \Log::info('✅ Authenticated User:', ['user_id' => $user->id]);

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'total' => 'required|numeric|min:1',
            ]);

            \Log::info('✅ Order Data Validated:', $validated);

            // ✅ Save Order with user_id
            $order = Order::create([
                'user_id' => $user->id, // Ensure user_id is saved
                'total' => $validated['total'],
                'status' => Order::STATUS_PENDING,
            ]);

            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => Product::find($item['product_id'])->price,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully!',
                'order_id' => $order->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('❌ Order Error:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Order creation failed!',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getUserOrders(Request $request)
{
    $user = $request->user();

    $orders = Order::with(['orderItems', 'payment'])
        ->where('user_id', $user->id)
        ->get()
        ->map(function ($order) {
            return [
                'order_id' => $order->id,
                'total' => $order->total,
                'status' => $order->latest_payment_status, // ✅ Use updated status
                'payment_status' => $order->payment ? $order->payment->status : 'Pending',
                'created_at' => $order->created_at,
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->product->name,
                        'image' => $item->product->image,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                }),
            ];
        });

    return response()->json($orders);
}


public function show($id)
{
    $order = Order::with('items.product')->find($id);

    if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
    }

    return response()->json([
        'id' => $order->id,
        'subtotal' => (float) $order->subtotal, // ✅ Ensure numeric values
        'shipping_fee' => (float) $order->shipping_fee,
        'total' => (float) $order->total,
        'items' => $order->items->map(function ($item) {
            return [
                'name' => $item->product->name, // ✅ Assuming product relation
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price, // ✅ Ensure price is a number
            ];
        }),
    ]);
}

}
