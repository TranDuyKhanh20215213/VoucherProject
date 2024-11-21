<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        try{
            $request->validate([
                'username' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
            ]);
    
            if (User::where('email', $request->email)->exists()) {
                return response()->json(['message' => 'This email address was already registered'], 409);
            }
    
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            $token = $user->createToken('user-auth-token')->plainTextToken;
            return response()->json(['token' => $token, 'user' => $user], 201);
        }
        catch(\Exception $e){
            Log::error('Error during registration: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function login(Request $request)
    {
        try{
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            $user = User::where('email', $request->email)->first();
    
            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }
    
            if (! Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Incorrect password'], 401);
            }
    
            $token = $user->createToken('user-auth-token')->plainTextToken;
    
            return response()->json(['token' => $token, 'user' => $user]);
        }
        catch(\Exception $e){
            Log::error('Error during login: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function logout(Request $request)
    { 
        try{
            $user = $request->user();

            if ($user) {
                $user->tokens()->delete();
                return response()->json(['message' => 'Successfully logged out']);
            } else {
                return response()->json(['message' => 'No authenticated user found'], 401);
            }
        }
        catch(\Exception $e){
            Log::error('Error during logout: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }
}
