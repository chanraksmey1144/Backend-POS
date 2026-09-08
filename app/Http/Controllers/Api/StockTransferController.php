<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockTransferRequest;
use App\Http\Requests\UpdateStockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Models\StockTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockTransferController extends Controller
{
   /**
     * Display a listing of stock transfers with status & warehouse filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'creator']);
        // Filter by status (draft, approved, in_transit, received, etc.)
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Filter by source warehouse
        if ($request->filled('source_warehouse_id')) {
            $query->where('source_warehouse_id', $request->query('source_warehouse_id'));
        }
        // Filter by destination warehouse
        if ($request->filled('destination_warehouse_id')) {
            $query->where('destination_warehouse_id', $request->query('destination_warehouse_id'));
        }
        // Search by transfer number
        if ($request->filled('search')) {
            $query->where('transfer_number', 'like', "%{$request->query('search')}%");
        }
        $transfers = $query->latest()->paginate($request->integer('per_page', 15));
        return StockTransferResource::collection($transfers);
    }
    /**
     * Create a new stock transfer request.
     */
    public function store(StoreStockTransferRequest $request): JsonResponse
    {
        $transfer = StockTransfer::create($request->validated());
        $transfer->load(['sourceWarehouse', 'destinationWarehouse', 'creator']);
        return (new StockTransferResource($transfer))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified stock transfer.
     */
    public function show(StockTransfer $stockTransfer): StockTransferResource
    {
        $stockTransfer->load(['sourceWarehouse', 'destinationWarehouse', 'creator']);
        return new StockTransferResource($stockTransfer);
    }
    /**
     * Update the specified stock transfer (e.g. status change to approved / received).
     */
    public function update(UpdateStockTransferRequest $request, StockTransfer $stockTransfer): StockTransferResource
    {
        $stockTransfer->update($request->validated());
        $stockTransfer->load(['sourceWarehouse', 'destinationWarehouse', 'creator']);
        return new StockTransferResource($stockTransfer);
    }
    /**
     * Cancel or delete a stock transfer.
     */
    public function destroy(StockTransfer $stockTransfer): JsonResponse
    {
        $stockTransfer->delete();
        return response()->json([
            'message' => 'Stock transfer deleted successfully.',
        ], 200);
    }
}
