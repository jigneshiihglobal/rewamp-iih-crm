<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function changeWorkspace(Request $request, Workspace $workspace)
    {
        $user = Auth::user();
        $workspaces = $user->workspaces;
        $workspaceName = $workspace->name;

        if (!$workspaces->contains($workspace->id)) {

            return redirect()->back()->with([
                'status' => "Unable to switch to {$workspaceName} workspace!",
                'type' => 'error'
            ]);
        }

        $user->workspace_id = $workspace->id;
        $user->save();

        ActivityLogHelper::log('user.workspace.switched', "User switched to \"{$workspaceName}\"", [], $request, $user, null);

        return redirect()->route('leads.index')->with([
            'status' => "Switched to {$workspaceName} successfully!",
            'type' => 'success'
        ]);
    }
}
