<?php

use App\Http\Controllers\SignatoriesController;

Route::get('signatories', [SignatoriesController::class, 'index'])->name('signatories.index');
Route::post('signatories', [SignatoriesController::class, 'store'])->name('signatories.store');
Route::get('signatories/{id}', [SignatoriesController::class, 'show'])->name('signatories.show');
Route::put('signatories', [SignatoriesController::class, 'update'])->name('signatories.update');
Route::delete('signatories/{id}', [SignatoriesController::class, 'destroy'])->name('signatories.destroy');
