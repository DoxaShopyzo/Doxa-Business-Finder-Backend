<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return response()->json($notifications);
    }
    
    public function markRead($id)
    {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();
        return response()->json(['message' => 'Notification marked as read']);
    }
    
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read']);
    }
}