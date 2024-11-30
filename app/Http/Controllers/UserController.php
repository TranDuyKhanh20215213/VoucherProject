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

        // Get the redemptions related to the authenticated user's issuances
        $voucherData = Issuance::with('voucher') // Eager load the related voucher
        ->where('user_id', $userId) // Filter issuances by the authenticated user
        ->get() // Fetch all related data
        ->map(function ($issuance) {
            return [
                'voucher_name' => $issuance->voucher->name,
                'voucher_description' => $issuance->voucher->description,
                'type_discount' => $issuance->voucher->type_discount,
                'discount_amount' => $issuance->voucher->discount_amount,
                'expired_at' => $issuance->voucher->expired_at,
                'is_active' => $issuance->is_active,
            ];
        });

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $voucherData
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

        // Fetch issuances related to the authenticated user and where the issuance is inactive
        $voucherData = Issuance::with('voucher') // Eager load the related voucher
        ->where('user_id', $userId) // Filter issuances by the authenticated user
        ->where('is_active', false) // Only inactive issuances
        ->get() // Fetch all related data
        ->map(function ($issuance) {
            return [
                'voucher_name' => $issuance->voucher->name,
                'voucher_description' => $issuance->voucher->description,
                'type_discount' => $issuance->voucher->type_discount,
                'discount_amount' => $issuance->voucher->discount_amount,
                'expired_at' => $issuance->voucher->expired_at,
            ];
        });

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $voucherData
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

        // Get the redemptions where the user has used the voucher, and eager load related data
        $voucherData = Redemption::with(['issuance.voucher', 'product']) // Eager load Issuance -> Voucher and Product
        ->whereHas('issuance', function ($query) use ($userId) {
            $query->where('user_id', $userId); // Filter issuances for the authenticated user
        })
            ->get() // Fetch all related data
            ->map(function ($redemption) {
                return [
                    'used_at' => $redemption->used_at,
                    'voucher_name' => $redemption->issuance->voucher->name,
                    'voucher_description' => $redemption->issuance->voucher->description,
                    'expired_at' => $redemption->issuance->voucher->expired_at,
                    'product_name' => $redemption->product->name, // Get product name from the redemption
                ];
            });

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $voucherData,
        ], 200);
    }





}
