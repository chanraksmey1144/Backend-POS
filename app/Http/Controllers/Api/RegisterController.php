<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterRequest;
use App\Http\Requests\UpdateRegisterRequest;
use App\Http\Resources\RegisterResource;
use App\Models\Register;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RegisterController extends Controller
{
    /**
     * Display a listing of registers with optional branch filter, status filter & search.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Register::with('branch');
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
        $registers = $query->latest()->paginate($request->integer('per_page', 15));
        return RegisterResource::collection($registers);
    }
    /**
     * Store a newly created register.
     */
    public function store(StoreRegisterRequest $request): JsonResponse
    {
        $register = Register::create($request->validated());
        $register->load('branch');
        return (new RegisterResource($register))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified register.
     */
    public function show(Register $register): RegisterResource
    {
        $register->load('branch');
        return new RegisterResource($register);
    }
    /**
     * Update the specified register.
     */
    public function update(UpdateRegisterRequest $request, Register $register): RegisterResource
    {
        $register->update($request->validated());
        $register->load('branch');
        return new RegisterResource($register);
    }
    /**
     * Remove the specified register.
     */
    public function destroy(Register $register): JsonResponse
    {
        $register->delete();
        return response()->json([
            'message' => 'Register deleted successfully.',
        ], 200);
    }

}
