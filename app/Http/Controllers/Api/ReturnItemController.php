<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReturnItemRequest;
use App\Http\Requests\UpdateReturnItemRequest;
use App\Http\Resources\ReturnItemResource;
use App\Models\ReturnItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReturnItemController extends Controller
{
  /**
     * Display a listing of items for a specific return.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ReturnItem::with(['product', 'variant']);
        if ($request->filled('return_id')) {
            $query->where('return_id', $request->query('return_id'));
        }
        $items = $query->paginate($request->integer('per_page', 50));
        return ReturnItemResource::collection($items);
    }
    /**
     * Add a returned item to a return record.
     */
    public function store(StoreReturnItemRequest $request): JsonResponse
    {
        $item = ReturnItem::create($request->validated());
        return (new ReturnItemResource($item))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified return item.
     */
    public function show(ReturnItem $returnItem): ReturnItemResource
    {
        return new ReturnItemResource($returnItem);
    }
    /**
     * Update the specified return item.
     */
    public function update(UpdateReturnItemRequest $request, ReturnItem $returnItem): ReturnItemResource
    {
        $returnItem->update($request->validated());
        return new ReturnItemResource($returnItem);
    }
    /**
     * Remove the specified return item.
     */
    public function destroy(ReturnItem $returnItem): JsonResponse
    {
        $returnItem->delete();
        return response()->json([
            'message' => 'Return item deleted successfully.',
        ], 200);
    }
}
