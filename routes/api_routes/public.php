<?php

use App\Http\Controllers\ReportBuilderController;
use App\Http\Controllers\TenantSettingController;

Route::post('settings/tenant', [TenantSettingController::class, 'getTenantSettingsByDomain'])->name('settings.tenant.find-by-domain');
Route::post('report-builders/{report_builder}/upload-files', [ReportBuilderController::class, 'uploadFiles'])->name('report-builders.upload-files');
