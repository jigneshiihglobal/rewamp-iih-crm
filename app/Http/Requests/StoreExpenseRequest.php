<?php

namespace App\Http\Requests;

use App\Enums\ExpenseType;
use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $expense_type_id = $this->expense_type_id;

        return [
            'client_id' => [
                'required',
                function ($attr, $val, $fail) {
                    $clientDoesntExist = Client::where('workspace_id', Auth::user()->workspace_id)->where('id', $val)->doesntExist();
                    if($clientDoesntExist) {
                        $fail("Please select a valid client");
                    }
                }
            ],
            'project_name' =>  'required|string|max:100',
            'expense_date' => 'required|date_format:d/m/Y',
            'expense_type_id' => 'required|exists:expense_types,id',
            'expense_sub_type_id' => ['required', Rule::exists('expense_sub_types', 'id')->where('expense_type_id', $expense_type_id)],
            'amount' => 'required|numeric|min:0.01|max:999999',
            'currency_id' => 'required|exists:currencies,id',
            'type' => ['required', Rule::in(ExpenseType::values())],
            'frequency' => ['required_if:type,1', Rule::in(ExpenseType::values())],
        ];
    }

    public function attributes()
    {
        return [
            'client_id' => 'client',
            'project_name' =>  'project name',
            'expense_date' => 'expense date',
            'expense_type_id' => 'expense type',
            'expense_sub_type_id' => 'expense sub type',
            'amount' => 'amount',
            'currency_id' => 'currency',
            'type' => 'type',
            'frequency' => 'frequency',
        ];
    }
}
