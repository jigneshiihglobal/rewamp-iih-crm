<?php

namespace App\Http\Requests;

use App\Enums\InvoicePaymentStatus;
use App\Enums\InvoiceSubscriptionStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionInvoiceRequest extends FormRequest
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
        $invoice = $this->route('invoice');
        $workspace_id = $invoice->client->workspace->id;

        return [
            'subscription_type' => 'required|in:0,1',
            'subscription_status' => ['required', Rule::in(InvoiceSubscriptionStatus::values())],
            'client_id' => ['required', Rule::exists('clients', 'id')->where('workspace_id', $workspace_id)],
            'company_detail_id' => ['required', Rule::exists('company_details', 'id')->where('workspace_id', $workspace_id)],
            'client_name' => 'nullable|string',
            'user_id' => 'nullable',
            'invoice_number' => ['required', 'string', function ($attribute, $value, $fail) use ($workspace_id, $invoice) {
                $invoiceNumberExists = Invoice::withTrashed()
                    ->where('type', InvoiceType::INVOICE)
                    ->where('invoice_number', $value)
                    ->whereHas('client', function ($q) use ($workspace_id) {
                        $q->where('workspace_id', $workspace_id);
                    })
                    ->where('id', '!=', $invoice->id)
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
            'invoice_items' => [Rule::requiredIf($invoice->payment_status == InvoicePaymentStatus::UNPAID), 'array'],
            'invoice_items.*.id' => ['nullable', Rule::exists('invoice_items', 'id')->where('invoice_id', $this->route('invoice')->id)],
            'invoice_items.*.description' => [Rule::requiredIf($invoice->payment_status == InvoicePaymentStatus::UNPAID), 'string'],
            'invoice_items.*.price' => [Rule::requiredIf($invoice->payment_status == InvoicePaymentStatus::UNPAID), 'numeric', 'min:0.01'],
            'invoice_items.*.tax_type' => 'nullable|in:vat_20,no_vat'
        ];
    }

    public function attributes()
    {
        return [
            'subscription_type' => 'Subscription type',
            'subscription_status' => "Subscription status",
            'client_id' => 'Client',
            'company_detail_id' => 'Company',
            'client_name' => 'Client\'s name',
            'user_id' => 'Sales person',
            'invoice_number' => 'Invoice number',
            'currency_id' => 'Currency',
            'invoice_date' => 'Invoice date',
            'due_date' => 'Due date',
            'note' => 'Note',
            // 'payment_status' => 'Payment status',
            'discount' => 'Discount',
            'invoice_items' => 'Invoice items',
            'invoice_items.*.id' => 'Invoice Item ID',
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
