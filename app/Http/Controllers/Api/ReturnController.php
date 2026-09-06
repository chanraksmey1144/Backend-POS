<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReturnRequest;
use App\Http\Requests\UpdateReturnRequest;
use App\Http\Resources\SaleReturnResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnItem;
use App\Models\SaleReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
   /**
     * Display a listing of returns with filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SaleReturn::with(['sale', 'branch', 'cashier', 'items']);
        // Filter by sale
        if ($request->filled('sale_id')) {
            $query->where('sale_id', $request->query('sale_id'));
        }
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }
        // Filter by cashier
        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->query('cashier_id'));
        }
        // Search by reason
        if ($request->filled('search')) {
            $query->where('reason', 'like', "%{$request->query('search')}%");
        }
        $returns = $query->latest()->paginate($request->integer('per_page', 15));
        return SaleReturnResource::collection($returns);
    }
    /**
     * Process a new return/refund.
     */
    public function store(StoreReturnRequest $request): JsonResponse
    {
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);

        $saleReturn = DB::transaction(function () use ($data, $items): SaleReturn {
            if (!isset($data['refund_amount'])) {
                $data['refund_amount'] = array_sum(array_map(
                    fn (array $item): float => max(0, ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0)),
                    $items,
                ));
            }

            $saleReturn = SaleReturn::create($data);

            foreach ($items as $item) {
                $item['return_id']  = $saleReturn->id;
                $item['created_at'] = now();
                ReturnItem::create($item);

                if (!empty($item['variant_id'])) {
                    ProductVariant::whereKey($item['variant_id'])->increment('stock', $item['quantity']);
                }
                if (!empty($item['product_id'])) {
                    Product::whereKey($item['product_id'])->increment('stock', $item['quantity']);
                }
            }

            return $saleReturn;
        });

        $saleReturn->load(['sale', 'branch', 'cashier', 'items']);
        return (new SaleReturnResource($saleReturn))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified return record.
     */
    public function show(SaleReturn $return): SaleReturnResource
    {
        $return->load(['sale', 'branch', 'cashier', 'items']);
        return new SaleReturnResource($return);
    }
    /**
     * Update the specified return record.
     */
    public function update(UpdateReturnRequest $request, SaleReturn $return): SaleReturnResource
    {
        $return->update($request->validated());
        $return->load(['sale', 'branch', 'cashier', 'items']);
        return new SaleReturnResource($return);
    }
    /**
     * Remove the specified return record.
     */
    public function destroy(SaleReturn $return): JsonResponse
    {
        $return->delete();
        return response()->json([
            'message' => 'Return record deleted successfully.',
        ], 200);
    }
}
