<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\PurchaseResource;
use App\Http\Resources\SaleResource;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * GET /api/reports/sales?from_date=&to_date=&payment_method=
     */
    public function sales(Request $request): JsonResponse
    {
        $query = Sale::with(['customer', 'cashier', 'branch', 'items'])
            ->where('status', 'completed');

        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->query('to_date'));
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->query('payment_method'));
        }

        $filtered = $query->orderByDesc('sale_date')->get();

        $revenue = round($filtered->sum('total'), 2);
        $cogs = $filtered->reduce(function ($sum, $sale) {
            return $sum + $sale->items->reduce(function ($s, $item) {
                return $s + $item->cost * $item->quantity;
            }, 0);
        }, 0);
        $discount = round($filtered->sum('discount'), 2);
        $tax = round($filtered->sum('tax'), 2);

        // Daily chart
        $labels = [];
        $values = [];
        $byDay = [];
        foreach ($filtered as $sale) {
            $date = $sale->sale_date;
            if ($date === null) {
                continue;
            }
            $key = $date->format('n/j');
            $byDay[$key] = ($byDay[$key] ?? 0) + $sale->total;
        }
        foreach ($byDay as $label => $value) {
            $labels[] = $label;
            $values[] = round($value, 2);
        }

        // Payment breakdown
        $byMethod = [];
        foreach ($filtered as $sale) {
            $method = $sale->payment_method ?: 'cash';
            $byMethod[$method] = ($byMethod[$method] ?? 0) + $sale->total;
        }
        $paymentBreakdown = collect($byMethod)
            ->map(fn ($value, $name) => ['name' => $name, 'value' => round($value, 2)])
            ->values()
            ->all();

        $rows = SaleResource::collection($filtered);

        return response()->json([
            'success' => true,
            'data'    => [
                'summary'          => [
                    'revenue'      => $revenue,
                    'orders'       => $filtered->count(),
                    'average_order' => $filtered->isEmpty() ? 0 : round($revenue / $filtered->count(), 2),
                    'discount'     => $discount,
                    'tax'          => $tax,
                    'gross_profit' => round($revenue - $cogs, 2),
                ],
                'chart'            => ['labels' => $labels, 'values' => $values],
                'payment_breakdown' => $paymentBreakdown,
                'rows'             => $rows,
            ],
        ]);
    }

    /**
     * GET /api/reports/purchases?from_date=&to_date=
     */
    public function purchases(Request $request): JsonResponse
    {
        $query = Purchase::with(['supplier', 'items']);

        if ($request->filled('from_date')) {
            $query->whereDate('order_date', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('order_date', '<=', $request->query('to_date'));
        }

        $filtered = $query->orderByDesc('order_date')->get();

        $total = round($filtered->sum('total'), 2);
        $count = $filtered->count();
        $items = $filtered->reduce(function ($sum, $purchase) {
            return $sum + $purchase->items->count();
        }, 0);

        $rows = PurchaseResource::collection($filtered);

        return response()->json([
            'success' => true,
            'data'    => [
                'summary' => [
                    'total' => $total,
                    'count' => $count,
                    'items' => $items,
                ],
                'rows'    => $rows,
            ],
        ]);
    }

    /**
     * GET /api/reports/inventory
     */
    public function inventory(): JsonResponse
    {
        $products = Product::with(['category', 'brand', 'unit'])->get();

        $totalItems = $products->count();
        $totalUnits = round($products->sum('stock'), 2);
        $totalValue = round($products->sum(fn ($product) => $product->cost * $product->stock), 2);
        $retailValue = round($products->sum(fn ($product) => $product->price * $product->stock), 2);
        $lowStock = $products->filter(fn (Product $product) => $product->stock > 0 && $product->stock <= $product->min_stock)->count();
        $outOfStock = $products->filter(fn (Product $product) => $product->stock == 0)->count();

        $rows = ProductResource::collection($products);

        return response()->json([
            'success' => true,
            'data'    => [
                'summary' => [
                    'total_items' => $totalItems,
                    'total_units' => $totalUnits,
                    'total_value' => $totalValue,
                    'retail_value' => $retailValue,
                    'low_stock'   => $lowStock,
                    'out_of_stock' => $outOfStock,
                ],
                'rows'    => $rows,
            ],
        ]);
    }

    /**
     * GET /api/reports/profit?from_date=&to_date=
     */
    public function profit(Request $request): JsonResponse
    {
        $query = Sale::with(['items', 'customer'])->where('status', 'completed');

        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->query('to_date'));
        }

        $sales = $query->get();

        $revenue = round($sales->sum('total'), 2);
        $cogs = $sales->reduce(function ($sum, $sale) {
            return $sum + $sale->items->reduce(function ($s, $item) {
                return $s + $item->cost * $item->quantity;
            }, 0);
        }, 0);

        $expenseQuery = Expense::query();
        if ($request->filled('from_date')) {
            $expenseQuery->whereDate('expense_date', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $expenseQuery->whereDate('expense_date', '<=', $request->query('to_date'));
        }
        $expenses = $expenseQuery->orderByDesc('expense_date')->get();
        $expenseTotal = round($expenses->sum('amount'), 2);

        $salesRows = $sales->map(function (Sale $sale): array {
            $costOfGoods = round($sale->items->sum(fn ($item) => $item->cost * $item->quantity), 2);

            return [
                'type' => 'sale',
                'date' => $sale->sale_date?->toIso8601String(),
                'reference' => $sale->invoice_number,
                'description' => $sale->customer?->name,
                'payment_method' => $sale->payment_method,
                'revenue' => (float) $sale->total,
                'cost_of_goods' => $costOfGoods,
                'expense' => 0.0,
                'profit' => round($sale->total - $costOfGoods, 2),
            ];
        });

        $expenseRows = $expenses->map(fn (Expense $expense): array => [
            'type' => 'expense',
            'date' => $expense->expense_date?->toIso8601String(),
            'reference' => 'EXP-' . $expense->id,
            'description' => trim(implode(' - ', array_filter([$expense->category, $expense->description]))),
            'payment_method' => $expense->payment_method,
            'revenue' => 0.0,
            'cost_of_goods' => 0.0,
            'expense' => (float) $expense->amount,
            'profit' => -((float) $expense->amount),
        ]);

        $transactions = $salesRows
            ->concat($expenseRows)
            ->sortByDesc('date')
            ->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'summary' => [
                    'revenue'      => $revenue,
                    'cogs'         => round($cogs, 2),
                    'gross_profit' => round($revenue - $cogs, 2),
                    'expenses'     => $expenseTotal,
                    'net_profit'   => round($revenue - $cogs - $expenseTotal, 2),
                ],
                'transactions' => $transactions,
            ],
        ]);
    }
}