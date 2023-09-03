<?php

use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('messages')->group(function () {
   Route::get('', [MessageController::class, 'index']);
   Route::post('', [MessageController::class, 'store']);
   Route::get('/{id}', [MessageController::class, 'show'])->whereNumber('id');
   Route::put('/{id}', [MessageController::class, 'update'])->whereNumber('id');
   Route::delete('/{id}', [MessageController::class, 'destroy'])->whereNumber('id');
});
