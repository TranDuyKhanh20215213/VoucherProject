<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Admin;
use App\Models\City;
use App\Models\Country;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function viewUserList()
    {
        if (Gate::denies('viewAny', User::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::all();
        return response()->json($users);
    }

    public function viewUserDetail($id)
    {
        $user = User::findOrFail($id);

        if (Gate::denies('view', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($user);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if (Gate::denies('delete', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function lockUser($id)
    {
        $user = User::findOrFail($id);

        if (Gate::denies('lockOrUnlock', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->status = 0;
        $user->save();

        return response()->json(['message' => 'Successfully locked user', 'user' => $user]);
    }

    public function unlockUser($id)
    {
        $user = User::findOrFail($id);

        if (Gate::denies('lockOrUnlock', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->status = 1;
        $user->save();

        return response()->json(['message' => 'Successfully unlocked user', 'user' => $user]);
    }

    public function changePassword(Request $request)
    {
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

    public function getProfile()
    {
        $admin = Auth::guard('admin')->user();

        if (Gate::denies('admin-getProfile', $admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $countries = Country::all();
        $cities = City::where('country_id', $admin->country_id)->get();

        return response()->json(['admin' => $admin, 'countries' => $countries, 'cities' => $cities]);
    }

    public function updateProfile(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        $admin = Admin::find($adminId);

        if (Gate::denies('admin-updateProfile', $admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'adminname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $adminId,
            'phone' => 'nullable|string|max:15|regex:/^[0-9+\(\)#\.\s\/ext-]+$/',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'gender' => 'nullable|string|max:255',
            'dateOfBirth' => 'nullable|date|before:today',
        ]);

        $admin->adminname = $request->input('adminname');
        $admin->email = $request->input('email');
        $admin->phone = $request->input('phone');
        $admin->country_id = $request->country_id;
        $admin->city_id = $request->city_id;
        $admin->dateOfBirth = $request->dateOfBirth ? Carbon::parse($request->dateOfBirth) : null;
        $admin->gender = $request->input('gender');
        $admin->save();

        return response()->json(['message' => 'Profile updated successfully']);
    }
}
