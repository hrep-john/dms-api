<?php

use App\Http\Controllers\CustomReportController;

Route::get('custom-report/{slug}', [CustomReportController::class, 'report'])->name('custom-report.report');
