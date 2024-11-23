<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
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

    public function logout(Request $request)
    {
        $admin = $request->user();

        if ($admin) {
            $admin->tokens()->delete();
            
            return response()->json(['message' => 'Successfully logged out']);
        } else {
            return response()->json(['message' => 'No authenticated admin found'], 401);
        }
    }
}
