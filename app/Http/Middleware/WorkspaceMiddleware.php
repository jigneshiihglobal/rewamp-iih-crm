<?php

namespace App\Http\Middleware;

use App\Helpers\ActivityLogHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$workspaceSlugs)
    {
        $user = Auth::user();

        foreach ($user->workspaces as $workspace) {
            if (in_array($workspace->slug, $workspaceSlugs)) {
                if (!$user->workspace_id) {
                    $user->workspace_id = $workspace->id;
                    $user->save();
                    ActivityLogHelper::log('user.workspace.changed', "User switched workspace.", [], $request, $user, null);
                } else if ($workspace->id != $user->workspace_id) {
                    continue;
                }
                return $next($request);
            }
        }

        $user->workspace_id = null;
        $user->save();

        abort(403, 'Unauthorized access to workspace');
    }
}
