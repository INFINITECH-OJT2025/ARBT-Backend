<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Get user profile details.
     */
    public function show(Request $request)
    {
        $user = Auth::user(); // Get authenticated user
        return response()->json($user);
    }

    /**
     * Update user profile details.
     */
    public function update(Request $request)
    {
        $user = Auth::user(); // Get the authenticated user

        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'contact_number' => 'nullable|string|max:11', 
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        // Update user details
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (isset($validated['contact_number'])) {
            $user->contact_number = $validated['contact_number']; // ✅ Save contact number
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save(); // Save changes

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }
}