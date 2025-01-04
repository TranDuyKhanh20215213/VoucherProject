<?php

namespace App\Http\Controllers;

use App\Models\Issuance;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Redemption;
use App\Models\Voucher;
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
use Illuminate\Support\Facades\Validator;

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


    public function viewDetailIssuance($id)
    {
        // Retrieve the issuance along with its voucher
        $issuance = Issuance::with('voucher')->where('id', $id)->first();

        // Check if the issuance exists
        if (!$issuance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Issuance not found',
            ], 404);
        }

        // Decode the 'rule' field to get product IDs
        $productIds = json_decode($issuance->voucher->rule, true); // Decode JSON to array

        // Validate the decoded 'rule' data
        if (!is_array($productIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid rule format in the voucher',
            ], 400);
        }

        // Fetch products based on the product IDs from the decoded 'rule'
        $products = Product::whereIn('id', $productIds)
            ->select('id', 'name', 'price')
            ->get();

        // Prepare the response data
        $data = [
            'name' => $issuance->voucher->name,
            'description' => $issuance->voucher->description,
            'type_discount' => $issuance->voucher->type_discount,
            'discount_amount' => $issuance->voucher->discount_amount,
            'expired_at' => $issuance->voucher->expired_at,
            'issued_at' => $issuance->issued_at,
            'products' => $products,
        ];

        // Return the response with issuance details
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
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
                'issuance_id' => $issuance->id, // Include issuance ID
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
                'issuance_id' => $issuance->id, // Include issuance ID
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
        $voucherData = Redemption::with(['issuance.voucher', 'order.products']) // Eager load Issuance -> Voucher and Order -> Products
        ->whereHas('issuance', function ($query) use ($userId) {
            $query->where('user_id', $userId); // Filter issuances for the authenticated user
        })
            ->get() // Fetch all related data
            ->map(function ($redemption) {
                // Prepare product names from the order's products
                $productNames = $redemption->order->products->pluck('name')->toArray();

                return [
                    'issuance_id' => $redemption->issuance->id, // Include issuance ID
                    'used_at' => $redemption->used_at,
                    'voucher_name' => $redemption->issuance->voucher->name,
                    'voucher_description' => $redemption->issuance->voucher->description,
                    'expired_at' => $redemption->issuance->voucher->expired_at,
                    'products' => $productNames, // Include all product names from the order
                ];
            });

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $voucherData,
        ], 200);
    }





    public function useVoucher(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'issuance_id' => 'required|exists:issuances,id',
            'order_id' => 'required|exists:orders,id',
        ]);

        $issuanceId = $request->issuance_id;
        $orderId = $request->order_id;

        // Fetch the issuance and its associated voucher
        $issuance = Issuance::with('voucher')->findOrFail($issuanceId);

        // Check if the issuance is active
        if (!$issuance->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This voucher issuance is no longer active.',
            ], 400);
        }

        $voucher = $issuance->voucher;

        // Fetch all products in the order using the relationship
        $order = Order::findOrFail($orderId);
        $orderProducts = $order->products; // This retrieves the products associated with the order via the pivot table

        // Check if all products in the order are eligible for the voucher
        foreach ($orderProducts as $orderProduct) {
            // Check if the product is eligible for the voucher
            if (!$voucher->usableProduct($orderProduct->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more products in the order are not eligible for this voucher.',
                ], 400);
            }
        }

        // Create a redemption record for the voucher use
        $redemption = Redemption::create([
            'order_id' => $orderId,
            'issuance_id' => $issuanceId,
            'used_at' => now(),
        ]);

        // Mark the issuance as inactive after successful voucher use
        $issuance->is_active = false;
        $issuance->save();

        return response()->json([
            'success' => true,
            'message' => 'Voucher used successfully.',
            'redemption' => $redemption,
        ], 201);
    }

    public function createOrderProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|integer|in:1,2', // Assuming 1 and 2 are valid payment methods
            'products' => 'required|array',
            'products.*.name' => 'required|string', // Validate the product names
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Retrieve product details by name
        $productNames = array_column($request->input('products'), 'name');
        $productsFromDb = Product::whereIn('name', $productNames)->get();

        // Validate if all product names exist
        $missingProducts = array_diff($productNames, $productsFromDb->pluck('name')->toArray());
        if (!empty($missingProducts)) {
            return response()->json([
                'errors' => ['products' => 'The following products do not exist: ' . implode(', ', $missingProducts)],
            ], 422);
        }

        // Create the order
        $order = Order::create([
            'payment_method' => $request->input('payment_method'),
            'ordered_at' => now(),
        ]);

        // Prepare order_product data
        $products = $request->input('products');
        $orderProducts = [];

        foreach ($products as $product) {
            $productFromDb = $productsFromDb->firstWhere('name', $product['name']);
            $orderProducts[] = [
                'order_id' => $order->id,
                'product_id' => $productFromDb->id,
                'quantity' => $product['quantity'],
            ];
        }

        // Bulk insert into the database
        OrderProduct::insert($orderProducts);

        return response()->json([
            'message' => 'Order and associated products created successfully!',
            'order' => $order,
            'order_products' => $orderProducts,
        ], 201);
    }







}
