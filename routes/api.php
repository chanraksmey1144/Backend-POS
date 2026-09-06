<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerGroupController;
use App\Http\Controllers\Api\HeldSaleController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\ReturnItemController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SaleItemController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are loaded by the RouteServiceProvider and prefixed with /api.
| Public:  POST /auth/login
| Protected (auth:sanctum): everything else, expects `Authorization: Bearer <token>`.
|
| Each apiResource exposes the standard REST verbs:
|
|   GET    /api/{resource}           -> index   (paginated + filterable)
|   POST   /api/{resource}           -> store
|   GET    /api/{resource}/{id}      -> show
|   PUT|PATCH /api/{resource}/{id}   -> update
|   DELETE /api/{resource}/{id}      -> destroy
|
| List endpoints support query filters (search, per_page, foreign keys,
| status, date ranges, etc.) as implemented by each controller.
|
|--------------------------------------------------------------------------
*/

// ---------------------------------------------------------------------------
// Authentication
// ---------------------------------------------------------------------------

Route::post('auth/login', [AuthController::class, 'login']);

// ---------------------------------------------------------------------------
// Protected API
// ---------------------------------------------------------------------------

Route::middleware('auth:sanctum')->group(function () {

    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // -----------------------------------------------------------------------
    // Access Control & Users
    // -----------------------------------------------------------------------

    Route::apiResource('branches', BranchController::class);
    Route::apiResource('warehouses', WarehouseController::class);
    Route::apiResource('registers', RegisterController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);

    // Permissions assigned to a specific role
    Route::prefix('roles')
        ->name('roles.')
        ->group(function () {
            Route::get('{role}/permissions', [RolePermissionController::class, 'index'])
                ->name('permissions.index');
            Route::match(['post', 'put'], '{role}/permissions', [RolePermissionController::class, 'sync'])
                ->name('permissions.sync');
        });

    // -----------------------------------------------------------------------
    // Customers & Suppliers
    // -----------------------------------------------------------------------

    Route::apiResource('customer-groups', CustomerGroupController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('suppliers', SupplierController::class);

    // -----------------------------------------------------------------------
    // Catalog & Inventory
    // -----------------------------------------------------------------------

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('product-variants', ProductVariantController::class);

    // -----------------------------------------------------------------------
    // Sales & Returns
    // -----------------------------------------------------------------------

    Route::apiResource('sales', SaleController::class);
    Route::apiResource('sale-items', SaleItemController::class);
    Route::apiResource('held-sales', HeldSaleController::class);
    Route::apiResource('returns', ReturnController::class);
    Route::apiResource('return-items', ReturnItemController::class);

    // -----------------------------------------------------------------------
    // Procurement
    // -----------------------------------------------------------------------

    Route::apiResource('purchases', PurchaseController::class);

    // -----------------------------------------------------------------------
    // Fallback (returns JSON for unknown API endpoints)
    // -----------------------------------------------------------------------

    Route::fallback(function () {
        return response()->json([
            'message' => 'Not Found. The requested API endpoint does not exist.',
        ], 404);
    });
});