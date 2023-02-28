<?php

use App\Http\Controllers\TransmittalController;

Route::get('transmittal', [TransmittalController::class, 'index'])->name('signatories.index');
