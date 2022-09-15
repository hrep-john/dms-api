<?php

use App\Http\Controllers\RoleController;

Route::get('roles/list', [RoleController::class, 'list'])->name('udfs.list');
Route::apiResource('roles', RoleController::class);
