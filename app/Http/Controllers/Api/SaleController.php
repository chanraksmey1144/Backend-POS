<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaleController extends Controller
{
    /**
     * Display a listing of sales with date filters, status, customer & branch filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Sale::with(['customer', 'cashier', 'branch', 'register', 'items']);
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }
        // Filter by cashier
        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->query('cashier_id'));
        }
        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }
        // Filter by status (completed, pending, hold, etc.)
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Filter by payment status (paid, partial, unpaid)
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }
        // Filter by payment method (cash, card, qr, etc.)
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->query('payment_method'));
        }
        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->query('to_date'));
        }
        // Search by invoice number
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->query('search')}%");
        }
        $sales = $query->latest('sale_date')->paginate($request->integer('per_page', 15));
        return SaleResource::collection($sales);
    }
    /**
     * Store a newly created sale transaction.
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if (!isset($data['change']) && isset($data['paid'], $data['total'])) {
                $data['change'] = max(0, $data['paid'] - $data['total']);
            }

            $items = $data['items'] ?? [];
            unset($data['items']);

            $sale = DB::transaction(function () use ($data, $items) {
                $sale = Sale::create($data);

                foreach ($items as $item) {
                    $item['sale_id']   = $sale->id;
                    $item['created_at'] = now();
                    SaleItem::create($item);

                    if (!empty($item['variant_id'])) {
                        ProductVariant::whereKey($item['variant_id'])->decrement('stock', $item['quantity']);
                    }
                    if (!empty($item['product_id'])) {
                        Product::whereKey($item['product_id'])->decrement('stock', $item['quantity']);
                    }
                }

                return $sale;
            });

            $sale->load(['customer', 'cashier', 'branch', 'register', 'items']);

            return response()->json([
                'message' => 'Sale transaction created successfully.',
                'data'    => new SaleResource($sale),
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create sale transaction.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Display the specified sale transaction.
     */
    public function show(Sale $sale): SaleResource
    {
        $sale->load(['customer', 'cashier', 'branch', 'register', 'creator', 'items']);
        return new SaleResource($sale);
    }
    /**
     * Update the specified sale.
     */
    public function update(UpdateSaleRequest $request, Sale $sale): SaleResource
    {
        $sale->update($request->validated());
        $sale->load(['customer', 'cashier', 'branch', 'register', 'items']);
        return new SaleResource($sale);
    }
    /**
     * Remove or cancel the sale transaction.
     */
    public function destroy(Sale $sale): JsonResponse
    {
        $sale->delete();
        return response()->json([
            'message' => 'Sale transaction deleted successfully.',
        ], 200);
    }
}
