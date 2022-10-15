<?php

use App\Http\Controllers\TenantSettingController;

Route::post('settings/tenant/sync', [TenantSettingController::class, 'sync'])->name('tenant.settings.sync');
Route::post('settings/tenant/upload', [TenantSettingController::class, 'upload'])->name('tenant.settings.upload');
Route::apiResource('settings/tenant', TenantSettingController::class);
