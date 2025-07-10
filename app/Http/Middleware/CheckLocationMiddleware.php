<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLocationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $positionLatitude = $_COOKIE['posLat'] ?? null;
        $positionLongitude = $_COOKIE['posLon'] ?? null;

        if (!$positionLatitude || !$positionLongitude || empty($positionLatitude) || empty($positionLongitude)) {
            if (Auth::check()) {
                Auth::guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            if($request->ajax()) {
                return response()->json([
                    'logout_cause' => 'Please allow location permission to login.'
                ], 401);
            }
            return redirect()->route('login')->with([
                'type' => 'error',
                'status' => 'Please allow location permission to login',
                'message' => "Unauthorized access!"
            ]);
        }
        return $next($request);
    }
}
