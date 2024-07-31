<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/donation', [DonationController::class, 'index']);
