<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class AdminController extends Controller
{
    // Get user's shop tracker
    public function getUserShopTracker(Request $request)
    {
        try {

            $user = Auth::guard('api')->user();


            if (!$user) {
                Log::error('❌ Invalid token or user not authenticated');
                return response()->json(['error' => 'Invalid token or user not authenticated'], 401);
            }

            Log::info('✅ User authenticated', ['user_id' => $user->id]);

            // ✅ Fetch user orders with related items & product details
            $orders = Order::where('user_id', $user->id)
                ->with(['orderItems.product']) // ✅ Includes items + product details
                ->orderBy('created_at', 'desc') // Sort latest first
                ->get();

            // ✅ Response Structure
            $formattedOrders = $orders->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total' => $order->total,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'product_id' => $item->product->id ?? null,
                            'name' => $item->product->name ?? 'Unknown',
                            'image' => $item->product->image ? url('storage/' . $item->product->image) : null, // ✅ Full URL
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                        ];
                    }),
                ];
            });

            if ($formattedOrders->isEmpty()) {
                Log::warning('⚠️ No orders found for user', ['user_id' => $user->id]);
            }

            return response()->json(['user_shop_tracker' => $formattedOrders], 200);
        } catch (\Exception $e) {
            Log::error('❌ Error in getUserShopTracker', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }




    public function getShopSalesReport()
    {
        try {
            // ✅ Fetch only completed payments
            $completedPayments = Payment::where('status', Payment::STATUS_PAID)->get();
    
            // ✅ Calculate total revenue from completed payments
            $totalRevenue = $completedPayments->sum('amount_paid');
    
            // ✅ Count total approved sales
            $totalSales = $completedPayments->count();
    
            // ✅ Get most & least sold products
            $mostSoldProduct = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
                ->select('products.name', \DB::raw('SUM(order_items.quantity) as total_sold'))
                ->groupBy('products.name')
                ->orderByDesc('total_sold')
                ->first();
    
            $leastSoldProduct = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
                ->select('products.name', \DB::raw('SUM(order_items.quantity) as total_sold'))
                ->groupBy('products.name')
                ->orderBy('total_sold')
                ->first();
    
            // ✅ Fetch recent sales (limit to last 5)
            $recentSales = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.status', 'Completed')
                ->select('products.name', 'order_items.quantity', 'orders.created_at as date')
                ->orderByDesc('orders.created_at')
                ->limit(5)
                ->get();
    
            return response()->json([
                'totalSales' => $totalSales,
                'totalRevenue' => $totalRevenue,
                'mostSoldProduct' => $mostSoldProduct ? ['name' => $mostSoldProduct->name, 'quantity' => $mostSoldProduct->total_sold] : null,
                'leastSoldProduct' => $leastSoldProduct ? ['name' => $leastSoldProduct->name, 'quantity' => $leastSoldProduct->total_sold] : null,
                'recentSales' => $recentSales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch shop sales report',
                'message' => $e->getMessage()
            ], 500);
        }
    }





    public function getSalesReport(Request $request)
{
    try {
        // ✅ Get start & end date from request (default: last 30 days)
        $startDate = $request->query('startDate', now()->subDays(30)->toDateString());
        $endDate = $request->query('endDate', now()->toDateString());

        // ✅ Fetch only completed payments within the date range
        $completedPayments = Payment::where('status', Payment::STATUS_PAID)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get();

        // ✅ Calculate total revenue from completed payments
        $totalRevenue = $completedPayments->sum('amount_paid');

        // ✅ Count total approved sales
        $totalSales = $completedPayments->count();

        // ✅ Get most & least sold products
        $mostSoldProduct = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', \DB::raw('SUM(order_items.quantity) as total_sold'))
            ->whereBetween('order_items.created_at', [$startDate, $endDate]) // ✅ Filter by date range
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->first();

        $leastSoldProduct = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', \DB::raw('SUM(order_items.quantity) as total_sold'))
            ->whereBetween('order_items.created_at', [$startDate, $endDate]) // ✅ Filter by date range
            ->groupBy('products.name')
            ->orderBy('total_sold')
            ->first();

        // ✅ Fetch recent sales (limit to last 5)
        $recentSales = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'Completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate]) // ✅ Filter by date range
            ->select('products.name', 'order_items.quantity', 'orders.created_at as date')
            ->orderByDesc('orders.created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'totalSales' => $totalSales,
            'totalRevenue' => "Php" . number_format($totalRevenue, 2, '.', ','), // ✅ Proper PHP format
            'mostSoldProduct' => $mostSoldProduct ? ['name' => $mostSoldProduct->name, 'quantity' => $mostSoldProduct->total_sold] : null,
            'leastSoldProduct' => $leastSoldProduct ? ['name' => $leastSoldProduct->name, 'quantity' => $leastSoldProduct->total_sold] : null,
            'recentSales' => $recentSales
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch shop sales report',
            'message' => $e->getMessage()
        ], 500);
    }
}






}
