<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with search & status filter.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Category::query();
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Search by category name or code
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        $categories = $query->latest()->paginate($request->integer('per_page', 15));
        return CategoryResource::collection($categories);
    }
    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified category.
     */
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }
    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());
        return new CategoryResource($category);
    }
    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json([
            'message' => 'Category deleted successfully.',
        ], 200);
    }
}
