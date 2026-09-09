<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCashTransactionRequest;
use App\Http\Requests\UpdateCashTransactionRequest;
use App\Http\Resources\CashTransactionResource;
use App\Models\CashRegisterSession;
use App\Models\CashTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CashTransactionController extends Controller
{
    /**
     * Display a listing of cash in/out transactions for a session.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CashTransaction::with('user');
        // Filter by session
        if ($request->filled('session_id')) {
            $query->where('session_id', $request->query('session_id'));
        }
        // Filter by type (cash_in or cash_out)
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->query('transaction_type'));
        }
        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->query('to_date'));
        }
        $transactions = $query->latest('created_at')->paginate($request->integer('per_page', 20));
        return CashTransactionResource::collection($transactions);
    }
    /**
     * Record cash in or cash out and update the session's expected cash.
     */
    public function store(StoreCashTransactionRequest $request): JsonResponse
    {
        $transaction = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['created_at'] = $data['created_at'] ?? now();
            $session = CashRegisterSession::lockForUpdate()->findOrFail($data['session_id']);
            // Update expected_cash on the session
            if ($data['transaction_type'] === 'cash_in') {
                $session->increment('expected_cash', $data['amount']);
            } elseif ($data['transaction_type'] === 'cash_out') {
                $session->decrement('expected_cash', $data['amount']);
            }
            return CashTransaction::create($data);
        });
        $transaction->load('user');
        return (new CashTransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified cash transaction.
     */
    public function show(CashTransaction $cashTransaction): CashTransactionResource
    {
        $cashTransaction->load('user');
        return new CashTransactionResource($cashTransaction);
    }
    /**
     * Update description of the cash transaction.
     */
    public function update(UpdateCashTransactionRequest $request, CashTransaction $cashTransaction): CashTransactionResource
    {
        $cashTransaction->update($request->validated());
        $cashTransaction->load('user');
        return new CashTransactionResource($cashTransaction);
    }
    /**
     * Delete cash transaction.
     */
    public function destroy(CashTransaction $cashTransaction): JsonResponse
    {
        DB::transaction(function () use ($cashTransaction) {
            $session = CashRegisterSession::lockForUpdate()->find($cashTransaction->session_id);
            if ($session) {
                // Reverse the expected_cash modification
                if ($cashTransaction->transaction_type === 'cash_in') {
                    $session->decrement('expected_cash', $cashTransaction->amount);
                } elseif ($cashTransaction->transaction_type === 'cash_out') {
                    $session->increment('expected_cash', $cashTransaction->amount);
                }
            }
            $cashTransaction->delete();
        });
        return response()->json([
            'message' => 'Cash transaction deleted successfully.',
        ], 200);
    }
}
