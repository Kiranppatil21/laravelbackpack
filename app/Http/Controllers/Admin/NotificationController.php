<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(AdminNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display all notifications for the current user
     */
    public function index()
    {
        $user = backpack_user();
        $notifications = $this->notificationService->getAllNotifications($user->id);
        
        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for the current user
     */
    public function markAllAsRead()
    {
        $user = backpack_user();
        $this->notificationService->markAllAsRead($user->id);
        
        return response()->json(['success' => true]);
    }

    /**
     * Get unread notification count for the current user
     */
    public function getCount()
    {
        $user = backpack_user();
        $count = $this->notificationService->getUnreadCount($user->id);
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for the current user
     */
    public function getRecent(Request $request)
    {
        $user = backpack_user();
        $limit = $request->get('limit', 10);
        $notifications = $this->notificationService->getUnreadNotifications($user->id, $limit);
        
        return response()->json(['notifications' => $notifications]);
    }
}