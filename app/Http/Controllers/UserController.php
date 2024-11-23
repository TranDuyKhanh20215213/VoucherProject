<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\City;
use App\Models\Country;
use Carbon\Carbon;

class UserController extends Controller
{
    public function changePassword(Request $request)
    {
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

    public function getProfile()
    {
        $user = Auth::guard('user')->user();

        if (Gate::denies('user-getProfile', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $countries = Country::all();
        $cities = City::where('country_id', $user->country_id)->get();

        return response()->json(['user' => $user, 'countries' => $countries, 'cities' => $cities]);
    }

    public function updateProfile(Request $request)
    {
        $userId = Auth::guard('user')->id();
        $user = \App\Models\User::find($userId);

        if (Gate::denies('user-updateProfile', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:15|regex:/^[0-9+\(\)#\.\s\/ext-]+$/',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'gender' => 'nullable|string|max:255',
            'dateOfBirth' => 'nullable|date|before:today',
        ]);

        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->country_id = $request->country_id;
        $user->city_id = $request->city_id;
        $user->dateOfBirth = $request->dateOfBirth ? Carbon::parse($request->dateOfBirth) : null;
        $user->gender = $request->input('gender');
        $user->save();

        return response()->json(['message' => 'Profile updated successfully']);
    }
}
