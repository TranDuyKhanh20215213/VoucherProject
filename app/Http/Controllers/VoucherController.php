<?php

namespace App\Http\Controllers;

use App\Models\Issuance;
use App\Models\Redemption;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type_discount' => 'required|boolean',
            'discount_amount' => 'required|numeric',
            'expired_at' => 'required|date|after:today',
        ]);

        // Create a new voucher
        $voucher = Voucher::create([
            'name' => $request->name,
            'description' => $request->description,
            'type_discount' => $request->type_discount,
            'discount_amount' => $request->discount_amount,
            'created_at' => now(),
            'expired_at' => $request->expired_at,
        ]);

        // Return a response with the created voucher
        return response()->json(['voucher' => $voucher], 201);
    }

    public function viewList()
    {
        // Fetch vouchers and select the required fields
        $vouchers = Voucher::select('name', 'description', 'type_discount', 'discount_amount')->get();

        // Return a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $vouchers
        ], 200);
    }

    public function viewDetail($id)
    {
        // Find the voucher by ID
        $voucher = Voucher::select(
            'name',
            'description',
            'type_discount',
            'discount_amount',
            'created_at',
            'expired_at'
        )->find($id);

        // Check if the voucher exists
        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found',
            ], 404);
        }

        // Return the voucher details
        return response()->json([
            'status' => 'success',
            'data' => $voucher,
        ], 200);
    }

    public function updateVoucher(Request $request, $id)
    {
        // Find the voucher by ID
        $voucher = Voucher::find($id);

        // Check if the voucher exists
        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found',
            ], 404);
        }

        // Validate the request
        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'type_discount' => 'nullable|boolean',
            'discount_amount' => 'nullable|numeric|min:0',
            'expired_at' => 'nullable|date|after:now',
        ]);

        // Prepare an array to hold the fields to update
        $fieldsToUpdate = [];

        // Update fields only if they are present in the request and not null
        if (!is_null($request->input('name'))) {
            $fieldsToUpdate['name'] = $request->input('name');
        }
        if (!is_null($request->input('description'))) {
            $fieldsToUpdate['description'] = $request->input('description');
        }
        if (!is_null($request->input('type_discount'))) {
            $fieldsToUpdate['type_discount'] = $request->input('type_discount');
        }
        if (!is_null($request->input('discount_amount'))) {
            $fieldsToUpdate['discount_amount'] = $request->input('discount_amount');
        }
        if (!is_null($request->input('expired_at'))) {
            $fieldsToUpdate['expired_at'] = $request->input('expired_at');
        }

        // Update the voucher details
        if (!empty($fieldsToUpdate)) {
            $fieldsToUpdate['created_at'] = now();
            $voucher->update($fieldsToUpdate);
        }

        // Return a success response
        return response()->json([
            'status' => 'success',
            'message' => 'Voucher updated successfully',
            'data' => $voucher,
        ], 200);
    }

    public function viewRedemption()
    {
        // Query to join tables and fetch the required fields
        $redemptionData = DB::table('redemptions')
            ->join('issuances', 'redemptions.issuance_id', '=', 'issuances.id')
            ->join('vouchers', 'issuances.voucher_id', '=', 'vouchers.id')
            ->join('users', 'issuances.user_id', '=', 'users.id')
            ->select(
                'redemptions.used_at as used_at',
                'vouchers.name as voucher_name',
                'users.username as user_name',
                'issuances.issued_at as issued_at',
            )
            ->get();

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $redemptionData
        ], 200);
    }

    public function viewRedemptionORM()
    {
        // Eloquent query with relationships and selected fields
        $redemptionData = Redemption::with(['issuance.voucher', 'issuance.user'])
            ->get()
            ->map(function ($redemption) {
                return [
                    'used_at' => $redemption->used_at,
                    'voucher_name' => $redemption->issuance->voucher->name,
                    'user_name' => $redemption->issuance->user->username,
                    'issuance_created_at' => $redemption->issuance->issued_at,
                ];
            });

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $redemptionData
        ], 200);
    }



    public function distributeVoucher(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // Retrieve voucher and check expiration
        $voucher = Voucher::find($request->voucher_id);
        if (now()->greaterThan($voucher->expired_at)) {
            return response()->json(['success' => false, 'message' => 'Voucher has expired.'], 400);
        }

        // Create Issuance record
        $issuance = Issuance::create([
            'voucher_id' => $request->voucher_id,
            'user_id' => $request->user_id,
            'is_active' => true,
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'issuance' => $issuance]);
    }
}
