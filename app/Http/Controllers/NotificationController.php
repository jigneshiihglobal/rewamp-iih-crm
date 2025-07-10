<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $notifications = Auth::user()->notifications()->latest()->limit(10)->offset($request->offset)->get();
                return response()->json([
                    'success' => true,
                    'html' => view('partials.notification_list', compact('notifications'))->render()
                ]);
            } catch (\Throwable $th) {
                Log::error($th);
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage(),
                    'error' => $th
                ], 500);
            }
        }
    }

    public function markAllAsRead(Request $request)
    {
        if ($request->ajax()) {
            try {
                Auth::user()->unreadNotifications->markAsRead();
                ActivityLogHelper::log(
                    'notification.mark-all-as-read',
                    'User read all notifications',
                    [],
                    $request,
                    Auth::user(),
                    null
                );
                return response()->json([
                    'success' => true
                ]);
            } catch (\Throwable $th) {
                Log::error($th);
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage(),
                    'error' => $th
                ], 500);
            }
        }
    }
}
