<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AuthController;

//Route::get('/', function () {
//    return view('admin.auth.login');
//});


Route::get('test', [AuthController::class, 'showLoginForm'])->name('login');

