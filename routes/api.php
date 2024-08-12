<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\OrderProcessController;
use App\Http\Controllers\API\RestaurantController;
use App\Http\Controllers\API\TableController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\CalculationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::controller(RegisterController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::post('deleteUser', 'deleteUser');
        Route::post('otpverification','otpVerification');
        Route::post('otpregenerate','oTpRegenerate');
        Route::post('logout','logout');
        Route::post('forgot-password','forgotPassword');
        Route::post('reset-password','resetPassword');
        
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('category', CategoryController::class);
        Route::resource('restaurant',RestaurantController::class);
        Route::resource('table',TableController::class);
        //----Customer API--------
        Route::post('createCustomer',[CustomerController::class,'createCustomer']);
        Route::get('getCustomer',[CustomerController::class,'getCustomer']);
        //------Order API------
        Route::post('createOrder', [OrderProcessController::class, 'orderProcess'])->name('createOrder');
        Route::post('getAllOrder',[OrderProcessController::class,'getAllOrder'])->name('getAllOrder');
        Route::post('getTaxAndChargeCalculation',[CalculationController::class,'getTaxAndChargeCalculation'])->name('getTaxAndChargeCalculation');
    });
});

