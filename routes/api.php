<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    // Auth routes...
    //Auth
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});
Route::post('/guest', [GuestController::class, 'store']);

Route::group(['middleware' => 'jwt.verify', 'prefix' => 'home'], function ($router) {
    Route::get('/card', [DashboardController::class, 'getCardData']);
});
Route::group(['middleware' => 'jwt.verify', 'prefix' => 'guest'], function ($router) {
    Route::get('/', [GuestController::class, 'getAllGuest']);
    Route::get('/{id}', [GuestController::class, 'getGuestById']);
});
