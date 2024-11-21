<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Admin;

class AdminController extends Controller
{
    public function viewUserList()
    {
        try{
            if (Gate::denies('viewAny', User::class)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
    
            $users = User::all();
            return response()->json($users);
        }
        catch(\Exception $e){
            Log::error('Error during retrieving user list: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function viewUserDetail($id)
    {
        try{
            $user = User::findOrFail($id);

            if (Gate::denies('view', $user)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json($user);
        }
        catch(\Exception $e){
            Log::error('Error during retrieving user information: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function deleteUser($id)
    {
        try{
            $user = User::findOrFail($id);

            if (Gate::denies('delete', $user)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        }
        catch(\Exception $e){
            Log::error('Error during deleting user: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function lockUser($id)
    {
        try{
            $user = User::findOrFail($id);

            if (Gate::denies('lockOrUnlock', $user)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user->status = 0;
            $user->save();
            return response()->json(['message' => 'Successfully locked user', 'user' => $user]);
        }
        catch(\Exception $e){
            Log::error('Error during locking user: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function unlockUser($id)
    {
        try{
            $user = User::findOrFail($id);

            if (Gate::denies('lockOrUnlock', $user)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user->status = 1;
            $user->save();
            return response()->json(['message' => 'Successfully unlocked user', 'user' => $user]);
        }
        catch(\Exception $e){
            Log::error('Error during unlocking user: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function changePassword(Request $request){
        try{
            $adminId = Auth::guard('admin')->id();
            $admin = Admin::find($adminId);

            if (Gate::denies('admin-changePassword', $admin)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $admin->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 403);
            }

            $admin->password = Hash::make($request->new_password);
            $admin->save();

            return response()->json(['message' => 'Password changed successfully']);
        }
        catch(\Exception $e){
            Log::error('Error during changing password: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function getProfile(){
        try{
            $admin = Auth::guard('admin')->user();

            if (Gate::denies('admin-getProfile', $admin)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json($admin);
        }
        catch(\Exception $e){
            Log::error('Error during retrieving admin information: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }

    public function updateProfile(Request $request){
        try{
            $adminId = Auth::guard('admin')->id();
            $admin = Admin::find($adminId);

            if (Gate::denies('admin-updateProfile', $admin)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'adminname' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $adminId,
                'phone' => 'nullable|string|max:15|regex:/^[0-9+\(\)#\.\s\/ext-]+$/',
                'address' => 'nullable|string|max:255' ,
                'gender' => 'nullable|string|max:255' ,
                'dateOfBirth' => 'nullable|date|before:today' ,
            ]);

            $admin->adminname = $request->input('adminname');
            $admin->email = $request->input('email');
            $admin->phone = $request->input('phone');
            $admin->address = $request->input('address');
            $admin->dateOfBirth = $request->input('dateOfBirth');
            $admin->gender = $request->input('gender');
            $admin->save();

            return response()->json(['message' => 'Profile updated successfully']);
        }
        catch(\Exception $e){
            Log::error('Error during updating profile: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred. Please try again later.', 500]);
        }
    }
}
