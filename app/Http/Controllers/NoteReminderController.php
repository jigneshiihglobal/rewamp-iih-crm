<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\StoreInvoiceNoteRequest;
use App\Http\Requests\UpdateInvoiceNoteRequest;
use App\Models\Invoice;
use App\Models\InvoicesNote;
use App\Models\NoteReminder;
use App\Models\NoteReminderAmount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use DateTime;
use Carbon\Carbon;


class NoteReminderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $invoices_note_id = EncryptionHelper::decrypt($request->note_reminder_id);
            if ($request->ajax()) {
                $note_reminder = NoteReminder::
                    select("note_reminders.*",DB::raw("GROUP_CONCAT(clients.name) as client_name"))
                    ->with([
                        'user:id,first_name,last_name',
                        'last_edited_by:id,first_name,last_name'
                    ])
                    ->leftjoin("clients",DB::raw("FIND_IN_SET(clients.id,note_reminders.assign_client_id)"),">",DB::raw("'0'"))
                    ->where('note_reminders.workspace_id',Auth::user()->workspace_id)
                    ->latest()
                    ->groupBy("note_reminders.id")
                    ->get();

                $note_reminder = $note_reminder->map(function ($noteReminders, $id) {
                    $noteReminders->formatted_created_at = $noteReminders->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::NOTE_REMINDER_CREATED_AT);
                    $noteReminders->formatted_last_edited_at = $noteReminders->last_edited_at ? $noteReminders->last_edited_at->setTimezone(Auth::user()->timezone)->format(DateHelper::NOTE_REMINDER_CREATED_AT) : null;
                    $noteReminders->can_delete = true;
                    if($noteReminders->assign_client_id){
                        $noteReminders->encrypt_assign_client_id = EncryptionHelper::encrypt($noteReminders->assign_client_id);
                    }
                    return $noteReminders;
                });

                return response()->json([
                    'note_reminders' => $note_reminder,
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

    public function store(Request $request)
    {
        try {
            $assign_client_id = '';
            if(isset($request->client_ids) && !empty($request->client_ids)){
                $assign_client_id = implode(',',$request->client_ids);
            }
            $NoteReminder = new NoteReminder();
            $NoteReminder->user_id = Auth::id();
            $NoteReminder->note = $request->note;
            $NoteReminder->assign_client_id = $assign_client_id ?? '';
            $NoteReminder->workspace_id = Auth::user()->workspace_id;
            $NoteReminder->without_vat = $request->without_vat ?? null;
            $NoteReminder->vat_amount = $request->vat_amount ?? null;
            $NoteReminder->total_amount = $request->numeric_value ?? null;
            $NoteReminder->vat_status = isset($request->vat_add) && $request->vat_add == 'on' ? '1' : '0';
            $NoteReminder->save();

            if($NoteReminder){
                if(isset($request->table_data) && !empty($request->table_data)){
                    $amounts = $request->table_data;
                    foreach ($amounts as $key => $amount) {
                        if($amount['received_amount'] == 0 && $key != 0){
                            return false;
                        }
                        $note_reminder_amount = new NoteReminderAmount;
                        $note_reminder_amount->note_reminder_id = $NoteReminder->id;
                        $note_reminder_amount->received_amount = $amount['received_amount'];
                        $note_reminder_amount->pending_amount =  $amount['pending_amount'];
                        $received_at = DateTime::createFromFormat('d-m-Y', $amount['received_at']);
                        $note_reminder_amount->received_at = $received_at ? $received_at->format('Y-m-d') : Carbon::today()->format('Y-m-d');
                        $note_reminder_amount->save();
                    }
                }
            }

            ActivityLogHelper::log(
                "invoices.modals.created",
                "Note reminder created by " . Auth::user()->full_name,
                [],
                $request,
                Auth::user(),
                $NoteReminder
            );

            if ($request->ajax()) {
                $NoteReminder->loadMissing(['user:id,first_name,last_name', 'last_edited_by:id,first_name,last_name']);
                $NoteReminder->formatted_created_at = $NoteReminder->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::NOTE_REMINDER_CREATED_AT);
                $NoteReminder->can_delete = true;

                return response()->json([
                    "success" => true,
                    "NoteReminder" => $NoteReminder
                ], 201);
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

    public function show(Request $request, NoteReminder $note_reminders)
    {
        try {
            $note_reminder_id = EncryptionHelper::decrypt($request->noteReminderId);
            $NoteReminder = NoteReminder::find($note_reminder_id);
            $client = explode(',',$NoteReminder->assign_client_id);
            $total_amt = $NoteReminder->total_amount ?? '';
            $vat_status = $NoteReminder->vat_status ?? '0';
            $note_reminder_amount = NoteReminderAmount::select('received_amount','pending_amount','received_at')->where('note_reminder_id',$note_reminder_id)->get();
            return response()->json([
                "success" => true,
                "client" => $client,
                "total_amt" => $total_amt,
                "vat_status" => $vat_status,
                "note_reminder_amount" => $note_reminder_amount,
            ], 200);
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
    public function update(Request $request, NoteReminder $note_reminders)
    {
        $note_reminder_id = EncryptionHelper::decrypt($request->note_reminder_id);
        $user = Auth::user();
        try {
            $assign_client_id = '';
            if(isset($request->client_ids) && !empty($request->client_ids)){
                $assign_client_id = implode(',',$request->client_ids);
            }
            $NoteReminder = NoteReminder::find($note_reminder_id);
            $NoteReminder->user_id = $NoteReminder->user_id;
            $NoteReminder->note = $request->note;
            $NoteReminder->assign_client_id = $assign_client_id ?? '';
            $NoteReminder->workspace_id = Auth::user()->workspace_id;
            $NoteReminder->without_vat = $request->without_vat ?? null;
            $NoteReminder->vat_amount = $request->vat_amount ?? null;
            $NoteReminder->last_edited_at = now();
            $NoteReminder->last_edited_by_user_id = Auth::id();
            $NoteReminder->total_amount = $request->numeric_value ?? null;
            $NoteReminder->vat_status = isset($request->vat_add) && $request->vat_add == 'on' ? '1' : '0';
            $NoteReminder->save();

            if($NoteReminder){
                if (isset($request->table_data) && !empty($request->table_data)) {
                    $amounts = $request->table_data;
                    foreach ($amounts as $key => $amount) {
                        // Check if the combination of received_amount and pending_amount already exists
                        $existingAmount = NoteReminderAmount::where('note_reminder_id', $NoteReminder->id)
                            ->where('received_amount', $amount['received_amount'])
                            ->where('pending_amount',  $amount['pending_amount'])
                            ->first();
                        // If the combination doesn't exist, add it
                        if($amount['received_amount'] == 0 && $key != 0){
                            return false;
                        }
                        if (!$existingAmount) {
                            $note_reminder_amount = new NoteReminderAmount;
                            $note_reminder_amount->note_reminder_id = $NoteReminder->id;
                            $note_reminder_amount->received_amount = $amount['received_amount'];
                            $note_reminder_amount->pending_amount =  $amount['pending_amount'];
                            $received_at = DateTime::createFromFormat('d-m-Y', $amount['received_at']);
                            $note_reminder_amount->received_at = $received_at ? $received_at->format('Y-m-d') : Carbon::today()->format('Y-m-d');
                            $note_reminder_amount->save();
                        }
                    }
                }
            }

            ActivityLogHelper::log(
                "invoices.modals.updated",
                "Note reminder updated by " . $user->full_name,
                [],
                $request,
                $user,
                $NoteReminder
            );

            if ($request->ajax()) {

                $NoteReminder->loadMissing(['user:id,first_name,last_name', 'last_edited_by:id,first_name,last_name']);
                $NoteReminder->formatted_created_at = $NoteReminder->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::NOTE_REMINDER_CREATED_AT);
                $NoteReminder->formatted_last_edited_at = optional($NoteReminder->last_edited_at)->setTimezone(Auth::user()->timezone)->format(DateHelper::NOTE_REMINDER_CREATED_AT);
                $NoteReminder->id = EncryptionHelper::encrypt($NoteReminder->id);

                return response()->json([
                    "success" => true,
                    "NoteReminder" => $NoteReminder
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

    public function destroy(Request $request)
    {
        try {
            $user = Auth::user();
            $note_reminder_id = EncryptionHelper::decrypt($request->noteReminderId);
            $NoteReminder = NoteReminder::find($note_reminder_id);
            NoteReminderAmount::where('note_reminder_id', $note_reminder_id)->delete();
            $NoteReminder->delete();

            ActivityLogHelper::log(
                "invoices.modals.deleted",
                "Note reminder deleted by " . $user->full_name,
                [],
                $request,
                $user,
                $NoteReminder
            );

            return response()->json([
                'success' => true
            ]);
        }catch (\Throwable $th) {
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
}
