<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\CloudinaryImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

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
    public function store(StoreProductRequest $request, CloudinaryImageService $cloudinary): JsonResponse
    {
        $data = $request->validated();
        $image = $data['image'] ?? null;
        unset($data['image']);

        if ($image) {
            $uploadedImage = $cloudinary->upload($image);
            $data['image_url'] = $uploadedImage['url'];
            $data['image_public_id'] = $uploadedImage['public_id'];
        }

        $product = Product::create($data);
        $product->load(['category', 'brand', 'unit']);
        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function generateMissingBarcodes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1', 'max:500'],
            'product_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
        ]);

        $products = DB::transaction(function () use ($validated) {
            $products = Product::query()
                ->whereIn('id', $validated['product_ids'])
                ->where(function ($query) {
                    $query->whereNull('barcode')->orWhere('barcode', '');
                })
                ->lockForUpdate()
                ->get();

            foreach ($products as $product) {
                $product->barcode = $this->generateUniqueEan13();
                $product->save();
            }

            return $products;
        });

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'generated' => $products->count(),
        ]);
    }

    private function generateUniqueEan13(): string
    {
        do {
            $digits = '899';
            for ($index = 0; $index < 9; $index++) {
                $digits .= (string) random_int(0, 9);
            }

            $sum = 0;
            foreach (str_split($digits) as $index => $digit) {
                $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
            }

            $barcode = $digits . ((10 - ($sum % 10)) % 10);
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
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
    public function update(UpdateProductRequest $request, Product $product, CloudinaryImageService $cloudinary): ProductResource
    {
        $data = $request->validated();
        $image = $data['image'] ?? null;
        unset($data['image']);

        if ($image) {
            $uploadedImage = $cloudinary->upload($image);
            $data['image_url'] = $uploadedImage['url'];
            $data['image_public_id'] = $uploadedImage['public_id'];
        }

        $previousImagePublicId = $product->image_public_id;
        $product->update($data);

        if ($image && $previousImagePublicId) {
            $cloudinary->delete($previousImagePublicId);
        }

        $product->load(['category', 'brand', 'unit']);
        return new ProductResource($product);
    }
    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): JsonResponse
    {
        app(CloudinaryImageService::class)->delete($product->image_public_id);
        $product->delete();
        return response()->json([
            'message' => 'Product deleted successfully.',
        ], 200);
    }
}

