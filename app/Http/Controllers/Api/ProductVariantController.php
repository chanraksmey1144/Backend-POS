<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Http\Resources\ProductVariantResource;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductVariantController extends Controller
{
     /**
     * Display a listing of variants with product filter & search.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ProductVariant::with('product');
        // Filter by parent product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }
        // Search by variant name, SKU, or barcode
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        $variants = $query->latest()->paginate($request->integer('per_page', 15));
        return ProductVariantResource::collection($variants);
    }
    /**
     * Store a newly created variant.
     */
    public function store(StoreProductVariantRequest $request): JsonResponse

    {
        $variant = ProductVariant::create($request->validated());
        $variant->load('product');
        return (new ProductVariantResource($variant))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified variant.
     */
    public function show(ProductVariant $productVariant): ProductVariantResource
    {
        $productVariant->load('product');
        return new ProductVariantResource($productVariant);
    }
    /**
     * Update the specified variant.
     */
    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant): ProductVariantResource
    {
        $productVariant->update($request->validated());
        $productVariant->load('product');
        return new ProductVariantResource($productVariant);
    }
    /**
     * Remove the specified variant.
     */
    public function destroy(ProductVariant $productVariant): JsonResponse
    {
        $productVariant->delete();
        return response()->json([
            'message' => 'Product variant deleted successfully.',
        ], 200);
    }
}
