<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuditLogRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
     /**
     * List audit logs with search, filtering, and pagination
     * GET /api/audit-logs
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $logs = AuditLog::query()
            ->with('user:id,name,email')
            ->when($request->filled('user_id'), fn($q) => $q->forUser($request->user_id))
            ->when($request->filled('action'), fn($q) => $q->ofAction($request->action))
            ->when($request->filled('module'), fn($q) => $q->ofModule($request->module))
            ->when($request->filled('record'), fn($q) => $q->where('record', 'like', "%{$request->record}%"))
            ->dateBetween($request->query('from'), $request->query('to'))
            ->latest('created_at')
            ->paginate($request->integer('per_page', 20));
        return AuditLogResource::collection($logs);
    }
    /**
     * View audit log detail
     * GET /api/audit-logs/{audit_log}
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load('user:id,name,email');
        return response()->json([
            'success' => true,
            'data'    => new AuditLogResource($auditLog),
        ]);
    }
    /**
     * Create an audit log record
     * POST /api/audit-logs
     */
    public function store(StoreAuditLogRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $data['user_id'] ?? $request->user()?->id;
        $log = AuditLog::create($data);
        $log->load('user:id,name,email');
        return response()->json([
            'success' => true,
            'message' => 'Audit log recorded successfully.',
            'data'    => new AuditLogResource($log),
        ], 201);
    }
}
