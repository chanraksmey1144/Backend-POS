<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchase orders with filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Purchase::with(['supplier', 'branch', 'warehouse']);
        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->query('supplier_id'));
        }
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }
        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->query('warehouse_id'));
        }
        // Filter by status (draft, ordered, received, etc.)
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Filter by payment status (paid, partial, unpaid)
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }
        // Search by purchase order number
        if ($request->filled('search')) {
            $query->where('purchase_number', 'like', "%{$request->query('search')}%");
        }
        $purchases = $query->latest('order_date')->paginate($request->integer('per_page', 15));
        return PurchaseResource::collection($purchases);
    }
    /**
     * Create a new purchase order.
     */
    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $purchase = Purchase::create($request->validated());
        $purchase->load(['supplier', 'branch', 'warehouse']);
        return (new PurchaseResource($purchase))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified purchase order.
     */
    public function show(Purchase $purchase): PurchaseResource
    {
        $purchase->load(['supplier', 'branch', 'warehouse', 'creator']);
        return new PurchaseResource($purchase);
    }
    /**
     * Update the specified purchase order.
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase): PurchaseResource
    {
        $purchase->update($request->validated());
        $purchase->load(['supplier', 'branch', 'warehouse']);
        return new PurchaseResource($purchase);
    }
    /**
     * Cancel or delete a purchase order.
     */
    public function destroy(Purchase $purchase): JsonResponse
    {
        $purchase->delete();
        return response()->json([
            'message' => 'Purchase order deleted successfully.',
        ], 200);
    }
}
