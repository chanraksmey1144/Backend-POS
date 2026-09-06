<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleItemRequest;
use App\Http\Requests\UpdateSaleItemRequest;
use App\Http\Resources\SaleItemResource;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SaleItemController extends Controller
{
    /**
     * Display a listing of items with sale_id filter.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SaleItem::with(['product', 'variant']);
        // Filter items for a specific sale / receipt
        if ($request->filled('sale_id')) {
            $query->where('sale_id', $request->query('sale_id'));
        }
        $items = $query->paginate($request->integer('per_page', 50));
        return SaleItemResource::collection($items);
    }
    /**
     * Store a newly created item in a sale.
     */
    public function store(StoreSaleItemRequest
     $request): JsonResponse
    {
        $item = SaleItem::create($request->validated());
        return (new SaleItemResource($item))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified sale item.
     */
    public function show(SaleItem $saleItem): SaleItemResource
    {
        return new SaleItemResource($saleItem);
    }
    /**
     * Update the specified sale item.
     */
    public function update(UpdateSaleItemRequest $request, SaleItem $saleItem): SaleItemResource
    {
        $saleItem->update($request->validated());
        return new SaleItemResource($saleItem);
    }
    /**
     * Remove the specified sale item.
     */
    public function destroy(SaleItem $saleItem): JsonResponse
    {
        $saleItem->delete();
        return response()->json([
            'message' => 'Sale item removed successfully.',
        ], 200);
    }
}