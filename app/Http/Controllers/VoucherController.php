<?php

namespace App\Http\Controllers;

use App\Models\Eligibility;
use App\Models\Issuance;
use App\Models\Product;
use App\Models\Redemption;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        // Create a new voucher
        $voucher = Voucher::create([
            'name' => $request->name,
            'description' => $request->description,
            'type_discount' => $request->type_discount,
            'discount_amount' => $request->discount_amount,
            'created' => now(),
            'expired_at' => $request->expired_at,
            'rule' => json_encode($request->product_ids), // Assigning product_ids array directly to rules
        ]);

        // Return a response with the created voucher
        return response()->json(['voucher' => $voucher], 201);
    }

    public function viewList()
    {
        // Fetch vouchers and select the required fields
        $vouchers = Voucher::select('id','name', 'description', 'type_discount', 'discount_amount', 'expired_at')->get();

        // Modify the response data by adding a new field
        $vouchers = $vouchers->map(function ($voucher) {
            return array_merge($voucher->toArray(), [
                'is_expired' => $voucher->expired_at < now(),
            ]);
        });

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
            'created',
            'expired_at',
            'rule' // Include the rule field to access product IDs
        )->find($id);

        // Check if the voucher exists
        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found',
            ], 404);
        }

        // Decode the rule field to get the product IDs as an array
        $productIds = json_decode($voucher->rule, true);

        // Ensure $productIds is an array before using it in whereIn
        if (!is_array($productIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid product IDs in voucher rule.',
            ], 400);
        }

        // Get the list of products (name, price, id) from the rule field
        $products = Product::select('id', 'name', 'price')
            ->whereIn('id', $productIds)
            ->get();

        // Return the voucher details along with the product details
        return response()->json([
            'status' => 'success',
            'data' => [
                'voucher' => $voucher,
                'eligible_products' => $products,
            ],
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
            'rule' => 'nullable|array', // Ensure 'rule' is an array of product IDs
            'rule.*' => 'integer|exists:products,id', // Each product ID must be a valid product ID from the products table
        ]);

        // Update fields directly if present in the request
        if (!is_null($request->input('name'))) {
            $voucher->name = $request->input('name');
        }
        if (!is_null($request->input('description'))) {
            $voucher->description = $request->input('description');
        }
        if (!is_null($request->input('type_discount'))) {
            $voucher->type_discount = $request->input('type_discount');
        }
        if (!is_null($request->input('discount_amount'))) {
            $voucher->discount_amount = $request->input('discount_amount');
        }
        if (!is_null($request->input('expired_at'))) {
            $voucher->expired_at = $request->input('expired_at');
        }

        // Handle rule (product_ids) update directly
        if (!is_null($request->input('rule'))) {
            $voucher->rule = json_encode($request->input('rule'));
        }

        // Update the created timestamp
        $voucher->created = now();

        // Save the changes
        $voucher->save();

        // Return a success response
        return response()->json([
            'status' => 'success',
            'message' => 'Voucher updated successfully',
            'data' => $voucher,
        ], 200);
    }




    public function viewRedemption()
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
        // Validate input
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
            'issued_at' => now(),
        ]);

        // Return success response with issuance and redemption
        return response()->json([
            'success' => true,
            'issuance' => $issuance,
        ]);
    }
    public function getProducts()
    {
        // Retrieve all products with only the specified columns
        $products = Product::select('id', 'name', 'price')->get();

        // Return the products in JSON format
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function getVoucherProducts($id)
    {
        // Find the voucher by ID
        $voucher = Voucher::find($id);

        // If voucher not found, return an error response
        if (!$voucher) {
            return response()->json(['error' => 'Voucher not found'], Response::HTTP_NOT_FOUND);
        }

        // Get product_ids directly from the 'rule' field (which is an array)
        $productIds = $voucher->rule;

        // If no product_ids are found in the rule, return an error response
        if (empty($productIds)) {
            return response()->json(['error' => 'No products found for this voucher'], Response::HTTP_BAD_REQUEST);
        }

        // Retrieve all products associated with the voucher's product_ids
        $products = Product::whereIn('id', $productIds)->get();

        // Return the products as JSON response
        return response()->json(['products' => $products], Response::HTTP_OK);
    }

}
