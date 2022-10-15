<?php

use App\Http\Controllers\CustomReportController;

Route::get('custom-reports/{slug}', [CustomReportController::class, 'report'])->name('custom-report.report');
