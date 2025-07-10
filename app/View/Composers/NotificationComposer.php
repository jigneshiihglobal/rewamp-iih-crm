<?php

namespace App\View\Composers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationComposer
{
    public function compose(View $view)
    {
        $notifications = Auth::user()->notifications()->limit(10)->get();
        $notification_count = Auth::user()->notifications()->count();
        $unread_notification_count = Auth::user()->unreadNotifications()->count();

        $view->with('notifications', $notifications);
        $view->with('notification_count', $notification_count);
        $view->with('unread_notification_count', $unread_notification_count);
    }
}
