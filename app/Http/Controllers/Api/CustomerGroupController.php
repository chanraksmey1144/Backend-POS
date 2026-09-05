<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerGroupRequest;
use App\Http\Requests\UpdateCustomerGroupRequest;
use App\Http\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerGroupController extends Controller
{
  /**
     * Display a listing of customer groups with search & pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CustomerGroup::query();
        // Search by group name
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->query('search')}%");
        }
        $groups = $query->orderBy('name')->paginate($request->integer('per_page', 15));
        return CustomerGroupResource::collection($groups);
    }
    /**
     * Store a newly created customer group.
     */
    public function store(StoreCustomerGroupRequest $request): JsonResponse
    {
        $group = CustomerGroup::create($request->validated());
        return (new CustomerGroupResource($group))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified customer group.
     */
    public function show(CustomerGroup $customerGroup): CustomerGroupResource
    {
        return new CustomerGroupResource($customerGroup);
    }
    /**
     * Update the specified customer group.
     */
    public function update(UpdateCustomerGroupRequest $request, CustomerGroup $customerGroup): CustomerGroupResource
    {
        $customerGroup->update($request->validated());
        return new CustomerGroupResource($customerGroup);
    }
    /**
     * Remove the specified customer group.
     */
    public function destroy(CustomerGroup $customerGroup): JsonResponse
    {
        $customerGroup->delete();
        return response()->json([
            'message' => 'Customer group deleted successfully.',
        ], 200);
    }
}
