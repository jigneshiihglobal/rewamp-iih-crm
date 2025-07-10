<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\UpdateExpenseNoteRequest;
use App\Models\MarketingExpenseNote;
use App\Models\MarketingExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpenseMarketingNoteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $id = request()->segment(2);
            $id = EncryptionHelper::decrypt($id);
            $expense = MarketingExpense::find($id);

            if ($request->ajax()) {
                $expenseNotes = $expense->marketing_expense_notes()
                    ->with([
                        'user:id,first_name,last_name',
                        'last_edited_by:id,first_name,last_name'
                    ])
                    ->latest()
                    ->get();

                $expenseNotes = $expenseNotes->map(function ($expenseNote, $id) {
                    $expenseNote->formatted_created_at = $expenseNote->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::EXPENSE_NOTE_CREATED_AT);
                    $expenseNote->formatted_last_edited_at = $expenseNote->last_edited_at ? $expenseNote->last_edited_at->setTimezone(Auth::user()->timezone)->format(DateHelper::EXPENSE_NOTE_CREATED_AT) : null;
                    $expenseNote->can_delete = true;
                    return $expenseNote;
                });
                return response()->json([
                    'expenseNotes' => $expenseNotes,
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

    public function store(Request $request, MarketingExpense $expense)
    {
        try {
            $expenseNoteData = $request->toArray();
            $id = request()->segment(2);
            $id = EncryptionHelper::decrypt($id);

            $expenseNote = new MarketingExpenseNote();
            $expenseNote->marketing_expense_id = $id ?? '';
            $expenseNote->user_id = Auth::id() ?? '';
            $expenseNote->note = $expenseNoteData['note'] ?? '';
            $expenseNote->save();

            ActivityLogHelper::log(
                "marketing.marketing_expense_notes.created",
                "Marketing expense note created by " . Auth::user()->full_name,
                [],
                $request,
                Auth::user(),
                $expenseNote
            );

            if ($request->ajax()) {
                $expenseNote->loadMissing(['user:id,first_name,last_name', 'last_edited_by:id,first_name,last_name']);
                $expenseNote->formatted_created_at = $expenseNote->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::EXPENSE_NOTE_CREATED_AT);
                $expenseNote->can_delete = true;

                return response()->json([
                    "success" => true,
                    "expenseNote" => $expenseNote
                ], 201);
            }

            return redirect()
                ->route('expenses.index');
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
                ->route('expenses.index');
        }
    }

    public function update(UpdateExpenseNoteRequest $request, MarketingExpense $expense, MarketingExpenseNote $expenseNote)
    {
        $user = Auth::user();
        try {
            $lead_id = request()->segment(2);
            $note_id = request()->segment(4);
            $note_id = EncryptionHelper::decrypt($note_id);

            $expenseNote = MarketingExpenseNote::find($note_id);
            $expenseNote->note = $request->note ?? $expenseNote->note;
            $expenseNote->last_edited_at = now();
            $expenseNote->last_edited_by_user_id = Auth::id();
            $expenseNote->save();

            ActivityLogHelper::log(
                "marketing.marketing_expense_notes.updated",
                "Marketing expense note updated by " . $user->full_name,
                [],
                $request,
                $user,
                $expenseNote
            );

            if ($request->ajax()) {

                $expenseNote->loadMissing(['user:id,first_name,last_name', 'last_edited_by:id,first_name,last_name']);
                $expenseNote->formatted_created_at = $expenseNote->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::EXPENSE_NOTE_CREATED_AT);
                $expenseNote->formatted_last_edited_at = optional($expenseNote->last_edited_at)->setTimezone(Auth::user()->timezone)->format(DateHelper::EXPENSE_NOTE_CREATED_AT);
                $expenseNote->id = EncryptionHelper::encrypt($expenseNote->id);

                return response()->json([
                    "success" => true,
                    "expenseNote" => $expenseNote
                ], 200);
            }

            return redirect()
                ->route('expenses.index');
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
                ->route('expenses.index');
        }
    }

    public function destroy(Request $request, MarketingExpense $expense, MarketingExpenseNote $expenseNote)
    {
        $id = $request->id;
        $id = EncryptionHelper::decrypt($id);
        $expenseNote = MarketingExpenseNote::find($id);

        $user = Auth::user();
        $expenseNote->delete();

        ActivityLogHelper::log(
            "marketing.marketing_expense_notes.deleted",
            "Marketing expense note deleted by " . $user->full_name,
            [],
            $request,
            $user,
            $expenseNote
        );

        return response()->json([
            'success' => true
        ]);
    }
}
