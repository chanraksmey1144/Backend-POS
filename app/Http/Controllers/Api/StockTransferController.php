<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockTransferRequest;
use App\Http\Requests\UpdateStockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\TransferItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    /**
     * Display a listing of stock transfers with status & warehouse filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'creator', 'items']);
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
        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->query('to_date'));
        }
        // Search by transfer number
        if ($request->filled('search')) {
            $query->where('transfer_number', 'like', "%{$request->query('search')}%");
        }
        $transfers = $query->latest()->paginate($request->integer('per_page', 15));
        return StockTransferResource::collection($transfers);
    }
    /**
     * Create a new stock transfer request (with optional line items).
     */
    public function store(StoreStockTransferRequest $request): JsonResponse
    {
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);

        $transfer = DB::transaction(function () use ($data, $items): StockTransfer {
            $transfer = StockTransfer::create($data);
            $this->insertItems($transfer, $items);
            return $transfer;
        });

        $transfer->load(['sourceWarehouse', 'destinationWarehouse', 'creator', 'items']);

        return (new StockTransferResource($transfer))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified stock transfer.
     */
    public function show(StockTransfer $stockTransfer): StockTransferResource
    {
        $stockTransfer->load(['sourceWarehouse', 'destinationWarehouse', 'creator', 'items']);
        return new StockTransferResource($stockTransfer);
    }
    /**
     * Update the specified stock transfer. When marked as received the stock
     * physically moves from the source warehouse to the destination.
     */
    public function update(UpdateStockTransferRequest $request, StockTransfer $stockTransfer): StockTransferResource
    {
        DB::transaction(function () use ($request, $stockTransfer) {
            $data = $request->validated();
            $items = $data['items'] ?? [];
            unset($data['items']);

            $previousStatus = $stockTransfer->status;
            $stockTransfer->update($data);

            if (!empty($items)) {
                $this->insertItems($stockTransfer, $items);
            }

            if ($stockTransfer->status === 'received' && $previousStatus !== 'received') {
                $this->applyStockTransfer($stockTransfer);
            } elseif ($previousStatus === 'received' && $stockTransfer->status !== 'received') {
                $this->reverseStockTransfer($stockTransfer);
            }
        });

        $stockTransfer->load(['sourceWarehouse', 'destinationWarehouse', 'creator', 'items']);

        return new StockTransferResource($stockTransfer);
    }
    /**
     * Cancel or delete a stock transfer (received transfers are reversed).
     */
    public function destroy(StockTransfer $stockTransfer): JsonResponse
    {
        DB::transaction(function () use ($stockTransfer) {
            if ($stockTransfer->status === 'received') {
                $this->reverseStockTransfer($stockTransfer);
            }
            $stockTransfer->delete();
        });

        return response()->json([
            'message' => 'Stock transfer deleted successfully.',
        ], 200);
    }

    /**
     * Persist the transfer line items and refresh the item_count.
     */
    private function insertItems(StockTransfer $transfer, array $items): void
    {
        foreach ($items as $item) {
            $item['transfer_id'] = $transfer->id;
            TransferItem::create($item);
        }

        $transfer->update(['item_count' => $transfer->items()->count()]);
    }

    /**
     * Move stock from the source warehouse to the destination.
     */
    private function applyStockTransfer(StockTransfer $transfer): void
    {
        foreach ($transfer->items as $item) {
            $quantity = (float) $item->quantity;

            if ($item->variant_id) {
                $model = ProductVariant::whereKey($item->variant_id)->lockForUpdate()->first();
                $model?->decrement('stock', $quantity);
            } elseif ($item->product_id) {
                $model = Product::whereKey($item->product_id)->lockForUpdate()->first();
                $model?->decrement('stock', $quantity);
            }

            StockMovement::create([
                'product_id'    => $item->product_id,
                'variant_id'    => $item->variant_id,
                'warehouse_id'  => $transfer->source_warehouse_id,
                'user_id'       => $transfer->created_by,
                'movement_date' => now(),
                'type'          => 'transfer',
                'quantity'      => -$quantity,
                'reference'     => $transfer->transfer_number,
                'note'          => "Transferred OUT to warehouse #{$transfer->destination_warehouse_id}.",
            ]);

            StockMovement::create([
                'product_id'    => $item->product_id,
                'variant_id'    => $item->variant_id,
                'warehouse_id'  => $transfer->destination_warehouse_id,
                'user_id'       => $transfer->created_by,
                'movement_date' => now(),
                'type'          => 'transfer',
                'quantity'      => $quantity,
                'reference'     => $transfer->transfer_number,
                'note'          => "Transferred IN from warehouse #{$transfer->source_warehouse_id}.",
            ]);
        }
    }

    /**
     * Reverse a received transfer (restore source, deduct destination).
     */
    private function reverseStockTransfer(StockTransfer $transfer): void
    {
        foreach ($transfer->items as $item) {
            $quantity = (float) $item->quantity;

            if ($item->variant_id) {
                ProductVariant::whereKey($item->variant_id)->lockForUpdate()->increment('stock', $quantity);
            } elseif ($item->product_id) {
                Product::whereKey($item->product_id)->lockForUpdate()->increment('stock', $quantity);
            }

            StockMovement::create([
                'product_id'    => $item->product_id,
                'variant_id'    => $item->variant_id,
                'warehouse_id'  => $transfer->source_warehouse_id,
                'user_id'       => $transfer->created_by,
                'movement_date' => now(),
                'type'          => 'adjustment',
                'quantity'      => $quantity,
                'reference'     => $transfer->transfer_number,
                'note'          => 'Transfer receipt reversed.',
            ]);
        }
    }
}