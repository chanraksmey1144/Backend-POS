<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers with search & filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Supplier::query();
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Filter suppliers with outstanding payable balance
        if ($request->boolean('has_outstanding')) {
            $query->where('outstanding', '>', 0);
        }
        // Search by name, contact person, email, or phone
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $suppliers = $query->latest()->paginate($request->integer('per_page', 15));
        return SupplierResource::collection($suppliers);
    }
    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());
        return (new SupplierResource($supplier))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }
    /**
     * Update the specified supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): SupplierResource
    {
        $supplier->update($request->validated());
        return new SupplierResource($supplier);
    }
    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();
        return response()->json([
            'message' => 'Supplier deleted successfully.',
        ], 200);
    }
}
