<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WarehouseController extends Controller
{
     /**
     * Display a listing of warehouses with optional filtering & branch eager-loading.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Warehouse::with('branch');
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        $warehouses = $query->latest()->paginate($request->integer('per_page', 15));
        return WarehouseResource::collection($warehouses);
    }
    /**
     * Store a newly created warehouse.
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $warehouse = Warehouse::create($request->validated());
        $warehouse->load('branch');
        return (new WarehouseResource($warehouse))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse): WarehouseResource
    {
        $warehouse->load('branch');
        return new WarehouseResource($warehouse);
    }
    /**
     * Update the specified warehouse.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): WarehouseResource
    {
        $warehouse->update($request->validated());
        $warehouse->load('branch');
        return new WarehouseResource($warehouse);
    }
    /**
     * Remove the specified warehouse.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $warehouse->delete();
        return response()->json([
            'message' => 'Warehouse deleted successfully.',
        ], 200);
    }
}
