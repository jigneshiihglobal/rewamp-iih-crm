<?php

namespace App\Http\Controllers;

use App\Models\ExpenseSubType;
use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeExpenseSubTypeController extends Controller
{
    public function index(ExpenseType $expenseType)
    {
        return response()
            ->json(
                [
                    'expense_sub_types' => $expenseType
                        ->expense_sub_types()
                        ->select('id', 'title')
                        ->get()
                ]
            );
    }
}
