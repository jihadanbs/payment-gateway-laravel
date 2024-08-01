<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/donation', [DonationController::class, 'store']);
Route::post('/midtrans/notification', [DonationController::class, 'notification']);
