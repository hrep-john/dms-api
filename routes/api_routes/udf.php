<?php

use App\Http\Controllers\UdfController;

Route::get('udfs/all', [UdfController::class, 'all'])->name('udfs.all');
Route::apiResource('udfs', UdfController::class);
