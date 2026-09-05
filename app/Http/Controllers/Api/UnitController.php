<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends Controller
{
   /**
     * Display a listing of units with search & status filter.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Unit::query();
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Search by unit name or abbreviation
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%");
            });
        }
        $units = $query->orderBy('name')->paginate($request->integer('per_page', 15));
        return UnitResource::collection($units);
    }
    /**
     * Store a newly created unit.
     */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        $unit = Unit::create($request->validated());
        return (new UnitResource($unit))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified unit.
     */
    public function show(Unit $unit): UnitResource
    {
        return new UnitResource($unit);
    }
    /**
     * Update the specified unit.
     */
    public function update(UpdateUnitRequest $request, Unit $unit): UnitResource
    {
        $unit->update($request->validated());
        return new UnitResource($unit);
    }
    /**
     * Remove the specified unit.
     */
    public function destroy(Unit $unit): JsonResponse
    {
        $unit->delete();
        return response()->json([
            'message' => 'Unit deleted successfully.',
        ], 200);
    }
}
