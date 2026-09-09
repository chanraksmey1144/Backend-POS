<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CashRegisterSessionController;
use App\Http\Controllers\Api\CashTransactionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerGroupController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\HeldSaleController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\PurchaseItemController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\ReturnItemController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SaleItemController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\StockTransferController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TransferItemController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Support\Facades\Route;



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
    Route::apiResource('transfer-items', TransferItemController::class);

    // -----------------------------------------------------------------------
    // Procurement & Finance
    // -----------------------------------------------------------------------

    Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('purchase-items', PurchaseItemController::class);
    Route::apiResource('stock-movements', StockMovementController::class)->only(['index', 'store', 'show']);
    Route::apiResource('stock-transfers', StockTransferController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('cash-register-sessions', CashRegisterSessionController::class);
    Route::apiResource('cash-transactions', CashTransactionController::class);

    // -----------------------------------------------------------------------
    // 12. Notifications & Audit
    // -----------------------------------------------------------------------

    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::apiResource('notifications', NotificationController::class)->except(['update']);

    // Audit Logs (Read & Append only)
    Route::apiResource('audit-logs', AuditLogController::class)->only(['index', 'show', 'store']);

    // -----------------------------------------------------------------------
    // 13. Settings (Supports the 7 Tabs)
    // -----------------------------------------------------------------------
    Route::get('settings', [SettingController::class, 'index']);
    Route::get('settings/{key}', [SettingController::class, 'show']);
    Route::match(['post', 'put'], 'settings', [SettingController::class, 'update']);

    // -----------------------------------------------------------------------
    // Dashboard & Reports
    // -----------------------------------------------------------------------

    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('profit', [ReportController::class, 'profit'])->name('profit');
    });

    // ------ -----------------------------------------------------------------
    // Fallback (returns JSON for unknown API endpoints)
    // -----------------------------------------------------------------------

    Route::fallback(function () {
        return response()->json([
            'message' => 'Not Found. The requested API endpoint does not exist.',
        ], 404);
    });
});
