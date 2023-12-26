<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HandcartController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\SetJsonResponseMiddleware;
use App\Services\MagicService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->middleware('validate.app')->group(function () {

    Route::prefix('authenticate')->group(function () {
        Route::post('in', [AuthController::class, 'in']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('recover', [AuthController::class, 'recover']);
        Route::post('verify', [AuthController::class, 'verify']);
    });

    Route::middleware('validate.auth')->group(function () {

        Route::prefix('user')->group(function () {
            Route::post('out', [UserController::class, 'out']);
            Route::post('link', [UserController::class, 'link']);
            Route::post('update', [UserController::class, 'update']);
            Route::post('delete', [UserController::class, 'delete']);
        });

        Route::prefix('address')->group(function () {
            Route::post('search', [AddressController::class, 'search']);
            Route::post('list', [AddressController::class, 'list']);
            Route::post('show', [AddressController::class, 'show']);
            Route::post('add', [AddressController::class, 'add']);
            Route::post('update', [AddressController::class, 'update']);
            Route::post('delete', [AddressController::class, 'delete']);
        });

        Route::prefix('notification')->group(function () {
            Route::post('list', [NotificationController::class, 'list']);
            Route::post('flag', [NotificationController::class, 'flag']);
        });

        Route::prefix('postcode')->group(function () {
            Route::post('show', [AddressController::class, 'postcode']);
        });

        Route::prefix('brand')->group(function () {
            Route::post('list', [BrandController::class, 'list']);
        });

        Route::prefix('category')->group(function () {
            Route::post('list', [CategoryController::class, 'list']);
        });

        Route::prefix('product')->group(function () {
            // TODO: product list api (home page products) API
            // TODO: rate a product API
            Route::post('search', [ProductController::class, 'search']);
            Route::post('show', [ProductController::class, 'show']);
        });

        // TODO: payment method CRUD API

        Route::prefix('order')->group(function () {
            Route::post('search', [OrderController::class, 'search']);
            Route::post('list', [OrderController::class, 'list']);
            Route::post('show', [OrderController::class, 'show']);
            Route::post('place', [OrderController::class, 'place']);
            Route::post('cancel', [OrderController::class, 'cancel']);
        });

        Route::prefix('handcart')->group(function () {
            Route::post('search', [HandcartController::class, 'search']);
            Route::post('list', [HandcartController::class, 'list']);
            Route::post('add', [HandcartController::class, 'add']);
            Route::post('update', [HandcartController::class, 'update']);
            Route::post('delete', [HandcartController::class, 'delete']);
        });

        Route::prefix('favorite')->group(function () {
            Route::post('search', [FavoriteController::class, 'search']);
            Route::post('list', [FavoriteController::class, 'list']);
            Route::post('add', [FavoriteController::class, 'add']);
            Route::post('update', [FavoriteController::class, 'update']);
            Route::post('delete', [FavoriteController::class, 'delete']);
        });

    });
});

Route::any('{any}', function (Request $request) {
    return MagicService::getErrorResponse('RESOURCE_NOT_FOUND', null, $request);
})->where('any', '.*');