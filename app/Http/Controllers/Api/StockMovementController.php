<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements with filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = StockMovement::with(['product', 'variant', 'warehouse', 'user']);
        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }
        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->query('warehouse_id'));
        }
        // Filter by movement type (purchase, sale, damage, adjustment, etc.)
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }
        // Search by reference or note
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }
        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('movement_date', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('movement_date', '<=', $request->query('to_date'));
        }
        $movements = $query->latest('movement_date')->paginate($request->integer('per_page', 20));
        return StockMovementResource::collection($movements);
    }
    /**
     * Record a stock movement and automatically update product stock level.
     */
    public function store(StoreStockMovementRequest $request): JsonResponse
    {
        $movement = DB::transaction(function () use ($request) {
            $data = $request->validated();

            $model = !empty($data['variant_id'])
                ? ProductVariant::lockForUpdate()->findOrFail($data['variant_id'])
                : Product::lockForUpdate()->findOrFail($data['product_id']);

            // Auto-calculate before_stock and after_stock if not provided
            if (!isset($data['before_stock'])) {
                $data['before_stock'] = (float) $model->stock;
            }
            if (!isset($data['after_stock'])) {
                $data['after_stock'] = (float) ($data['before_stock'] + $data['quantity']);
            }
            // Update stock level in database
            if (($model->track_inventory ?? true) && $data['after_stock'] >= 0) {
                $model->update(['stock' => $data['after_stock']]);
            }
            // Create movement record
            return StockMovement::create($data);
        });
        $movement->load(['product', 'warehouse', 'user']);
        return (new StockMovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified stock movement record.
     */
    public function show(StockMovement $stockMovement): StockMovementResource
    {
        $stockMovement->load(['product', 'variant', 'warehouse', 'user']);
        return new StockMovementResource($stockMovement);
    }
}
