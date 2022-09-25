<?php

use App\Http\Controllers\UserController;

Route::get('users/list', [UserController::class, 'list'])->name('users.list');
Route::apiResource('users', UserController::class);
