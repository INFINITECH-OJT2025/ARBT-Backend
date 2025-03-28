<?php

use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PassportFixController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Models\OrderItem; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController; 


Route::get('/news/active-campaigns', [NewsController::class, 'activeCampaigns']);


Route::get('/news', [NewsController::class, 'index']);
Route::post('/news', [NewsController::class, 'store']);

Route::get('/news/{id}', [NewsController::class, 'show']); // Fetch single campaign

Route::delete('/news/{id}', [NewsController::class, 'destroy']); // Delete campaign
// Route::patch('/news/{id}/archive', [NewsController::class, 'archive']); // Archive campaign
// Route::patch('/news/{id}/unarchive', [NewsController::class, 'unarchive']); // Unarchive campaign


Route::patch('/news/{id}/archive', [NewsController::class, 'archive']); // Archive
Route::patch('/news/{id}/unarchive', [NewsController::class, 'unarchive']); // Unarchive

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json($request->user());
});
// Route::post('/news/{id}', [NewsController::class, 'update']);
Route::put('/news/{id}', [NewsController::class, 'update']);


Route::get('/news/{id}', [NewsController::class, 'show']);

Route::post('/news/{news}', [NewsController::class, 'update']); // Accepts multipart



Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index']);
Route::patch('/subscription-plans/{id}', [SubscriptionPlanController::class, 'update']);



//for authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::get('/user-bookings', [BookingController::class, 'getUserBookings']);

//for bookings
Route::get('/bookings', [BookingController::class, 'index']); // ✅ Get all bookings
Route::post('/bookings', [BookingController::class, 'store']); // ✅ Allow POST for new bookings
Route::patch('/bookings/{id}', [BookingController::class, 'update']);
Route::patch('/bookings/{id}', [BookingController::class, 'updateStatus']);
//for my dashboard
Route::get('/booking-report', [BookingController::class, 'getBookingReport']);


//for shop

Route::get('/products', [ProductController::class, 'index']); 
Route::post('/products', [ProductController::class, 'store']);



Route::middleware('auth:api')->post('/checkout', [PaymentController::class, 'processPayment']);




Route::get('/products/{id}', [ProductController::class, 'show']);
Route::match(['put', 'patch'], '/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::match(['post', 'patch'], 'products/{id}', [ProductController::class, 'update']); // ✅ Allow both PATCH and POST for updates

Route::get('/payments', [PaymentController::class, 'index']);
Route::post('/payments', [PaymentController::class, 'store']);
Route::patch('/payments/{id}', [PaymentController::class, 'update']);
Route::get('/payments/order/{order_id}', [PaymentController::class, 'getByOrder']);







//reports
Route::get('/products/export/csv', [ProductController::class, 'exportCSV']);

Route::get('/bookings/export/csv', [BookingController::class, 'exportCSV']);
Route::get('/bookings/export/pdf', [BookingController::class, 'exportPDF']);


//report routes
Route::get('/export-approved-bookings', [BookingController::class, 'getApprovedBookingsForExport']);
Route::get('/get-sales-report', [AdminController::class, 'getSalesReport']);




//orders
Route::post('/orders', [OrderController::class, 'placeOrder']);
// Route::post('orders', action: [OrderController::class, 'placeOrder']);
Route::post('payment-process', [PaymentController::class, 'processPayment']);


Route::get('/payments', [PaymentController::class, 'index']);


//for my adminsss


Route::middleware('auth:api')->get('/user-shop-tracker', [AdminController::class, 'getUserShopTracker']);
Route::middleware('auth:api')->get('/orders', [AdminController::class, 'getUserOrders']);




    
Route::get('/admin/payments', [PaymentController::class, 'fetchPayments']);








use App\Http\Controllers\ProfileController;

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']); // ✅ Get Profile
    Route::put('/profile/update', [ProfileController::class, 'update']); // ✅ Update Profile
});




Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::put('/payments/{id}/accept', [PaymentController::class, 'acceptPayment']);
    Route::put('/payments/{id}/decline', [PaymentController::class, 'declinePayment']); // ✅ Fix URL
});

Route::get('/orders/{id}', [OrderController::class, 'show']);



use App\Http\Controllers\ReviewController;

Route::middleware('auth:api')->group(function () {
    Route::get('/reviews', [ReviewController::class, 'index']); // Get user's reviews
    Route::post('/reviews', [ReviewController::class, 'store']); // Submit a new review (1 per order)
});

//review on landing page
// Route::middleware('auth:api')->get('/user/reviews', [ReviewController::class, 'getUserReviews']);

Route::get('/reviews', [ReviewController::class, 'getAllReviews']);






use App\Http\Controllers\TeamController;

Route::resource('teams', TeamController::class);


Route::get('/teams', [TeamController::class, 'index']);
Route::post('/teams', [TeamController::class, 'store']);

Route::get('/teams/{id}', [TeamController::class, 'show']); // Fetch single team member
Route::put('/teams/{id}', [TeamController::class, 'update']); // Update team member
Route::delete('/teams/{id}', [TeamController::class, 'destroy']); // Delete team member
Route::patch('/teams/{id}/archive', [TeamController::class, 'archive']); // Archive team member
Route::patch('/teams/{id}/unarchive', [TeamController::class, 'unarchive']); // Unarchive team member




// use App\Http\Controllers\TeamController;

// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('teams', TeamController::class);
// });







Route::get('/shop-report', [AdminController::class, 'getShopSalesReport']);




Route::patch('/reviews/{id}/publish', [ReviewController::class, 'publish']);
Route::patch('/reviews/{id}/unpublish', [ReviewController::class, 'unpublish']);

Route::patch('/reviews/{id}/toggle-publish', [ReviewController::class, 'togglePublish']);




// routes/web.php or routes/api.php
Route::post('/save-token', [NotificationController::class, 'saveToken']);

































