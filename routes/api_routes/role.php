<?php

use App\Http\Controllers\RoleController;

Route::get('roles/list', [RoleController::class, 'list'])->name('roles.list');
Route::get('roles/permissions/list', [RoleController::class, 'permissionList'])->name('roles.permissions.list');
Route::apiResource('roles', RoleController::class);
