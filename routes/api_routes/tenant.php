<?php

use App\Http\Controllers\TenantController;

Route::get('tenants/list', [TenantController::class, 'list'])->name('tenants.list');
Route::apiResource('tenants', TenantController::class);
