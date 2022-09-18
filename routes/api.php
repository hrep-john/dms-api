<?php

use Illuminate\Support\Facades\Route;

/** Authentication routes **/
Route::prefix('/auth')->group(base_path('routes/api_routes/auth.php'));

/** Profile routes */
Route::prefix('/profile')->group(base_path('routes/api_routes/profile.php'));

/** Public Routes */
Route::prefix('/public')->group(base_path('routes/api_routes/public.php'));

/** Dashboard Routes */
Route::middleware('auth:sanctum')->group(base_path('routes/api_routes/dashboard.php'));

/** Document routes */
Route::middleware('auth:sanctum')->group(base_path('routes/api_routes/document.php'));

/** Tenants Routes */
Route::middleware('auth:sanctum')->group(base_path('routes/api_routes/tenant.php'));

/** Tenant Settings Routes */
Route::middleware('auth:sanctum')->group(base_path('routes/api_routes/tenant_setting.php'));

/** User Defined Field Routes */
Route::middleware('auth:sanctum')->group(base_path('routes/api_routes/udf.php'));

/** User Routes */
Route::middleware('auth:sanctum')->group(base_path('routes/api_routes/user.php'));

/** Roles Routes */
Route::middleware('auth:sanctum')->group(base_path('routes/api_routes/role.php'));

/** Report Builders Routes */
Route::middleware('auth:sanctum')->group(base_path('routes/api_routes/report_builder.php'));
