<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * Display a listing of products with eager relations & filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::with(['category', 'brand', 'unit', 'variants']);
        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }
        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->query('brand_id'));
        }
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Filter low stock products alert
        if ($request->boolean('low_stock')) {
            $query->where('track_inventory', true)
                  ->whereColumn('stock', '<=', 'min_stock');
        }
        // Search by name, SKU, or barcode
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        $products = $query->latest()->paginate($request->integer('per_page', 15));
        return ProductResource::collection($products);
    }
    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        $product->load(['category', 'brand', 'unit']);
        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified product.
     */
    public function show(Product $product): ProductResource
    {
        $product->load(['category', 'brand', 'unit']);
        return new ProductResource($product);
    }
    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());
        $product->load(['category', 'brand', 'unit']);
        return new ProductResource($product);
    }
    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json([
            'message' => 'Product deleted successfully.',
        ], 200);
    }
}

