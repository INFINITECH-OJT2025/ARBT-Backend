<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // If you have a User model to store the FCM token.

class NotificationController extends Controller
{
    // Save FCM token to the database
    public function saveToken(Request $request)
    {
        // Validate incoming token
        $request->validate([
            'token' => 'required|string', // Ensure token is sent
        ]);

        // Assuming you want to save the token for the currently authenticated user
        $user = auth()->user();  // Get the currently authenticated user (if using authentication)

        if ($user) {
            // Save the FCM token (Assuming you have a column `fcm_token` in your users table)
            $user->fcm_token = $request->input('token');
            $user->save();

            return response()->json(['message' => 'Token saved successfully']);
        }

        return response()->json(['error' => 'User not authenticated'], 401);
    }
}