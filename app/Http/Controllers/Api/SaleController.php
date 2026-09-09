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
use App\Models\StockMovement;
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
            $data['created_by'] = $data['created_by'] ?? $request->user()?->id;

            $items = $data['items'] ?? [];
            unset($data['items']);

            $sale = DB::transaction(function () use ($data, $items) {
                $sale = Sale::create($data);

                foreach ($items as $item) {
                    $item['sale_id']    = $sale->id;
                    $item['created_at'] = now();

                    $target = $this->resolveStockTarget($item);
                    $this->assertSufficientStock($target, $item['quantity']);

                    SaleItem::create($item);
                    $this->adjustStock($target, $item['quantity'], -1);

                    $this->recordMovement($sale, $item, $target);
                }

                return $sale;
            });

            $sale->load(['customer', 'cashier', 'branch', 'register', 'items']);

            return response()->json([
                'message' => 'Sale transaction created successfully.',
                'data'    => new SaleResource($sale),
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Failed to create sale transaction.',
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
     * Update the specified sale. Restores / re-applies stock when the
     * status moves between completed and cancelled/refunded.
     */
    public function update(UpdateSaleRequest $request, Sale $sale): SaleResource
    {
        DB::transaction(function () use ($request, $sale) {
            $data = $request->validated();
            $previousStatus = $sale->status;
            $sale->update($data);

            $nowStatus = $sale->status;
            if ($previousStatus === $nowStatus) {
                return;
            }

            $closed  = in_array($nowStatus, ['cancelled', 'refunded'], true);
            $reopened = in_array($previousStatus, ['cancelled', 'refunded'], true);

            if ($closed && in_array($previousStatus, ['completed', 'pending', 'hold'], true)) {
                $this->restoreStock($sale);
            } elseif ($reopened && in_array($nowStatus, ['completed', 'pending', 'hold'], true)) {
                foreach ($sale->items as $item) {
                    $target = $this->resolveStockTarget($item->toArray());
                    $this->adjustStock($target, $item->quantity, -1);
                }
            }
        });

        $sale->load(['customer', 'cashier', 'branch', 'register', 'items']);
        return new SaleResource($sale);
    }
    /**
     * Remove or cancel the sale transaction.
     */
    public function destroy(Sale $sale): JsonResponse
    {
        DB::transaction(function () use ($sale) {
            if (in_array($sale->status, ['completed', 'pending', 'hold'], true)) {
                $this->restoreStock($sale);
            }
            $sale->delete();
        });

        return response()->json([
            'message' => 'Sale transaction deleted successfully.',
        ], 200);
    }

    /**
     * Resolve the stock owner of a sale item (variant takes precedence).
     */
    private function resolveStockTarget(array $item): ?array
    {
        if (!empty($item['variant_id'])) {
            return ['variant', (int) $item['variant_id']];
        }
        if (!empty($item['product_id'])) {
            return ['product', (int) $item['product_id']];
        }

        return null;
    }

    /**
     * Lock the stock row and adjust it by a signed quantity.
     */
    private function adjustStock(?array $target, float $quantity, int $sign): void
    {
        if ($target === null) {
            return;
        }

        [$type, $id] = $target;
        $model = $type === 'variant'
            ? ProductVariant::whereKey($id)->lockForUpdate()->first()
            : Product::whereKey($id)->lockForUpdate()->first();

        if ($model) {
            $model->update(['stock' => max(0, (float) $model->stock + ($sign * $quantity))]);
        }
    }

    /**
     * Ensure enough stock exists before an outgoing movement.
     */
    private function assertSufficientStock(?array $target, float $quantity): void
    {
        if ($target === null) {
            return;
        }

        [$type, $id] = $target;
        $model = $type === 'variant'
            ? ProductVariant::whereKey($id)->lockForUpdate()->first()
            : Product::whereKey($id)->lockForUpdate()->first();

        if ($model && (float) $model->stock < $quantity) {
            abort(422, "Insufficient stock for {$model->name} (available: {$model->stock}).");
        }
    }

    /**
     * Increase stock back on the sale items (cancelled / refunded / deleted).
     */
    private function restoreStock(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            $target = $this->resolveStockTarget($item->toArray());
            $this->adjustStock($target, $item->quantity, 1);

            StockMovement::create([
                'product_id'    => $item->product_id,
                'variant_id'    => $item->variant_id,
                'user_id'       => $sale->cashier_id ?? $sale->created_by,
                'movement_date' => now(),
                'type'          => 'return',
                'quantity'      => $item->quantity,
                'reference'     => $sale->invoice_number,
                'note'          => 'Stock restored from cancelled / refunded / deleted sale.',
            ]);
        }
    }

    /**
     * Record a stock movement for an outgoing sale line.
     */
    private function recordMovement(Sale $sale, array $item, ?array $target): void
    {
        if ($target === null) {
            return;
        }

        StockMovement::create([
            'product_id'    => $item['product_id'] ?? null,
            'variant_id'    => $item['variant_id'] ?? null,
            'user_id'       => $sale->created_by ?? $sale->cashier_id,
            'movement_date' => $sale->sale_date ?? now(),
            'type'          => 'sale',
            'quantity'      => -(float) $item['quantity'],
            'reference'     => $sale->invoice_number,
            'note'          => 'Items sold via POS.',
        ]);
    }
}
