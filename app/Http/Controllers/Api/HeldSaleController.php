<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHeldSaleRequest;
use App\Http\Requests\UpdateHeldSaleRequest;
use App\Http\Resources\HeldSaleResource;
use App\Models\HeldSale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HeldSaleController extends Controller
{
    /**
     * Display a listing of all currently held carts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = HeldSale::with(['customer', 'cashier']);
        // Filter by cashier
        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->query('cashier_id'));
        }
        // Search by hold number
        if ($request->filled('search')) {
            $query->where('hold_number', 'like', "%{$request->query('search')}%");
        }
        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->query('to_date'));
        }
        $heldSales = $query->latest('created_at')->paginate($request->integer('per_page', 15));
        return HeldSaleResource::collection($heldSales);
    }
    /**
     * Hold / Park a cart.
     */
    public function store(StoreHeldSaleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_at'] = $data['created_at'] ?? now();
        $heldSale = HeldSale::create($data);
        $heldSale->load(['customer', 'cashier']);
        return (new HeldSaleResource($heldSale))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * View details of a specific held cart.
     */
    public function show(HeldSale $heldSale): HeldSaleResource
    {
        $heldSale->load(['customer', 'cashier']);
        return new HeldSaleResource($heldSale);
    }
    /**
     * Update a held cart.
     */
    public function update(UpdateHeldSaleRequest $request, HeldSale $heldSale): HeldSaleResource
    {
        $heldSale->update($request->validated());
        $heldSale->load(['customer', 'cashier']);
        return new HeldSaleResource($heldSale);
    }
    /**
     * Resume / delete a held cart (called after cashier resumes or cancels cart).
     */
    public function destroy(HeldSale $heldSale): JsonResponse
    {
        $heldSale->delete();
        return response()->json([
            'message' => 'Held cart released / deleted successfully.',
        ], 200);
    }
}
