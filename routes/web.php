<?php

use App\Http\Apis\JobController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    Route::get('jobs', [JobController::class, 'index']);
});
