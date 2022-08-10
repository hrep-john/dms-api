<?php

use App\Http\Controllers\FileStorageController;
use App\Http\Controllers\ProfileController;

/** GET    /api/profile/me    Show current auth user's profile info    Private **/
Route::middleware('auth:sanctum')->get('me', [ProfileController::class, 'show'])->name('show-profile');

/** PUT    /api/profile/me    Update current auth user's profile info    Private **/
Route::middleware('auth:sanctum')->put('me', [ProfileController::class, 'update'])->name('update-profile');

/** PUT    /api/profile/me/change-password    Change current auth user's password    Private **/
Route::middleware('auth:sanctum')->put('me/change-password', [ProfileController::class, 'changePassword'])->name('change-password');

/** POST    /api/profile/me/change-password    Upload profile picture    Private **/
Route::middleware('auth:sanctum')
    ->post('me/upload-profile-picture', [ProfileController::class, 'uploadProfilePicture'])
    ->name('upload-profile-picture');

/** GET    /api/profile/me/profile-picture    Show current auth user's profile picture    Private **/
Route::middleware('auth:sanctum')->get('me/profile-picture ', [FileStorageController::class, 'showProfilePicture'])->name('profile-picture');
