<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     * Aggregated metrics, charts and recent activity for the dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        $today = now()->today();

        $todaySales = Sale::query()
            ->whereDate('sale_date', $today->toDateString())
            ->where('status', 'completed')
            ->with('items')
            ->get();

        $todaysRevenue = round($todaySales->sum('total'), 2);
        $todaysOrders = $todaySales->count();
        $cogs = $todaySales->reduce(function ($sum, $sale) {
            return $sum + $sale->items->reduce(function ($s, $item) {
                return $s + $item->cost * $item->quantity;
            }, 0);
        }, 0);
        $grossProfit = round($todaysRevenue - $cogs, 2);
        $avgOrderValue = $todaysOrders > 0 ? round($todaysRevenue / $todaysOrders, 2) : 0;

        $totalProducts = Product::where('status', 'active')->count();
        $lowStockCount = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();
        $outOfStockCount = Product::where('status', 'active')->where('stock', 0)->count();
        $outstandingPayments = round(Customer::sum('outstanding'), 2);

        // Sales trend for the last 30 days
        $start30 = now()->subDays(29)->startOfDay();
        $last30Sales = Sale::query()
            ->where('status', 'completed')
            ->where('sale_date', '>=', $start30)
            ->with('items')
            ->get();

        $chartLabels = [];
        $dailyRevenue = [];
        $dailyOrders = [];
        $dailyProfit = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $daySales = $last30Sales->filter(fn (Sale $sale) => $sale->sale_date?->isSameDay($date));
            $revenue = round($daySales->sum('total'), 2);
            $profit = round($daySales->reduce(function ($sum, $sale) {
                return $sum + $sale->total - $sale->items->reduce(function ($s, $item) {
                    return $s + $item->cost * $item->quantity;
                }, 0);
            }, 0), 2);
            $chartLabels[] = $date->format('n/j');
            $dailyRevenue[] = $revenue;
            $dailyOrders[] = $daySales->count();
            $dailyProfit[] = $profit;
        }

        // Sales by category (last 90 days)
        $start90 = now()->subDays(90)->startOfDay();
        $recentSales = Sale::query()
            ->where('status', 'completed')
            ->where('sale_date', '>=', $start90)
            ->with('items')
            ->get();

        $productCategoryMap = Product::query()->pluck('category_id', 'id')->toArray();
        $categories = \App\Models\Category::query()->pluck('name', 'id')->toArray();

        $categoryTotals = [];
        foreach ($recentSales as $sale) {
            foreach ($sale->items as $item) {
                $categoryId = $productCategoryMap[$item->product_id] ?? null;
                if ($categoryId === null) {
                    continue;
                }
                $categoryName = $categories[$categoryId] ?? 'Other';
                $categoryTotals[$categoryName] = ($categoryTotals[$categoryName] ?? 0) + ($item->price * $item->quantity);
            }
        }
        $salesByCategory = collect($categoryTotals)
            ->map(fn ($value, $name) => ['name' => (string) $name, 'value' => round($value, 2)])
            ->filter(fn (array $item) => $item['value'] > 0)
            ->values()
            ->all();

        // Top products by revenue (last 90 days)
        $productStats = [];
        foreach ($recentSales as $sale) {
            foreach ($sale->items as $item) {
                $key = $item->product_id;
                if (!isset($productStats[$key])) {
                    $productStats[$key] = ['product_id' => $key, 'name' => $item->name, 'quantity' => 0, 'revenue' => 0];
                }
                $productStats[$key]['quantity'] += $item->quantity;
                $productStats[$key]['revenue'] += $item->price * $item->quantity;
            }
        }
        $topProducts = collect($productStats)
            ->map(function ($entry) {
                $entry['revenue'] = round($entry['revenue'], 2);
                return $entry;
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values()
            ->all();

        // Payment method breakdown (last 90 days)
        $byMethod = [];
        foreach ($recentSales as $sale) {
            $method = $sale->payment_method ?: 'cash';
            $byMethod[$method] = ($byMethod[$method] ?? 0) + $sale->total;
        }
        $paymentBreakdown = collect($byMethod)
            ->map(fn ($value, $name) => ['name' => $name, 'value' => round($value, 2)])
            ->filter(fn (array $item) => $item['value'] > 0)
            ->values()
            ->all();

        // Purchase trend (last 30 days)
        $last30Purchases = Purchase::query()
            ->where('created_at', '>=', $start30)
            ->get();
        $purchaseTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayPurchases = $last30Purchases->filter(fn (Purchase $purchase) => $purchase->order_date?->isSameDay($date));
            $purchaseTrend[] = [
                'label' => $date->format('n/j'),
                'value' => round($dayPurchases->sum('total'), 2),
            ];
        }

        $recentSales = $recentSales
            ->sortByDesc('sale_date')
            ->take(6)
            ->map(fn (Sale $sale) => [
                'id'             => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'sale_date'      => $sale->sale_date?->toIso8601String(),
                'total'          => (float) $sale->total,
                'status'         => $sale->status,
            ])
            ->values()
            ->all();

        $recentPurchases = Purchase::query()
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id'              => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'order_date'      => $purchase->order_date?->toIso8601String(),
                'total'           => (float) $purchase->total,
                'status'          => $purchase->status,
            ])
            ->all();

        $recentMovements = StockMovement::query()
            ->latest('movement_date')
            ->take(6)
            ->get()
            ->map(fn (StockMovement $movement) => [
                'id'            => $movement->id,
                'type'          => $movement->type,
                'quantity'      => (float) $movement->quantity,
                'movement_date' => $movement->movement_date?->toIso8601String(),
                'reference'     => $movement->reference,
            ])
            ->all();

        return response()->json([
            'success' => true,
            'data'    => [
                'metrics' => [
                    'todays_revenue'      => $todaysRevenue,
                    'todays_orders'       => $todaysOrders,
                    'gross_profit'        => $grossProfit,
                    'avg_order_value'     => $avgOrderValue,
                    'total_products'      => $totalProducts,
                    'low_stock_count'     => $lowStockCount,
                    'out_of_stock_count'  => $outOfStockCount,
                    'outstanding_payments' => $outstandingPayments,
                ],
                'charts' => [
                    'sales_trend'      => ['labels' => $chartLabels, 'revenue' => $dailyRevenue, 'orders' => $dailyOrders, 'profit' => $dailyProfit],
                    'sales_by_category' => $salesByCategory,
                    'top_products'     => $topProducts,
                    'payment_breakdown' => $paymentBreakdown,
                    'purchase_trend'   => $purchaseTrend,
                ],
                'recent_sales'     => $recentSales,
                'recent_purchases' => $recentPurchases,
                'recent_movements' => $recentMovements,
            ],
        ]);
    }
}