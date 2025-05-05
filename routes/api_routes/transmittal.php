<?php

use App\Http\Controllers\TransmittalController;

Route::get('transmittal/{id}', [TransmittalController::class, 'index'])->name('transmittal.index');
Route::post('transmittal/upload', [TransmittalController::class, 'upload'])->name('transmittal.upload');
Route::post('transmittal/print-transmittal/{id}', [TransmittalController::class, 'print'])->name('transmittal.print');
