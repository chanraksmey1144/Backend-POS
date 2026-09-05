<?php

use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerGroupController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::apiResource('branches', BranchController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('registers', RegisterController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('users', UserController::class);

// Permissions for a specific role
Route::get('roles/{role}/permissions', [RolePermissionController::class, 'index']);
Route::post('roles/{role}/permissions', [RolePermissionController::class, 'sync']);


Route::apiResource('customer-groups', CustomerGroupController::class);
Route::apiResource('customers', CustomerController::class);

Route::apiResource('categories', CategoryController::class);
Route::apiResource('brands', BrandController::class);

Route::apiResource('units', UnitController::class);

Route::apiResource('products', ProductController::class);

Route::apiResource('product-variants', ProductVariantController::class);
