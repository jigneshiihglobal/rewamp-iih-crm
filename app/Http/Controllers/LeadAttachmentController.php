<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Models\File;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeadAttachmentController extends Controller
{
    public function destroy(Request $request, Lead $lead, File $attachment)
    {
        abort_if(
            !Auth::user()->hasRole(['Admin', 'Superadmin'])
                && $attachment->uploaded_by_user_id != Auth::id(),
            403,
            'Only user who uploaded the file or admin can delete'
        );
        try {
            $attachment->delete();
            ActivityLogHelper::log(
                'lead.attachment.deleted',
                'User deleted lead attachment',
                [],
                $request,
                Auth::user(),
                $attachment
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
