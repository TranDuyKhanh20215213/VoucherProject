<?php

namespace App\Http\Controllers;

use App\Models\Issuance;
use App\Models\Redemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\City;
use App\Models\Country;
use Carbon\Carbon;

class UserController extends Controller
{
    public function changePassword(Request $request)
    {
        $userId = Auth::guard('user')->id();
        $user = User::find($userId);

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

    public function viewAllVoucher()
    {
        // Get the authenticated user ID
        $userId = Auth::guard('user')->id();

        // Check if the user is authenticated
        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Query to join tables and fetch the required fields
        $redemptionData = DB::table('redemptions')
            ->join('issuances', 'redemptions.issuance_id', '=', 'issuances.id')
            ->join('vouchers', 'issuances.voucher_id', '=', 'vouchers.id')
            ->join('users', 'issuances.user_id', '=', 'users.id')
            ->where('users.id', '=', $userId) // Filter redemptions for the authenticated user
            ->select(
                'redemptions.used_at as used_at',
                'vouchers.name as voucher_name',
                'vouchers.description as voucher_description',
                'vouchers.type_discount as type_discount',
                'vouchers.discount_amount as discount_amount',
                'vouchers.expired_at as expired_at',
            )
            ->get();

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $redemptionData
        ], 200);
    }

    public function viewVoucherExpired()
    {
        // Get the authenticated user ID
        $userId = Auth::guard('user')->id();

        // Check if the user is authenticated
        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Query to join tables and fetch the required fields
        $redemptionData = DB::table('redemptions')
            ->join('issuances', 'redemptions.issuance_id', '=', 'issuances.id')
            ->join('vouchers', 'issuances.voucher_id', '=', 'vouchers.id')
            ->join('users', 'issuances.user_id', '=', 'users.id')
            ->where('users.id', '=', $userId) // Filter redemptions for the authenticated user
            ->where('issuances.is_active', '=', false) // Add condition for inactive issuances
            ->select(
                'redemptions.created_at as used_at',
                'vouchers.name as voucher_name',
                'vouchers.description as voucher_description',
                'vouchers.type_discount as type_discount',
                'vouchers.discount_amount as discount_amount',
                'vouchers.expired_at as expired_at'
            )
            ->get();

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $redemptionData,
        ], 200);
    }

    public function viewVoucherUsed()
    {
        // Get the authenticated user ID
        $userId = Auth::guard('user')->id();

        // Check if the user is authenticated
        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Query to join tables and fetch the required fields
        $redemptionData = DB::table('redemptions')
            ->join('issuances', 'redemptions.issuance_id', '=', 'issuances.id')
            ->join('vouchers', 'issuances.voucher_id', '=', 'vouchers.id')
            ->join('users', 'issuances.user_id', '=', 'users.id')
            ->where('users.id', '=', $userId) // Filter redemptions for the authenticated user
            ->whereNotNull('redemptions.used_at') // Add condition for used vouchers
            ->select(
                'redemptions.created_at as used_at',
                'vouchers.name as voucher_name',
                'vouchers.description as voucher_description',
                'vouchers.type_discount as type_discount',
                'vouchers.discount_amount as discount_amount',
                'vouchers.expired_at as expired_at'
            )
            ->get();

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $redemptionData,
        ], 200);
    }



}
