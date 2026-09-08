<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCashRegisterSessionRequest;
use App\Http\Requests\UpdateCashRegisterSessionRequest;
use App\Http\Resources\CashRegisterSessionResource;
use App\Models\CashRegisterSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CashRegisterSessionController extends Controller
{
    /**
     * Display a listing of register sessions with status & register filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CashRegisterSession::with(['register', 'branch', 'user']);
        // Filter by register
        if ($request->filled('register_id')) {
            $query->where('register_id', $request->query('register_id'));
        }
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }
        // Filter by cashier user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }
        // Filter by session status ('open' or 'closed')
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        $sessions = $query->latest('opened_at')->paginate($request->integer('per_page', 15));
        return CashRegisterSessionResource::collection($sessions);
    }
    /**
     * Open a new cash drawer shift.
     */
    public function store(StoreCashRegisterSessionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = 'open';
        $session = CashRegisterSession::create($data);
        $session->load(['register', 'branch', 'user']);
        return (new CashRegisterSessionResource($session))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified session details.
     */
    public function show(CashRegisterSession $cashRegisterSession): CashRegisterSessionResource
    {
        $cashRegisterSession->load(['register', 'branch', 'user']);
        return new CashRegisterSessionResource($cashRegisterSession);
    }
    /**
     * Close cash drawer shift / record actual counted cash.
     */
    public function update(UpdateCashRegisterSessionRequest $request, CashRegisterSession $cashRegisterSession): CashRegisterSessionResource
    {
        $data = $request->validated();
        // If closing the session or providing counted cash:
        if (isset($data['actual_cash'])) {
            $expected = $data['expected_cash'] ?? $cashRegisterSession->expected_cash;
            $data['difference'] = (float) ($data['actual_cash'] - $expected);
            $data['status'] = 'closed';
            $data['closed_at'] = $data['closed_at'] ?? now();
        }
        $cashRegisterSession->update($data);
        $cashRegisterSession->load(['register', 'branch', 'user']);
        return new CashRegisterSessionResource($cashRegisterSession);
    }
    /**
     * Delete a session.
     */
    public function destroy(CashRegisterSession $cashRegisterSession): JsonResponse
    {
        $cashRegisterSession->delete();
        return response()->json([
            'message' => 'Cash register session deleted successfully.',
        ], 200);
    }
}
