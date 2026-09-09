<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferItemRequest;
use App\Http\Requests\UpdateTransferItemRequest;
use App\Http\Resources\TransferItemResource;
use App\Models\StockTransfer;
use App\Models\TransferItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransferItemController extends Controller
{
   /**
     * Display a listing of items for a specific stock transfer.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TransferItem
        ::with(['product', 'variant']);
        if ($request->filled('transfer_id')) {
            $query->where('transfer_id', $request->query('transfer_id'));
        }
        $items = $query->paginate($request->integer('per_page', 50));
        return TransferItemResource::collection($items);
    }
    /**
     * Add a product item to a stock transfer.
     */
    public function store(StoreTransferItemRequest $request): JsonResponse
    {
        $item = TransferItem::create($request->validated());
        $this->refreshItemCount($item->transfer_id);
        $item->load(['product', 'variant']);
        return (new TransferItemResource($item))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified transfer item.
     */
    public function show(TransferItem $transferItem): TransferItemResource
    {
        $transferItem->load(['product', 'variant']);
        return new TransferItemResource($transferItem);
    }
    /**
     * Update the transfer item quantity.
     */
    public function update(UpdateTransferItemRequest $request, TransferItem $transferItem): TransferItemResource
    {
        $transferItem->update($request->validated());
        $transferItem->load(['product', 'variant']);
        return new TransferItemResource($transferItem);
    }
    /**
     * Remove an item from the stock transfer.
     */
    public function destroy(TransferItem $transferItem): JsonResponse
    {
        $transferId = $transferItem->transfer_id;
        $transferItem->delete();
        $this->refreshItemCount($transferId);
        return response()->json([
            'message' => 'Transfer item deleted successfully.',
        ], 200);
    }

    /**
     * Keep StockTransfer.item_count in sync after adding / removing items.
     */
    private function refreshItemCount(int $transferId): void
    {
        StockTransfer::whereKey($transferId)?->update([
            'item_count' => TransferItem::where('transfer_id', $transferId)->count(),
        ]);
    }
}
