<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseItemRequest;
use App\Http\Requests\UpdatePurchaseItemRequest;
use App\Http\Resources\PurchaseItemResource;
use App\Models\PurchaseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseItemController extends Controller
{
  /**
     * Display a listing of items for a specific purchase order.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PurchaseItem::with(['product', 'variant']);
        if ($request->filled('purchase_id')) {
            $query->where('purchase_id', $request->query('purchase_id'));
        }
        $items = $query->paginate($request->integer('per_page', 50));
        return PurchaseItemResource::collection($items);
    }
    /**
     * Add an item to a purchase order.
     */
    public function store(StorePurchaseItemRequest $request): JsonResponse
    {
        $item = PurchaseItem::create($request->validated());
        return (new PurchaseItemResource($item))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified purchase item.
     */
    public function show(PurchaseItem $purchaseItem): PurchaseItemResource
    {
        return new PurchaseItemResource($purchaseItem);
    }
    /**
     * Update the specified purchase item (e.g. updating received_quantity).
     */
    public function update(UpdatePurchaseItemRequest $request, PurchaseItem $purchaseItem): PurchaseItemResource
    {
        $purchaseItem->update($request->validated());
        return new PurchaseItemResource($purchaseItem);
    }
    /**
     * Remove the specified purchase item.
     */
    public function destroy(PurchaseItem $purchaseItem): JsonResponse
    {
        $purchaseItem->delete();
        return response()->json([
            'message' => 'Purchase item deleted successfully.',
        ], 200);
    }
}
