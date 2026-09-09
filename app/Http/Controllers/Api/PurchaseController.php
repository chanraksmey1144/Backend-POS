<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchase orders with filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Purchase::with(['supplier', 'branch', 'warehouse', 'items']);
        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->query('supplier_id'));
        }
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }
        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->query('warehouse_id'));
        }
        // Filter by status (draft, ordered, received, etc.)
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Filter by payment status (paid, partial, unpaid)
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }
        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('order_date', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('order_date', '<=', $request->query('to_date'));
        }
        // Search by purchase order number
        if ($request->filled('search')) {
            $query->where('purchase_number', 'like', "%{$request->query('search')}%");
        }
        $purchases = $query->latest('order_date')->paginate($request->integer('per_page', 15));
        return PurchaseResource::collection($purchases);
    }
    /**
     * Create a new purchase order (with optional line items; received orders
     * restock the warehouse and update the supplier ledger).
     */
    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);
        $data['created_by'] = $data['created_by'] ?? $request->user()?->id;

        $purchase = DB::transaction(function () use ($data, $items): Purchase {
            $purchase = Purchase::create($data);
            $this->insertItems($purchase, $items);

            if ($data['status'] ?? null === 'received') {
                $this->receive($purchase, $items);
            }

            return $purchase;
        });

        $purchase->load(['supplier', 'branch', 'warehouse', 'items', 'creator']);

        return (new PurchaseResource($purchase))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified purchase order.
     */
    public function show(Purchase $purchase): PurchaseResource
    {
        $purchase->load(['supplier', 'branch', 'warehouse', 'creator', 'items']);
        return new PurchaseResource($purchase);
    }
    /**
     * Update the specified purchase order. Receiving it (status -> received /
     * partially_received) restocks the warehouse and logs stock movements.
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase): PurchaseResource
    {
        DB::transaction(function () use ($request, $purchase) {
            $data = $request->validated();
            $items = $data['items'] ?? [];
            unset($data['items']);

            $previousStatus = $purchase->status;
            $purchase->update($data);

            if (!empty($items)) {
                $this->insertItems($purchase, $items);
            }
            $purchase->load('items');

            $status = $purchase->status;
            if (in_array($status, ['received', 'partially_received'], true) && $status !== $previousStatus) {
                $this->receive($purchase);
            } elseif ($status === 'cancelled' && in_array($previousStatus, ['received', 'partially_received'], true)) {
                $this->reverseReceipt($purchase);
            }
        });

        $purchase->load(['supplier', 'branch', 'warehouse', 'items', 'creator']);

        return new PurchaseResource($purchase);
    }
    /**
     * Cancel or delete a purchase order (received orders restore stock).
     */
    public function destroy(Purchase $purchase): JsonResponse
    {
        DB::transaction(function () use ($purchase) {
            if (in_array($purchase->status, ['received', 'partially_received'], true)) {
                $this->reverseReceipt($purchase);
            }
            $purchase->delete();
        });

        return response()->json([
            'message' => 'Purchase order deleted successfully.',
        ], 200);
    }

    /**
     * Persist the purchase line items.
     */
    private function insertItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $item['purchase_id'] = $purchase->id;
            $item['created_at']  = now();
            PurchaseItem::create($item);
        }
    }

    /**
     * Apply the receipt of goods: restock, log movements, set received_at
     * and update the supplier ledger.
     */
    private function receive(Purchase $purchase, ?array $items = null): void
    {
        $receivedAt = now();
        $purchase->timestamps = false;
        $purchase->update(['status' => 'received', 'received_at' => $receivedAt]);

        $totalQty = 0;
        foreach ($purchase->items()->get() as $item) {
            if (!empty($item->product_id)) {
                Product::whereKey($item->product_id)
                    ->increment('stock', $item->quantity);
            } elseif (!empty($item->variant_id)) {
                ProductVariant::whereKey($item->variant_id)
                    ->increment('stock', $item->quantity);
            }

            $item->update(['received_quantity' => $item->quantity]);
            $totalQty += (float) $item->quantity;

            StockMovement::create([
                'product_id'    => $item->product_id,
                'variant_id'    => $item->variant_id,
                'warehouse_id'  => $purchase->warehouse_id,
                'user_id'       => $purchase->created_by,
                'movement_date' => $receivedAt,
                'type'          => 'purchase',
                'quantity'      => (float) $item->quantity,
                'reference'     => $purchase->purchase_number,
                'note'          => "Purchase received into warehouse #{$purchase->warehouse_id}.",
            ]);
        }

        if ($purchase->supplier_id) {
            Supplier::whereKey($purchase->supplier_id)
                ->increment('total_purchases', (float) $purchase->total);
            if (in_array($purchase->payment_status, ['unpaid', 'partial'], true)) {
                Supplier::whereKey($purchase->supplier_id)
                    ->increment('outstanding', (float) $purchase->total);
            }
        }
    }

    /**
     * Reverse a previously received purchase (delete / cancellation).
     */
    private function reverseReceipt(Purchase $purchase): void
    {
        foreach ($purchase->items()->get() as $item) {
            if (!empty($item->product_id)) {
                Product::whereKey($item->product_id)
                    ->decrement('stock', $item->received_quantity ?: $item->quantity);
            } elseif (!empty($item->variant_id)) {
                ProductVariant::whereKey($item->variant_id)
                    ->decrement('stock', $item->received_quantity ?: $item->quantity);
            }

            StockMovement::create([
                'product_id'    => $item->product_id,
                'variant_id'    => $item->variant_id,
                'warehouse_id'  => $purchase->warehouse_id,
                'user_id'       => $purchase->created_by,
                'movement_date' => now(),
                'type'          => 'adjustment',
                'quantity'      => -((float) ($item->received_quantity ?: $item->quantity)),
                'reference'     => $purchase->purchase_number,
                'note'          => 'Purchase receipt reversed.',
            ]);
        }

        if ($purchase->supplier_id) {
            Supplier::whereKey($purchase->supplier_id)
                ->decrement('total_purchases', (float) $purchase->total);
            if (in_array($purchase->payment_status, ['unpaid', 'partial'], true)) {
                Supplier::whereKey($purchase->supplier_id)
                    ->decrement('outstanding', (float) $purchase->total);
            }
        }
    }
}