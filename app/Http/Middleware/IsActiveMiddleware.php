<?php

namespace App\Http\Middleware;

use App\Helpers\ActivityLogHelper;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsActiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $is_active = true)
    {
        $user = Auth::user();
        if ($user->is_active != $is_active) {
            Auth::guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            ActivityLogHelper::log('logout', 'User logged out.', [], $request, $user);
            if($request->ajax() || $request->expectsJson()) {
                return response()->json([], 401);
            }
            return redirect()->route('login')->with([
                'message' =>  'Please contact admin',
                'status' => 'User is inactive',
                'type' => 'error'
            ]);
        }
        return  $next($request);
    }
}
