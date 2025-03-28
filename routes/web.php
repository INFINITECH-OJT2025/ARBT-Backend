<?php

use Illuminate\Support\Facades\Route;
use App\Services\FirebaseMessagingService;

Route::get('/', function () {
    return view('welcome');
});

// This route sends a push notification to a device token
Route::get('/test-push', function (FirebaseMessagingService $fcm) {
    // Replace this with a real device token from your frontend
    $token = 'YOUR_DEVICE_TOKEN_HERE';  // Get this token dynamically from the frontend.

    // Send the notification through the FirebaseMessagingService
    $response = $fcm->sendNotificationToToken(
        $token,  // Device Token
        '🚀 Test Notification',  // Title of the notification
        'Hello from Laravel via Firebase Cloud Messaging V1!'  // Body of the notification
    );

    // Return the response from Firebase (either success or error)
    return response()->json($response);
});
