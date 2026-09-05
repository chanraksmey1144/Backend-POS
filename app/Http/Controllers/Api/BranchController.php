<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchController extends Controller
{
    /**
     * Display a listing of branches with optional filtering & pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Branch::query();
        // Optional filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Optional search by name or code
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        $branches = $query->latest()->paginate($request->integer('per_page', 15));
        return BranchResource::collection($branches);
    }
    /**
     * Store a newly created branch.
     */
    public function store(StoreBranchRequest $request): JsonResponse
    {
        $branch = Branch::create($request->validated());
        return (new BranchResource($branch))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified branch.
     */
    public function show(Branch $branch): BranchResource
    {
        return new BranchResource($branch);
    }
    /**
     * Update the specified branch.
     */
    public function update(UpdateBranchRequest $request, Branch $branch): BranchResource
    {
        $branch->update($request->validated());
        return new BranchResource($branch);
    }
    /**
     * Remove the specified branch.
     */
    public function destroy(Branch $branch): JsonResponse
    {
        $branch->delete();
        return response()->json([
            'message' => 'Branch deleted successfully.',
        ], 200);
    }
}
