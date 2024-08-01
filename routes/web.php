<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonationController;

Route::get('/', [DonationController::class, 'index']);
Route::get('/donation', [DonationController::class, 'create']);
