<?php

use App\Http\Controllers\TenantSettingController;

Route::apiResource('settings/tenant', TenantSettingController::class);
