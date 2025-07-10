<?php

namespace App\Http\Requests;

use App\Enums\ExpenseFrequency;
use App\Enums\ExpenseType;
use App\Models\ExpenseSubType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpensesRequest extends FormRequest
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
        return [
            'expenses' => 'required|array|min:1',
            'expenses.*' =>  'required|array|min:1',
            'expenses.*.client_id' => 'required|exists:clients,id',
            'expenses.*.project_name' =>  'required|string|max:100',
            'expenses.*.expense_date' => 'required|date_format:d/m/Y',
            'expenses.*.expense_type_id' => 'required|exists:expense_types,id',
            'expenses.*.expense_sub_type_id' => [
                'required',
                function ($attr, $val, $fail) {
                    $index = str_replace(["expenses.", ".expense_sub_type_id"], "", $attr);
                    $expense_type_id = $this->input("expenses.{$index}.expense_type_id");
                    $expense_sub_type_id_doesnt_exist = ExpenseSubType::query()
                        ->where('id', $val)
                        ->where('expense_type_id', $expense_type_id)
                        ->doesntExist();
                    if ($expense_sub_type_id_doesnt_exist) {
                        $fail("Please select a valid expense sub type");
                    }
                }
            ],
            'expenses.*.amount' => 'required|numeric|min:0.01|max:999999',
            'expenses.*.currency_id' => 'required|exists:currencies,id',
            'expenses.*.type' => ['required', Rule::in(ExpenseType::values())],
            'expenses.*.frequency' => ['required_if:type,1', Rule::in(ExpenseFrequency::values())],
        ];
    }

    public function attributes()
    {
        return [
            'expenses'                          => 'expenses',
            'expenses.*'                        => 'expenses',
            'expenses.*.client_id'              => 'client',
            'expenses.*.project_name'           =>  'project name',
            'expenses.*.expense_date'           => 'expense date',
            'expenses.*.expense_type_id'        => 'expense type',
            'expenses.*.expense_sub_type_id'    => 'expense sub type',
            'expenses.*.amount'                 => 'amount',
            'expenses.*.currency_id'            => 'currency',
            'expenses.*.type'                   => 'type',
            'expenses.*.frequency'              => 'frequency',
        ];
    }
}
