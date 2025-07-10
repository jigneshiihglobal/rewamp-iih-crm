<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\StoreInvoiceNoteRequest;
use App\Http\Requests\UpdateInvoiceNoteRequest;
use Illuminate\Http\Request;
use App\Models\InvoicesNote;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoicesNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $invoices_note_id = EncryptionHelper::decrypt($request->invoices_id);
        $invoices = Invoice::withTrashed()->with('client')->where('id',$invoices_note_id)->first();
        abort_if(
            $invoices->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        try {
            if ($request->ajax()) {
                $invoices_note = $invoices->invoice_notes()
                    ->with([
                        'user:id,first_name,last_name',
                        'last_edited_by:id,first_name,last_name'
                    ])
                    ->latest()
                    ->get();

                $invoices_note = $invoices_note->map(function ($invoicesNote, $id) {
                    $invoicesNote->formatted_created_at = $invoicesNote->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::INVOICE_NOTE_CREATED_AT);
                    $invoicesNote->formatted_last_edited_at = $invoicesNote->last_edited_at ? $invoicesNote->last_edited_at->setTimezone(Auth::user()->timezone)->format(DateHelper::INVOICE_NOTE_CREATED_AT) : null;
                    $invoicesNote->can_delete = true;
                    return $invoicesNote;
                });
                return response()->json([
                    'invoicesNotes' => $invoices_note,
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoiceNoteRequest $request)
    {
        $invoices_note_id = EncryptionHelper::decrypt($request->invoices_id);
        $invoices = Invoice::withTrashed()->with('client')->where('id',$invoices_note_id)->first();
        abort_if(
            $invoices->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        try {
            $invoicesNote = $invoices->invoice_notes()->create($request->validated() + ['user_id' => Auth::id()]);
            ActivityLogHelper::log(
                "invoices.modals.created",
                "Invoices note created by " . Auth::user()->full_name,
                [],
                $request,
                Auth::user(),
                $invoicesNote
            );

            if ($request->ajax()) {
                $invoicesNote->loadMissing(['user:id,first_name,last_name', 'last_edited_by:id,first_name,last_name']);
                $invoicesNote->formatted_created_at = $invoicesNote->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::INVOICE_NOTE_CREATED_AT);
                $invoicesNote->can_delete = true;

                return response()->json([
                    "success" => true,
                    "invoicesNote" => $invoicesNote
                ], 201);
            }

            return redirect()
                ->route('invoices.index');
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
                ->route('invoices.index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceNoteRequest $request, Invoice $invoices, InvoicesNote $invoices_note)
    {
        $invoices_note_id = EncryptionHelper::decrypt($request->invoices_note_id);
        $invoices_note = InvoicesNote::with(['invoices' => function ($query) {
            $query->withTrashed();
        }])->where('id',$invoices_note_id)->first();
        abort_if(
            $invoices_note->invoices->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        $user = Auth::user();
        try {

            $valid = $request->validated();
            $invoices_note->note = $valid['note'] ?? $invoices_note->note;
            $invoices_note->last_edited_at = now();
            $invoices_note->last_edited_by_user_id = Auth::id();
            $invoices_note->save();

            ActivityLogHelper::log(
                "invoices.modals.updated",
                "Invoices note updated by " . $user->full_name,
                [],
                $request,
                $user,
                $invoices_note
            );

            if ($request->ajax()) {

                $invoices_note->loadMissing(['user:id,first_name,last_name', 'last_edited_by:id,first_name,last_name']);
                $invoices_note->formatted_created_at = $invoices_note->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::INVOICE_NOTE_CREATED_AT);
                $invoices_note->formatted_last_edited_at = optional($invoices_note->last_edited_at)->setTimezone(Auth::user()->timezone)->format(DateHelper::INVOICE_NOTE_CREATED_AT);
                $invoices_note->id = EncryptionHelper::encrypt($invoices_note->id);

                return response()->json([
                    "success" => true,
                    "invoicesNote" => $invoices_note
                ], 200);
            }

            return redirect()->route('invoices.index');

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
                ->route('invoices.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Invoice $invoices, InvoicesNote $invoices_note)
    {
        $invoices_note_id = EncryptionHelper::decrypt($request->invoiceNoteId);
        $invoices_note = InvoicesNote::with(['invoices' => function ($query) {
            $query->withTrashed();
        }])->where('id',$invoices_note_id)->first();
        abort_if(
            $invoices_note->invoices->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        $user = Auth::user();
        $invoices_note->delete();

        ActivityLogHelper::log(
            "invoices.modals.deleted",
            "Invoices note deleted by " . $user->full_name,
            [],
            $request,
            $user,
            $invoices_note
        );

        return response()->json([
            'success' => true
        ]);
    }
}
