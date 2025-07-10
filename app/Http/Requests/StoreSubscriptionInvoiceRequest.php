<?php

namespace App\Http\Requests;

use App\Enums\InvoiceSubscriptionStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreSubscriptionInvoiceRequest extends FormRequest
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
        $workspace_id  = Auth::user()->workspace_id;
        return [
            'subscription_type' => 'required|in:0,1',
            'subscription_status' => ['required', Rule::in(InvoiceSubscriptionStatus::values())],
            'client_id' => ['required', Rule::exists('clients', 'id')->where('workspace_id', $workspace_id)],
            'company_detail_id' => ['required', Rule::exists('company_details', 'id')->where('workspace_id', $workspace_id)],
            'client_name' => 'nullable|string',
            'user_id' => 'nullable',
            'invoice_number' => ['required', 'string', function ($attribute, $value, $fail) use ($workspace_id) {
                $invoiceNumberExists = Invoice::withTrashed()
                    ->where('type', InvoiceType::INVOICE)
                    ->where('invoice_number', $value)
                    ->whereHas('client', function ($q) use ($workspace_id) {
                        $q->where('workspace_id', $workspace_id);
                    })
                    ->exists();
                if ($invoiceNumberExists) {
                    $fail("Entered invoice number belongs to another Invoice");
                }
            }],
            'currency_id' => 'required|exists:currencies,id',
            'invoice_date' => 'required|date_format:d-m-Y',
            'due_date' => 'required|date_format:d-m-Y',
            'note' => 'nullable',
            'discount' => 'nullable|numeric|min:0.00',
            // 'payment_status' => 'required|in:paid,unpaid,partially_paid',
            'invoice_items' => 'required|array',
            'invoice_items.*.description' => 'required|string',
            'invoice_items.*.price' => 'required|numeric|min:0.01',
            'invoice_items.*.tax_type' => 'nullable|in:vat_20,no_vat'
        ];
    }

    public function attributes()
    {
        return [
            'subscription_type' => 'Subscription type',
            'subscription_status' => 'Subscription status',
            'client_id' => 'Client',
            'company_detail_id' => 'company',
            'client_name' => 'Client\'s name',
            'user_id' => 'Sales person',
            'invoice_number' => 'Invoice number',
            'currency_id' => 'Currency',
            'invoice_date' => 'Invoice date',
            'due_date' => 'Due date',
            'note' => 'Note',
            'discount' => 'Discount',
            // 'payment_status' => 'Payment status',
            'invoice_items' => 'Invoice items',
            'invoice_items.*.description' => 'Description',
            'invoice_items.*.price' => 'Price',
            'invoice_items.*.tax_type' => 'Tax type'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'note' => htmlentities($this->note),
        ]);
    }
}
