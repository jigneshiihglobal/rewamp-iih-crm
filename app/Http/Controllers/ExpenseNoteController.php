<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\StoreExpenseNoteRequest;
use App\Http\Requests\UpdateExpenseNoteRequest;
use App\Models\Expense;
use App\Models\ExpenseNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpenseNoteController extends Controller
{
    public function index(Request $request, Expense $expense)
    {
        abort_if(
            $expense->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        try {
            if ($request->ajax()) {
                $expenseNotes = $expense->expense_notes()
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

    public function store(StoreExpenseNoteRequest $request, Expense $expense)
    {
        abort_if(
            $expense->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        try {
            $expenseNote = $expense->expense_notes()->create($request->validated() + ['user_id' => Auth::id()]);

            ActivityLogHelper::log(
                "expense.expense_notes.created",
                "Expense note created by " . Auth::user()->full_name,
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

    public function update(UpdateExpenseNoteRequest $request, Expense $expense, ExpenseNote $expenseNote)
    {
        abort_if(
            $expenseNote->expense->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        $user = Auth::user();
        try {

            $valid = $request->validated();
            $expenseNote->note = $valid['note'] ?? $expenseNote->note;
            $expenseNote->last_edited_at = now();
            $expenseNote->last_edited_by_user_id = Auth::id();
            $expenseNote->save();

            ActivityLogHelper::log(
                "expense.expense_notes.updated",
                "Expense note updated by " . $user->full_name,
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

    public function destroy(Request $request, Expense $expense, ExpenseNote $expenseNote)
    {
        abort_if(
            $expense->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        $user = Auth::user();
        $expenseNote->delete();

        ActivityLogHelper::log(
            "expense.expense_notes.deleted",
            "Expense note deleted by " . $user->full_name,
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
