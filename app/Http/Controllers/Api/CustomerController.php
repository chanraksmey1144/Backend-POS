<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
   /**
     * Display a listing of customers with group filter, status filter, and search.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Customer::with('group');
        // Filter by customer group tier
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->query('group_id'));
        }
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Filter customers who have outstanding balance
        if ($request->boolean('has_outstanding')) {
            $query->where('outstanding', '>', 0);
        }
        // Search by name, email, or phone
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $customers = $query->latest()->paginate($request->integer('per_page', 15));
        return CustomerResource::collection($customers);
    }
    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());
        $customer->load('group');
        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): CustomerResource
    {
        $customer->load('group');
        return new CustomerResource($customer);
    }
    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $customer->update($request->validated());
        $customer->load('group');
        return new CustomerResource($customer);
    }
    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json([
            'message' => 'Customer deleted successfully.',
        ], 200);
    }  
}
