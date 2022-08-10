<?php

use App\Http\Controllers\DropzoneController;

/** POST    /api/sample    Create new sample resource    Private**/
Route::post('', [DropzoneController::class, 'dropzoneStore'])->name('dropzone');

Route::get('bucket_url', [DropzoneController::class, 'index'])->name('bucket_url');
