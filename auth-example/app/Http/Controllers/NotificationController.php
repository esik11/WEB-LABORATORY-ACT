<?php

namespace App\Http\Controllers;

use App\Models\Notification; 
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Fetch unread notifications for the logged-in user
    public function fetchUnreadNotifications()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->get();

        return response()->json($notifications);
    }

    // Fetch all notifications (read and unread) for the logged-in user
    public function fetchAllNotifications()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    // Mark a specific notification as read
    public function markAsRead($id)
    {
        $notification = Notification::find($id);
    
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }
    
        $notification->is_read = true;
        $notification->save();
    
        return response()->json(['message' => 'Notification marked as read']);
    }
    
    
    
    // Delete a specific notification
// Delete all notifications for the authenticated user
public function clearNotifications(Request $request)
{
    $userId = auth()->id(); // Get the logged-in user ID
    
    // Attempt to delete all notifications for the authenticated user
    $deletedCount = Notification::where('user_id', $userId)->delete();
    
    if ($deletedCount > 0) {
        return response()->json(['message' => 'Notifications cleared successfully.']);
    } else {
        return response()->json(['message' => 'No notifications found to clear.']);
    }
}


}