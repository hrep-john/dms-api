<?php

use App\Http\Controllers\TenantSettingController;

Route::post('settings/tenant', [TenantSettingController::class, 'getTenantSettingsByDomain'])->name('settings.tenant.find-by-domain');
