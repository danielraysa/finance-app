<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display the notifications page (Inertia view)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Get notifications as JSON (for API/dropdown)
     */
    public function list(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->user();

        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'notification' => $notification,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'unreadCount' => 0,
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, $notificationId)
    {
        $user = $request->user();

        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->delete();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'unreadCount' => $unreadCount,
        ]);
    }
}
