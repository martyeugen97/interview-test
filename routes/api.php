<?php

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Middleware\ApiAuthentication;
use Illuminate\Support\Facades\Route;

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


Route::prefix('v1')->middleware(ApiAuthentication::class)->group(function() {
    Route::match(['get', 'post'], '', [ApiController::class, 'index']);
});