<?php

use App\Http\Controllers\Api\V1\GooglePlacesController;
use App\Http\Controllers\Api\V1\DioceseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('dioceses/search', [DioceseController::class, 'search']);
    Route::get('google-places/search', [GooglePlacesController::class, 'search']);
    Route::get('google-places/details', [GooglePlacesController::class, 'details']);
});
