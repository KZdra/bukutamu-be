<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\UnitController;


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

Route::get('guest/export-pdf', [GuestController::class, 'exportPdf']);
Route::get('guest/export-excel', [GuestController::class, 'exportExcel']);

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    // Auth routes...
    //Auth
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::group([ 'prefix' => 'home'], function ($router) {
    Route::get('/card', [DashboardController::class, 'getCardData']);
    Route::get('/last-guest', [DashboardController::class, 'getLastTamu']);
    Route::get('/last-institutions', [DashboardController::class, 'getLastPerusahaan']);
});
Route::group([ 'prefix' => 'guest'], function ($router) {
    Route::get('/', [GuestController::class, 'getAllGuest']);
    Route::post('/', [GuestController::class, 'store']);
    Route::get('/institution', [GuestController::class, 'getInstitutionList']);
    Route::get('/status', [GuestController::class, 'getallStatus']);
    Route::get('/unit', [GuestController::class, 'getAllUnit']);
    Route::get('/{id}', [GuestController::class, 'getGuestById']);
});

Route::prefix('unit')->group(function () {
    Route::get('/', [UnitController::class, 'index']);
    Route::post('/', [UnitController::class, 'store']);
    Route::put('/{id}', [UnitController::class, 'update']);
    Route::delete('/{id}', [UnitController::class, 'destroy']);
});