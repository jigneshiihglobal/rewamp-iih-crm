<?php

namespace App\Http\Requests;

use App\Enums\InvoiceType;
use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCreditNoteRequest extends FormRequest
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
        $credit_note = $this->route('credit_note');
        $workspace_id = Auth::user()->workspace_id;

        return [
            'client_id' => ['required', Rule::exists('clients', 'id')->where('workspace_id', $workspace_id)],
            'company_detail_id' => ['nullable', Rule::exists('company_details', 'id')->where('workspace_id', $workspace_id)],
            'client_name' => 'nullable|string',
            'user_id' => 'nullable',
            'invoice_number' => ['required', 'string', function ($attribute, $value, $fail) use ($workspace_id, $credit_note) {
                $invoiceNumberExists = Invoice::where('type', InvoiceType::CREDIT_NOTE)
                    ->where('invoice_number', $value)
                    ->whereHas('client', function ($q) use ($workspace_id, $credit_note) {
                        $q->where('workspace_id', $workspace_id);
                    })
                    ->where('id', '!=', $credit_note->id)
                    ->exists();
                if ($invoiceNumberExists) {
                    $fail("Entered credit note number belongs to another Credit Note");
                }
            }],
            'currency_id' => 'required|exists:currencies,id',
            'invoice_date' => 'required|date_format:d-m-Y',
            'note' => 'nullable',
            'discount' => 'nullable|numeric|min:0.00',
            'invoice_items' => 'required|array',
            'invoice_items.*.description' => 'required|string',
            'invoice_items.*.price' => 'required|numeric|min:0.01',
            'invoice_items.*.tax_type' => 'nullable|in:vat_20,no_vat'
        ];
    }
}
