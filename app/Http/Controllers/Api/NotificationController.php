<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    // GET /api/notifications
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()?->id;
        $notifications = Notification::query()
            ->when($userId, fn($q) => $q->forUser($userId))
            ->when($request->filled('type'), fn($q) => $q->ofType($request->type))
            ->when($request->boolean('unread_only'), fn($q) => $q->unread())
            ->latest('created_at')
            ->paginate($request->integer('per_page', 15));
        return NotificationResource::collection($notifications);
    }
    // GET /api/notifications/{notification} (View detail & auto mark as read)
    public function show(Request $request, Notification $notification): JsonResponse
    {
        if (! $notification->is_read) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data'    => new NotificationResource($notification),
        ]);
    }
    // POST /api/notifications
    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $notification = Notification::create($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully.',
            'data'    => new NotificationResource($notification),
        ], 201);
    }
    // PATCH /api/notifications/{notification}/read
    public function markAsRead(Notification $notification): JsonResponse
    {
        $notification->markAsRead();
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }
    // POST /api/notifications/read-all
    public function markAllAsRead(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $query = Notification::query()->unread();
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $query->update(['is_read' => true]);
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }
    // DELETE /api/notifications/{notification}
    public function destroy(Notification $notification): JsonResponse
    {
        $notification->delete();
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }
}
