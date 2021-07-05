<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::get('login', [AuthController::class, 'index'])->name('login');
Route::post('login/verification', [AuthController::class, 'loginVerification']);
Route::post('login', [AuthController::class, 'login']);
Route::get('register', [UserController::class, 'getRegister']);
Route::post('register', [UserController::class, 'register']);
Route::middleware(['auth:api'])->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::patch('{user}/update', [UserController::class, 'update']);
    });
    Route::post('logout', [AuthController::class, 'logout']);
    Route::middleware(['scope:admin'])->group(function () {
        Route::post('sendInvitation', [UserController::class, 'sendInvitation']);
    });
});