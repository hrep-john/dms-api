<?php

use App\Http\Controllers\TransmittalController;

Route::get('transmittal/{id}', [TransmittalController::class, 'index'])->name('transmittal.index');
