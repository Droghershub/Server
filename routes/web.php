<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\MagicService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::any('{any}', function (Request $request) {
    $body = [
        'message' => 'Server is up and running.'
    ];
    return MagicService::getSuccessResponse($body, $request);
})->where('any', '.*');