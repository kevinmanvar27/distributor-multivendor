<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\ProformaInvoice;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display the notifications page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.notifications.index');
    }
    
    /**
     * Mark a specific notification as read and remove it from the database
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->delete(); // Remove notification from database
        
        // Get updated unread count
        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->count();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification removed',
            'unread_count' => $unreadCount
        ]);
    }
    
    /**
     * Mark all notifications as read and remove them from the database
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        // Delete all unread notifications for the current user
        Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications removed',
            'unread_count' => 0
        ]);
    }
    
    /**
     * Mark all notifications for a specific proforma invoice as read and remove them from the database
     *
     * @param  int  $invoiceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markInvoiceNotificationsAsRead($invoiceId)
    {
        // Get all unread notifications for the current user that are related to this invoice
        $notifications = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->where('type', 'proforma_invoice')
            ->where('data', 'like', '%"invoice_id":' . $invoiceId . '%')
            ->get();
        
        // Delete all matching notifications
        foreach ($notifications as $notification) {
            $notification->delete();
        }
        
        // Get updated unread count
        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->count();
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice notifications removed',
            'unread_count' => $unreadCount,
            'removed_count' => $notifications->count()
        ]);
    }
    
    /**
     * Get notifications for the current user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserNotifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);
        
        // Get unread count
        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->count();
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
}