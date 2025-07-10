<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\StoreLeadNoteRequest;
use App\Http\Requests\UpdateLeadNoteRequest;
use App\Models\Lead;
use App\Models\LeadNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeadNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function index(Lead $lead, Request $request)
    {
        try {
            if ($request->ajax()) {
                $leadNotes = $lead->lead_notes()
                    ->with([
                        'user' => function ($q) {
                            $q->withTrashed()
                                ->select(
                                    'id',
                                    'first_name',
                                    'last_name'
                                );
                        },
                        'last_edited_by:id,first_name,last_name'
                    ])->latest()->get();
                $leadNotes = $leadNotes->map(function ($leadNote, $id) {
                    $leadNote->formatted_created_at = $leadNote->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
                    $leadNote->formatted_last_edited_at = $leadNote->last_edited_at ? $leadNote->last_edited_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT) : null;
                    $leadNote->can_delete = Auth::user()->can('delete', $leadNote);
                    return $leadNote;
                });
                return response()->json([
                    'leadNotes' => $leadNotes,
                    'success' => true
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage(),
                    'error' => $th
                ], 500);
            }
        }
    }

    public function store(StoreLeadNoteRequest $request, Lead $lead)
    {
        try {
            $leadNote = $lead->lead_notes()->create($request->validated() + ['user_id' => Auth::id()]);

            ActivityLogHelper::log(
                "lead.note.created",
                "Lead note created by " . Auth::user()->full_name,
                [],
                $request,
                Auth::user(),
                $leadNote
            );

            if ($request->ajax()) {
                $leadNote->loadMissing(['user:id,first_name,last_name', 'last_edited_by:id,first_name,last_name']);
                $leadNote->formatted_created_at = $leadNote->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
                $leadNote->can_delete = Auth::user()->can('delete', $leadNote);
                return response()->json([
                    "success" => true,
                    "leadNote" => $leadNote
                ], 201);
            }

            return redirect()
                ->route('leads.index');
        } catch (\Throwable $th) {
            Log::error($th);
            if ($request->ajax()) {
                return response()->json([
                    "success" => false,
                    "message" => $th->getMessage(),
                    "error" => $th,
                ], 500);
            }
            return redirect()
                ->route('leads.index');
        }
    }

    public function update(UpdateLeadNoteRequest $request, Lead $lead, LeadNote $leadNote)
    {
        $user = Auth::user();
        abort_if($lead->assigned_to != Auth::id() && !$user->hasRole(['Admin', 'Superadmin']), 403);
        try {

            $valid = $request->validated();
            $leadNote->note = $valid['note'] ?? $leadNote->note;
            $leadNote->last_edited_at = now();
            $leadNote->last_edited_by_user_id = Auth::id();
            $leadNote->save();

            ActivityLogHelper::log(
                "lead.note.updated",
                "Lead note updated by " . $user->full_name,
                [],
                $request,
                $user,
                $leadNote
            );

            if ($request->ajax()) {

                $leadNote->loadMissing(['user:id,first_name,last_name', 'last_edited_by:id,first_name,last_name']);
                $leadNote->formatted_created_at = $leadNote->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
                $leadNote->formatted_last_edited_at = optional($leadNote->last_edited_at)->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
                $leadNote->id = EncryptionHelper::encrypt($leadNote->id);

                return response()->json([
                    "success" => true,
                    "leadNote" => $leadNote
                ], 200);
            }

            return redirect()
                ->route('leads.index');
        } catch (\Throwable $th) {
            Log::error($th);
            if ($request->ajax()) {
                return response()->json([
                    "success" => false,
                    "message" => $th->getMessage(),
                    "error" => $th,
                ], 500);
            }
            return redirect()
                ->route('leads.index');
        }
    }

    public function destroy(Request $request, Lead $lead, LeadNote $leadNote)
    {
        $user = Auth::user();
        $this->authorize('delete', $leadNote);

        $leadNote->delete();

        ActivityLogHelper::log(
            "lead.note.deleted",
            "Lead note deleted by " . $user->full_name,
            [],
            $request,
            $user,
            $leadNote
        );

        return response()->json([
            'success' => true
        ]);
    }
}
