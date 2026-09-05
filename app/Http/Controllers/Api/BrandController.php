<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends Controller
{
    /**
     * Display a listing of brands with search & status filter.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Brand::query();
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Search by brand name or code
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        $brands = $query->latest()->paginate($request->integer('per_page', 15));
        return BrandResource::collection($brands);
    }
    /**
     * Store a newly created brand.
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = Brand::create($request->validated());
        return (new BrandResource($brand))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified brand.
     */
    public function show(Brand $brand): BrandResource
    {
        return new BrandResource($brand);
    }
    /**
     * Update the specified brand.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): BrandResource
    {
        $brand->update($request->validated());
        return new BrandResource($brand);
    }
    /**
     * Remove the specified brand.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        $brand->delete();
        return response()->json([
            'message' => 'Brand deleted successfully.',
        ], 200);
    }
}
