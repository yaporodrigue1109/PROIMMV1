<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\LocataireController;
use App\Http\Controllers\Api\Mobile\ProprietaireController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->middleware('throttle:60,1')->group(function () {
    Route::prefix('auth/{role}')->whereIn('role', ['locataire', 'proprietaire'])->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    });

    Route::middleware('mobile.auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::prefix('locataire')->middleware('mobile.auth:locataire')->group(function () {
        Route::get('agencies', [LocataireController::class, 'agencies']);
        Route::post('agencies/attach', [LocataireController::class, 'attachAgency']);
        Route::get('agencies/{agency}', [LocataireController::class, 'agency']);
        Route::get('agencies/{agency}/properties', [LocataireController::class, 'properties']);
        Route::get('agencies/{agency}/receipts', [LocataireController::class, 'receipts']);
    });

    Route::prefix('proprietaire')->middleware('mobile.auth:proprietaire')->group(function () {
        Route::get('agencies', [ProprietaireController::class, 'agencies']);
        Route::post('agencies/attach', [ProprietaireController::class, 'attachAgency']);
        Route::get('agencies/{agency}/dashboard', [ProprietaireController::class, 'dashboard']);
        Route::get('agencies/{agency}/properties', [ProprietaireController::class, 'properties']);
        Route::get('agencies/{agency}/properties/{property}', [ProprietaireController::class, 'property']);
        Route::get('agencies/{agency}/properties/{property}/tenants', [ProprietaireController::class, 'tenants']);
        Route::get('agencies/{agency}/properties/{property}/arrears', [ProprietaireController::class, 'arrears']);
        Route::get('agencies/{agency}/properties/{property}/payouts', [ProprietaireController::class, 'payouts']);
    });
});
