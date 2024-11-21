<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        try{
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            $admin = Admin::where('email', $request->email)->first();
    
            if (! $admin) {
                return response()->json(['message' => 'Admin not found'], 404);
            }
    
            if (! Hash::check($request->password, $admin->password)) {
                return response()->json(['message' => 'Incorrect password'], 401);
            }
    
            $token = $admin->createToken('admin-auth-token')->plainTextToken;
    
            return response()->json(['token' => $token, 'admin' => $admin]);
        }
        catch(\Exception $e){
            Log::error('Error during login: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function logout(Request $request)
    {
        try{
            $admin = $request->user();

            if ($admin) {
                $admin->tokens()->delete();
                return response()->json(['message' => 'Successfully logged out']);
            } else {
                return response()->json(['message' => 'No authenticated admin found'], 401);
            }
        }
        catch(\Exception $e){
            Log::error('Error during logout: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }
}
