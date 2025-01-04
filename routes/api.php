<?php

use App\Http\Controllers\VoucherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CountryCityDropdownController;

Route::get('/fetch-countries', [CountryCityDropdownController::class, 'fetchCountry']);
Route::post('/fetch-cities', [CountryCityDropdownController::class, 'fetchCity']);


Route::post('/user/register', [UserAuthController::class, 'register']);
Route::post('/user/login', [UserAuthController::class, 'login']);

Route::middleware('auth:user')->prefix('user')->group(function () {

    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::get('/view-detail/{id}', [UserController::class, 'viewDetailIssuance']);
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::get('/view-all-voucher', [UserController::class, 'viewAllVoucher']);
    Route::get('/view-voucher-expired', [UserController::class, 'viewVoucherExpired']);
    Route::get('/view-voucher-used', [UserController::class, 'viewVoucherUsed']);
    Route::post('/use-voucher', [UserController::class, 'useVoucher']);
    Route::post('/create-order', [UserController::class, 'createOrderProducts']);
});


Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:admin')->prefix('admin')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);

    Route::get('/profile', [AdminController::class, 'getProfile']);
    Route::post('/update-profile', [AdminController::class, 'updateProfile']);
    Route::post('/change-password', [AdminController::class, 'changePassword']);

    Route::get('/users', [AdminController::class, 'viewUserList']);
    Route::get('/users/{id}', [AdminController::class, 'viewUserDetail']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    Route::post('/users/{id}/lock', [AdminController::class, 'lockUser']);
    Route::post('/users/{id}/unlock', [AdminController::class, 'unlockUser']);


    Route::get('/view-detail/{id}', [VoucherController::class, 'viewDetail']);
    Route::get('/view-list-voucher', [VoucherController::class, 'viewList']);
    Route::put('/update-voucher/{id}', [VoucherController::class, 'updateVoucher']);
    Route::post('/create-voucher', [VoucherController::class, 'store']);
    Route::delete('/delete-voucher/{id}', [VoucherController::class, 'deleteVoucher']);
    Route::post('/distribute-voucher', [VoucherController::class, 'distributeVoucher']);
    Route::get('/view-redemption', [VoucherController::class, 'viewRedemption']);
    Route::get('/products', [VoucherController::class, 'getProducts']);
    Route::get('/get-voucher-rule/{id}', [VoucherController::class, 'getVoucherProducts']);

});

Route::get('/login', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('login');

Route::get('/forgot-password', function () {
    return view('auth.reset-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('guest')->name('password.update');
