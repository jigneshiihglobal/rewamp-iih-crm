<?php

namespace App\Http\Middleware;

use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        $user = Auth::user();
        $workspace_id = Auth::user()->workspace_id;
        if (! $request->expectsJson()) {
            CronActivityLogHelper::log(
                'logout',
                'User Automatic logged out.',
                [],
                $request,
                $user,
                null,
                $workspace_id
            );

            return route('login');
        }
    }
}
