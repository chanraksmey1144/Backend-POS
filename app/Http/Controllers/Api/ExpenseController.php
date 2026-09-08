<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses with category, branch, date filters, and search.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Expense::with(['branch', 'creator']);
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }
        // Filter by category (Rent, Utilities, Salaries, etc.)
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }
        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->query('payment_method'));
        }
        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->query('to_date'));
        }
        // Search by description
        if ($request->filled('search')) {
            $query->where('description', 'like', "%{$request->query('search')}%");
        }
        $expenses = $query->latest('expense_date')->paginate($request->integer('per_page', 15));
        return ExpenseResource::collection($expenses);
    }
    /**
     * Record a new expense.
     */
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = Expense::create($request->validated());
        $expense->load(['branch', 'creator']);
        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified expense record.
     */
    public function show(Expense $expense): ExpenseResource
    {
        $expense->load(['branch', 'creator']);
        return new ExpenseResource($expense);
    }
    /**
     * Update the specified expense record.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): ExpenseResource
    {
        $expense->update($request->validated());
        $expense->load(['branch', 'creator']);
        return new ExpenseResource($expense);
    }
    /**
     * Delete an expense record.
     */
    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();
        return response()->json([
            'message' => 'Expense record deleted successfully.',
        ], 200);
    }
}
