<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // ✅ Custom validation messages
        $messages = [
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email.',
            'email.unique' => 'Email already exists.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.'
        ];

        // ✅ Validate user input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // ✅ Create user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        // ✅ Attempt to authenticate the user
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        // ✅ Get authenticated user
        $user = Auth::user(); 
    
        // ✅ Generate a new Passport token
        $token = $user->createToken('authToken')->accessToken;
    
        return response()->json([
            'id' => $user->id, 
            'email' => $user->email,
            'role' => $user->role,
            'token' => $token, // ✅ Send token to the frontend
        ], 200);
    }
    
    
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // ✅ Revoke all user tokens
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Logged out successfully'])
            ->cookie('auth_token', '', -1); // ✅ Expire token cookie
    }
}
