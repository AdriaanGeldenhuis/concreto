<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationWebController extends Controller
{
    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        return response()->json(['unread_count' => $count]);
    }

    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'data' => json_decode($n->data, true),
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at->toISOString(),
                ];
            });

        return response()->json(['data' => $notifications]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $request->user()
            ->notifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'read']);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'all_read']);
    }
}
