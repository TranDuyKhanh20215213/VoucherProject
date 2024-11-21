<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function changePassword(Request $request){  
        try{
            $userId = Auth::guard('user')->id();
            $user = \App\Models\User::find($userId);

            if (Gate::denies('user-changePassword', $user)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 403);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json(['message' => 'Password changed successfully']);
        }
        catch(\Exception $e){
            Log::error('Error during changing password: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function getProfile(){
        try{
            $user = Auth::guard('user')->user();

            if (Gate::denies('user-getProfile', $user)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json($user);
        }
        catch(\Exception $e){
            Log::error('Error during retrieving user information: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function updateProfile(Request $request){
        try{
            $userId = Auth::guard('user')->id();
            $user = \App\Models\User::find($userId);

            if (Gate::denies('user-updateProfile', $user)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'username' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $userId,
                'phone' => 'nullable|string|max:15|regex:/^[0-9+\(\)#\.\s\/ext-]+$/',
                'address' => 'nullable|string|max:255' ,
                'gender' => 'nullable|string|max:255' ,
                'dateOfBirth' => 'nullable|date|before:today' ,
            ]);

            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $user->phone = $request->input('phone');
            $user->address = $request->input('address');
            $user->dateOfBirth = $request->input('dateOfBirth');
            $user->gender = $request->input('gender');
            $user->save();

            return response()->json(['message' => 'Profile updated successfully']);
        }
        catch(\Exception $e){
            Log::error('Error during updating profile: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

}
