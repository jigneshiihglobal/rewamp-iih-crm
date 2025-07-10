@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <link rel="stylesheet" href="{{ asset('app-assets/css/custom/bootstrap-datepicker.css?v=' . config('versions.css')) }}">
    <style>
        #invoiceItemsTable table td {
            padding: 4px;
        }

        textarea.form-control {
            padding: 0.188rem 0.5rem !important;
        }

        .amount_input {
            min-width: 8rem;
        }
    </style>
@endsection

@section('content')
    <section>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-1 invoice-note-lbl">
                        <h4 class="card-title"> {{ $view_config['title'] ?? 'Create' }} Subscription Invoice</h4>
                        <div>
                           <a class="btn btn-xs btn-info pull-right" href="{{URL::previous()}}" >
                            <i data-feather="arrow-left" color="white"></i>
                            &nbsp;
                            <font color="white"> Back </font>
                            </a>

                        </div>
                    </div>
                    <div id="invoice_notes"></div>
                    <hr class="m-0">
                    <div class="card-body pt-1">
                        <form class="form repeater" id="invoice_form">
                            <div class="row">
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="client_id">Customer <span
                                                class="text-danger">*</span></label>
                                        <select id="client_id" class="select2-size-sm form-select select2" name="client_id">
                                            <option value="">Select a customer</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}"
                                                    {{ $client->id == old('client_id', $invoice->client_id ?? null) ? 'selected' : '' }}
                                                    data-encrypted-id="{{ $client->encrypted_id }}">
                                                    {{ $client->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('client_id')
                                            <span id="client_id-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label for="invoice_date" class="form-label">Invoice Date <span
                                                class="text-danger">*</span></label>
                                        @if(isset($invoice->invoice_date))
                                            <input type="text" class="form-control form-control-sm"
                                                   placeholder="Invoice Date" name="invoice_date"
                                                   value="{{ old('invoice_date', isset($invoice) && isset($invoice->invoice_date) ? $invoice->invoice_date->format('d-m-Y') : date('d-m-Y')) }}"
                                                   autocomplete="off" readonly />
                                        @else
                                            <input type="text" id="invoice_date" class="form-control form-control-sm"
                                                   placeholder="Invoice Date" name="invoice_date"
                                                   value="{{ old('invoice_date', date('d-m-Y')) }}"
                                                   autocomplete="off"/>
                                        @endif
                                        @error('invoice_date')
                                            <span id="invoice_date-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label for="due_date" class="form-label">Due Date <span
                                                class="text-danger">*</span></label>
                                        @if(isset($invoice->due_date))
                                            <input type="text" class="form-control form-control-sm"
                                                   placeholder="Due Date" name="due_date" autocomplete="off"
                                                   value="{{ old('due_date', isset($invoice) && isset($invoice->due_date) ? $invoice->due_date->format('d-m-Y') : date('d-m-Y', strtotime('+5 days'))) }}" readonly/>
                                        @else
                                            <input type="text" id="due_date" class="form-control form-control-sm"
                                                   placeholder="Due Date" name="due_date" autocomplete="off"
                                                   value="{{ old('due_date', date('d-m-Y', strtotime('+5 days'))) }}" />
                                        @endif
                                        @error('due_date')
                                            <span id="due_date-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="invoice_number">Invoice Number <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="invoice_number" class="form-control form-control-sm"
                                            placeholder="Invoice Number" name="invoice_number" autocomplete="off"
                                            value="{{ old('invoice_number', isset($invoice) && isset($invoice->invoice_number) && Route::is('invoices.subscription.edit') ? $invoice->invoice_number : $invoice_number) }}" />
                                        @error('invoice_number')
                                            <span id="invoice_number-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="currency_id">Currency <span
                                                class="text-danger">*</span></label>
                                        <select id="currency_id" class="select2-size-sm form-select select2"
                                            name="currency_id">
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}"
                                                    {{ $currency->id == old('currency_id', isset($invoice) && isset($invoice->currency_id) ? $invoice->currency_id : 2) ? 'selected' : '' }}
                                                    data-symbol="{{ $currency->symbol }}">
                                                    {{ $currency->symbol }} - {{ $currency->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('currency_id')
                                            <span id="currency_id-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1 form-group">
                                        <label class="form-label" for="user_id">Sales Person</label>
                                        <select id="user_id" class="select2-size-sm form-select select2" name="user_id">
                                            <option value="" selected>Select Sales Person</option>
                                            @foreach ($sales_people as $sales_person)
                                                <option value="{{ $sales_person->id }}"
                                                    {{ $sales_person->id == old('user_id', $invoice->user_id ?? '') ? 'selected' : '' }}>
                                                    {{ $sales_person->full_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <span id="user_id-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                {{-- <div class="col-md-3 col-12">
                                    <div class="mb-1 form-group">
                                        <label class="form-label" for="payment_status">Payment Status <span
                                                class="text-danger">*</span></label>
                                        <select id="payment_status" class="select2-size-sm form-select select2"
                                            name="payment_status">
                                            <option value="" disabled>Select Payment Status</option>
                                            <option value="unpaid" @if (isset($invoice) && $invoice->payment_status == 'unpaid') selected @endif>Unpaid
                                            </option>
                                            <option value="partially_paid"
                                                @if (isset($invoice) && $invoice->payment_status == 'partially_paid') selected @endif>Partially paid
                                            </option>
                                            <option value="paid" @if (isset($invoice) && $invoice->payment_status == 'paid') selected @endif>Paid
                                            </option>
                                        </select>
                                        @error('payment_status')
                                            <span id="payment_status-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div> --}}
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="client_name">Client's Name</label>
                                        <input type="text" id="client_name" class="form-control form-control-sm"
                                            name="client_name" autocomplete="off"
                                            value="{{ old('client_name', isset($invoice) && isset($invoice->client_name) ? $invoice->client_name : '') }}" />
                                        @error('client_name')
                                            <span id="client_name-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                {{--<div class="col-md-3 col-12">
                                    <div class="mb-1 form-group">
                                        <label for="note" class="form-label">Note</label>
                                        <textarea id="note" class="form-control  form-control-sm" name="note" rows="1" autocomplete="off">{{ html_entity_decode(old('note', isset($invoice) ? $invoice->note : '')) }}</textarea>
                                    </div>
                                </div>--}}
                                <div class="col-md-3 col-12">
                                    <div class="mb-1 form-group">
                                        <label for="company_detail_id" class="form-label">Company <span
                                                class="text-danger">*</span></label>
                                        <select id="company_detail_id" class="select2-size-sm form-select select2"
                                            name="company_detail_id">
                                            <option value="" selected>Select Company </option>
                                            @foreach ($company_details as $company_detail)
                                                <option value="{{ $company_detail->id }}"
                                                    {{ $company_detail->id == old('company_detail_id', $invoice->company_detail_id ?? '') ? 'selected' : '' }}>
                                                    {{ $company_detail->name }}</option>
                                            @endforeach
                                        </select>
                                         @error('company_detail_id')
                                            <span id="company_detail_id-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1 form-group">
                                        <label class="form-label" for="subscription_type">Subscription Type <span
                                                class="text-danger">*</span></label>
                                        <select id="subscription_type" class="select2-size-sm form-select select2"
                                            name="subscription_type">
                                            <option value="{{ config('custom.subscription_types.monthly', '0') }}"
                                                selected
                                                {{ config('custom.subscription_types.monthly', '0') == old('subscription_type', $invoice->subscription_type ?? '') ? 'selected' : '' }}>
                                                Monthly</option>
                                            <option value="{{ config('custom.subscription_types.yearly', '1') }}"
                                                {{ config('custom.subscription_types.yearly', '1') == old('subscription_type', $invoice->subscription_type ?? '') ? 'selected' : '' }}>
                                                Yearly</option>
                                        </select>
                                        @error('subscription_type')
                                            <span id="subscription_type-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1 form-group">
                                        <label class="form-label" for="subscription_status">Subscription Status <span
                                                class="text-danger">*</span></label>
                                        {{-- @dd(App\Enums\InvoiceSubscriptionStatus::all()) --}}
                                        <select id="subscription_status" class="select2-size-sm form-select select2"
                                            name="subscription_status">
                                            @foreach (App\Enums\InvoiceSubscriptionStatus::all() as $k => $v)
                                                <option value="{{ $v }}"
                                                    @if (old('subscription_status', $invoice->subscription_status ?? '') === $v) selected @endif>
                                                    {{ Illuminate\Support\Str::headline($v) }}</option>
                                            @endforeach
                                        </select>
                                        @error('subscription_status')
                                            <span id="subscription_status-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12" id="invoiceItemsTable">
                                    @if (Route::is('invoices.subscription.edit') && $invoice->payment_status != 'unpaid')
                                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                                            <div class="alert-body d-flex align-items-center">
                                                <i data-feather="alert-circle" class="me-1 font-medium-1"></i>
                                                You can't modify invoice products/services if the invoice is either
                                                partially
                                                paid or paid.
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                    @endif
                                    <div class="mb-1 table-responsive ">
                                        <table class="table table-borderless">
                                            <thead>
                                                <tr>
                                                    <th class="ps-1">Description <span class="text-danger">*</span></th>
                                                    <th class="text-end pe-1">Price <span class="text-danger">*</span>
                                                    </th>
                                                    <th class="text-end pe-1">VAT Type</th>
                                                    <th class="text-end pe-1">VAT Amount</th>
                                                    <th class="text-end pe-1">Amount</th>
                                                    <th class="text-end pe-1">Total</th>
                                                    <th class="text-end pe-1"></th>
                                                </tr>
                                            </thead>
                                            <tbody data-repeater-list="invoice_items">
                                                @php $invoiceItemState = ''; @endphp
                                                @if (isset($invoice) && isset($invoice->invoice_items) && $invoice->invoice_items->count())
                                                    @if ($invoice->payment_status != 'unpaid' && !$invoice->copying_invoice)
                                                        @php $invoiceItemState = 'readonly'; @endphp
                                                    @endif
                                                    @foreach ($invoice->invoice_items as $invoice_item)
                                                        <tr data-repeater-item>
                                                            <td class="align-top ps-0" style="width: 34%">
                                                                <textarea name="description" rows="1" class="form-control form-control-sm" required autocomplete="off"
                                                                    {{ $invoiceItemState }}>{{ $invoice_item->description ?? '' }}</textarea>
                                                                <input type="hidden" name="id"
                                                                    value="{{ Route::is('invoices.subscription.edit') ? $invoice_item->id : '' }}">
                                                            </td>
                                                            <td class="align-top" style="width: 12%">
                                                                <input type="number"
                                                                    class="form-control form-control-sm text-end amount_input"
                                                                    name="price" required min="0.01"
                                                                    value="{{ $invoice_item->price ?? '0.0' }}"
                                                                    autocomplete="off" {{ $invoiceItemState }} />
                                                            </td>
                                                            <td class="align-top tax_type_td" style="width: 15%">
                                                                <select
                                                                    class="select2-size-sm form-select select2 select2-sm"
                                                                    name="tax_type"
                                                                    value="{{ $invoice_item->tax_type ?? '' }}"
                                                                    {{ $invoiceItemState == 'readonly' ? 'disabled' : '' }}>
                                                                    <option value="" disabled>Select VAT
                                                                        Type
                                                                    </option>
                                                                    <option value="no_vat"
                                                                        {{ $invoice_item->tax_type == 'no_vat' ? 'selected' : '' }}>
                                                                        No VAT</option>
                                                                    <option value="vat_20"
                                                                        {{ $invoice_item->tax_type == 'vat_20' ? 'selected' : '' }}>
                                                                        20% VAT</option>
                                                                </select>
                                                            </td>
                                                            <td class="align-top" style="width: 12%">
                                                                <input type="text"
                                                                    class="form-control form-control-sm text-end amount_input"
                                                                    name="vat_amount"
                                                                    value="{{ $invoice_item->tax_amount ?? '0.0' }}"
                                                                    readonly />
                                                            </td>
                                                            <td class="align-top" style="width: 12%">
                                                                <input type="text"
                                                                    class="form-control form-control-sm text-end amount_input"
                                                                    name="price_amount"
                                                                    value="{{ $invoice_item->price ?? '0.0' }}"
                                                                    readonly />
                                                            </td>
                                                            <td class="align-top" style="width: 12%">
                                                                <input type="text"
                                                                    class="form-control form-control-sm text-end amount_input"
                                                                    name="total_amount"
                                                                    value="{{ $invoice_item->total_price ?? '0.0' }}"
                                                                    readonly />
                                                            </td>
                                                            <td class="align-top pe-0 text-end" style="width: 3%">
                                                                <button data-repeater-delete type="button"
                                                                    class="btn btn-icon btn-sm btn-danger ms-auto"
                                                                    @if ($invoice->invoice_items->count() == 1) style = 'display: none' @endif
                                                                    {{ $invoiceItemState == 'readonly' ? 'disabled' : '' }}>
                                                                    <i data-feather='x'></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr data-repeater-item>
                                                        <td class="align-top ps-0" style="width: 34%">
                                                            <textarea name="description" rows="1" class="form-control form-control-sm" required {{ $invoiceItemState }}></textarea>
                                                        </td>
                                                        <td class="align-top" style="width: 12%">
                                                            <input type="number"
                                                                class="form-control form-control-sm text-end amount_input"
                                                                name="price" required min="0.01"
                                                                {{ $invoiceItemState }} />
                                                        </td>
                                                        <td class="align-top tax_type_td" style="width: 15%">
                                                            <select class="select2-size-sm form-select select2 select2-sm"
                                                                name="tax_type"
                                                                {{ $invoiceItemState == 'readonly' ? 'disabled' : '' }}>
                                                                <option value="" selected disabled>Select VAT
                                                                    Type
                                                                </option>
                                                                <option value="no_vat">No VAT</option>
                                                                <option value="vat_20">20% VAT</option>
                                                            </select>
                                                        </td>
                                                        <td class="align-top" style="width: 12%">
                                                            <input type="text"
                                                                class="form-control form-control-sm text-end amount_input"
                                                                name="vat_amount" value="0.00" readonly />
                                                        </td>
                                                        <td class="align-top" style="width: 12%">
                                                            <input type="text"
                                                                class="form-control form-control-sm text-end amount_input"
                                                                name="price_amount" value="0.00" readonly />
                                                        </td>
                                                        <td class="align-top" style="width: 12%">
                                                            <input type="text"
                                                                class="form-control form-control-sm text-end amount_input"
                                                                name="total_amount" value="0.00" readonly />
                                                        </td>
                                                        <td class="align-top pe-0 text-end" style="width: 3%">
                                                            <button data-repeater-delete type="button"
                                                                class="btn btn-icon btn-sm btn-danger"
                                                                style="display: none"
                                                                {{ $invoiceItemState == 'readonly' ? 'disabled' : '' }}>
                                                                <i data-feather='x'></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 row justify-content-end">
                                    <div class="col-12 mt-1 mt-sm-0 col-md-8">
                                        <button data-repeater-create type="button"
                                            class="btn btn-icon btn-info float-start"
                                            {{ $invoiceItemState == 'readonly' ? 'disabled' : '' }}><i
                                                data-feather="plus-square"></i>
                                            Add item</button>
                                    </div>
                                    <div class="col-12 mt-1 mt-sm-0 pe-sm-3 col-md-4">
                                        <ul class="list-unstyled">
                                            <li class="d-flex justify-content-between">
                                                <span>Price Total:</span>
                                                <span id="sub_total">0.00</span>
                                            </li>
                                            <li class="d-flex justify-content-between">
                                                <span>VAT Total:</span>
                                                <span id="tax_total">0.00</span>
                                            </li>
                                            {{--<li class="d-flex justify-content-between position-relative">
                                                <span id="discount-label" class="text-primary">Discount:</span>
                                                <span id="discount_total">0.00</span>
                                                <div id="discount-popup" class="card p-1 position-absolute border"
                                                    style="z-index: 1; display: none; width: 200px;">
                                                    <div class="form-group">
                                                        <label for="discount" class="form-label">Discount</label>
                                                        <input type="number" name="discount" id='discount'
                                                            autocomplete="off"
                                                            class="form-control form-control-sm text-end"
                                                            {{ $invoiceItemState }}
                                                            value="{{ isset($invoice) ? $invoice->discount ?? '' : '' }}">
                                                    </div>
                                                </div>
                                            </li>--}}
                                            <li>
                                                <hr style="margin: 4px 0">
                                            </li>
                                            <li class="d-flex justify-content-between">
                                                <span>Grand Total:</span>
                                                <span id="grand_total">0.00</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <input type="hidden" id="payment_link" name="payment_link" value="{{ old('payment_link', isset($invoice) && isset($invoice->payment_link) ? $invoice->payment_link : '') }}"/>
                                <div class="col-12 align-items-start pe-2 pe-sm-5">
                                    <div class="float-end">
                                        <a href="{{URL::previous()}}"
                                            class="btn btn-outline-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary me-1"
                                            id="subscriptionInvoiceSubmitBtn">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('clients.modals.create')
@endsection

@section('page-vendor-js')
    <script
        src="{{ asset('app-assets/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/forms/repeater/jquery.repeater.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/extensions/moment.min.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('custom-js')
    <script>
        $(document).ready(function() {

            if (route().current('invoices.subscription.create')) {
                clientIdChanged();
            }

            var invoice;
            @if (isset($invoice))
                invoice = @json($invoice)
            @endif

            function formatPrice(num) {
                try {
                    num = parseFloat(num) ?? num;
                    num = num?.toLocaleString(
                        'us', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }) ?? num;
                    // num = (Math.floor(Number(num) * 100) / 100).toFixed(2);
                } catch (error) {}
                return num;
            }
            $.validator.addMethod("validDate", function(value, element) {
                return this.optional(element) || moment(value, "DD/MM/YYYY").isValid();
            }, "Please enter a valid date in the format DD/MM/YYYY");

            $('#due_date').datepicker({
                format: 'dd-mm-yyyy',
                orientation: 'bottom'
            });

            $('#invoice_date').datepicker({
                format: 'dd-mm-yyyy',
                orientation: 'bottom',
                endDate: '0d'
            });

            $(document).on('select2:open', (e) => {
                const selectId = e.target.id
                $(".select2-search__field[aria-controls='select2-" + selectId + "-results']").each(function(
                    key,
                    value,
                ) {
                    value.focus();
                })
            })

            $('.select2').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                if ($this.attr('name') == 'client_id') {
                    $this.select2({
                        // dropdownAutoWidth: true,
                        placeholder: 'Select Customer',
                        width: '100%',
                        dropdownParent: $('body'),
                        containerCssClass: 'select-sm',
                    });
                } else if ($this.attr('name') == 'user_id') {
                    $this.select2({
                        placeholder: {
                            id: "-1",
                            text: 'Select Sales Person',
                            selected: 'selected'
                        },
                        containerCssClass: 'select-sm',
                        width: '100%',
                        dropdownParent: $('body')
                    });
                } else if ($this.attr('name') == 'company_detail_id') {
                    $this.select2({
                        placeholder: {
                            id: "-1",
                            text: 'Select Company',
                            selected: 'selected'
                        },
                        containerCssClass: 'select-sm',
                        width: '100%',
                        dropdownParent: $('body')
                    });
                } else if ($this.attr('name') == 'country_id') {
                    $this.select2({
                        containerCssClass: 'select-sm',
                        width: '100%',
                        dropdownParent: $('#addClientModal')
                    });
                } else {
                    $this.select2({
                        // dropdownAutoWidth: true,
                        containerCssClass: 'select-sm',
                        width: '100%',
                        dropdownParent: $('body')
                    });
                }
            });

            var $repeater = $('.repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    vat_amount: '0.00',
                    price_amount: '0.00',
                    total_amount: '0.00'
                },
                show: function() {
                    let len = $('[data-repeater-list=invoice_items] [data-repeater-item]').length;
                    $(this).slideDown();
                    $('[data-repeater-list=invoice_items] [data-repeater-item]').each(function(index,
                        el) {
                        $(el).find('[data-repeater-delete]').show();
                    });
                    $('.select2-container').remove();
                    $('.select2').each(function() {
                        let $this = $(this);
                        $this.wrap('<div class="position-relative"></div>');
                        if ($this.attr('name') == 'client_id') {
                            $this.select2({
                                // dropdownAutoWidth: true,
                                containerCssClass: 'select-sm',
                                placeholder: 'Select Customer',
                                width: '100%',
                                dropdownParent: $('body')
                            });
                        } else {
                            $this.select2({
                                // dropdownAutoWidth: true,
                                containerCssClass: 'select-sm',
                                width: '100%',
                                dropdownParent: $('body')
                            });
                        }
                    });
                    $('.select2-container').css('width', '100%');
                    // toastr config
                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }
                },
                hide: function(deleteElement) {
                    Swal.fire({
                        title: 'Are you sure you want to remove this row?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, remove it!',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-danger ms-1'
                        },
                        buttonsStyling: false
                    }).then(function(result) {
                        if (result.value) {
                            let len = $(
                                    '[data-repeater-list=invoice_items] [data-repeater-item]')
                                .length;
                            if (len == 1) {
                                return alert("You can't delete the last invoice item");
                            }

                            $(this).slideUp(function() {
                                deleteElement();
                                if (len === 2) {
                                    $('[data-repeater-item]').each(function(index, el) {
                                        $(el).find('[data-repeater-delete]')
                                            .hide();
                                    });
                                }

                                countTotals();
                            });
                        }
                    });
                    // toastr config
                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }
                },
                ready: function(setIndexes) {
                    // $dragAndDrop.on('drop', setIndexes);
                },
            });

            $('body').on('change input',
                //'#currency_id, #discount, #invoiceItemsTable tbody input, #invoiceItemsTable tbody select, #invoiceItemsTable tbody textarea',
                '#currency_id, #invoiceItemsTable tbody input, #invoiceItemsTable tbody select, #invoiceItemsTable tbody textarea',
                function(e) {
                    countTotals();
                });

            function countTotals() {
                let subTotal = 0,
                    grandTotal = 0,
                    vatTotal = 0;
                $('[data-repeater-item]').each(function(i, el) {
                    let price = $(el).find(`[name="invoice_items[${i}][price]"]`).val() * 1.000;
                    let tax_type = $(el).find(`[name="invoice_items[${i}][tax_type]"]`).val();
                    let tax_rate = 0;
                    subTotal += price;
                    if (tax_type === 'vat_20') {
                        tax_rate = 20;
                    }
                    let tax_amount = (price * tax_rate) / 100;
                    vatTotal += tax_amount;
                    let taxedPrice = price + tax_amount;
                    $(el).find(`[name='invoice_items[${i}][total_amount]']`).val(formatPrice(taxedPrice))
                    $(el).find(`[name='invoice_items[${i}][vat_amount]']`).val(formatPrice(tax_amount))
                    $(el).find(`[name='invoice_items[${i}][price_amount]']`).val(formatPrice(price))
                    grandTotal += taxedPrice;
                });
                var currency_symbol = $('#currency_id').find(':selected').data('symbol');
                /*var discount = $('#discount').val() * 1.000;
                grandTotal = grandTotal - discount;*/

                //$('#discount_total').text(currency_symbol + formatPrice(discount));
                $('#sub_total').text(currency_symbol + formatPrice(subTotal));
                $('#grand_total').text(currency_symbol + formatPrice(grandTotal));
                $('#tax_total').text(currency_symbol + formatPrice(vatTotal));

                return {
                    /*discount,*/
                    subTotal,
                    grandTotal,
                    vatTotal
                };
            }

            countTotals();

            const subscription_statuses = @json(App\Enums\InvoiceSubscriptionStatus::all());
            var subscription_status_value = $('#subscription_status').val();

            $(document).on('change', '#subscription_status', function(e) {
                if ($(this).val() == subscription_statuses['CANCELLED']) {
                    Swal.fire({
                        title: "Are you sure you want to cancel this invoice subscription?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-danger ms-1'
                        },
                        buttonsStyling: false
                    }).then(result => {
                        if (result.value) {
                            subscription_status_value = $(this).val();
                        } else {
                            $('#subscription_status').val(subscription_status_value).trigger(
                                'change');
                        }
                    });
                } else {
                    subscription_status_value = $(this).val();
                }
            });

            var invoiceForm = $('#invoice_form').validate({
                ignore: [],
                rules: {
                    invoice_number: {
                        required: true
                    },
                    currency_id: {
                        required: true
                    },
                    invoice_date: {
                        required: true,
                        validDate: true,
                    },
                    due_date: {
                        required: true,
                        validDate: true
                    },
                    client_id: {
                        required: true
                    },
                    company_detail_id: {
                        required: true
                    },
                    /*discount: {
                        required: false,
                        number: true,
                        min: 0.00,
                        max: function() {
                            let {
                                subTotal,
                                vatTotal
                            } = countTotals();
                            return subTotal + vatTotal;
                        }
                    },*/
                    subscription_type: {
                        required: true,
                    },
                    client_name: {
                        required: false,
                    },
                    subscription_status: {
                        required: true,
                    },
                    // payment_status: {
                    //     required: true,
                    //     inArray: ['paid', 'partially_paid', 'unpaid']
                    // },
                    'invoice_items[][price]': {
                        required: true,
                        number: true,
                        min: 0.01
                    },
                    'invoice_items[][description]': {
                        required: true
                    },
                    'invoice_items[][tax_type]': {
                        required: false
                    }
                },
                messages: {
                    invoice_number: {
                        required: "Please enter invoice number"
                    },
                    currency_id: {
                        required: "Please select a currency"
                    },
                    invoice_date: {
                        required: "Please select a valid invoice date",
                        validDate: "Please select a valid invoice date",
                    },
                    due_date: {
                        required: "Please select a valid invoice date",
                        validDate: "Please select a valid invoice date",
                    },
                    client_id: {
                        required: "Please select a client"
                    },
                    subscription_type: {
                        required: "Please select a subscription type",
                    },
                    subscription_status: {
                        required: "Please select subscription type",
                    },
                    company_detail_id: {
                        required: "Please select a company",
                    },
                    /*discount: {
                        number: "Please enter a number",
                        max: "Please enter value less than grand total",
                        min: "Please enter value greater than or equal to 0.00"
                    },*/
                    // payment_status: {
                    //     required: "Please select a payment status",
                    //     inArray: "Please select from: Paid / Partially paid / Unpaid"
                    // },
                    'invoice_items[][price]': {
                        required: "Please enter price",
                        number: "Please enter a number",
                        min: "Please enter number greater than {0}"
                    },
                    'invoice_items[][description]': {
                        required: "Please enter item description"
                    },
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    let subscription_status = $('#subscription_status').val();
                    const confirmationHandler = function(result) {
                        if (result.value) {
                            $('#subscriptionInvoiceSubmitBtn').prop('disabled', true);
                            $(form).block({
                                message: '<div class="spinner-border text-warning" role="status"></div>',
                                css: {
                                    backgroundColor: 'transparent',
                                    border: '0'
                                },
                                overlayCSS: {
                                    backgroundColor: '#fff',
                                    opacity: 0.8
                                }
                            });
                            let url = route('invoices.subscription.store');
                            let method = 'POST';
                            if (route().current('invoices.subscription.edit')) {
                                url = route('invoices.subscription.update', invoice
                                    ?.encrypted_id);
                                method = 'PUT';
                            }

                            $.ajax({
                                url,
                                method,
                                data: $(form).serialize(),
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr(
                                        'content')
                                },
                                success: function(response) {
                                    $(form).unblock();
                                    $('#subscriptionInvoiceSubmitBtn').prop(
                                        'disabled', false);
                                    if (response.errors) {
                                        $(form).validate().showErrors(response
                                            .errors);
                                        return;
                                    }
                                    window.location.href = route().current(
                                            'invoices.subscription.edit') ?
                                        "{{ url()->previous() }}" :
                                        route('invoices.index');
                                },
                                error: function(xhr, status, error) {
                                    $(form).unblock();
                                    $('#subscriptionInvoiceSubmitBtn').prop(
                                        'disabled', false);
                                    if (xhr.status == 422) {
                                        let errors = xhr.responseJSON?.errors;
                                        let invoiceItemCount = $(
                                            '[data-repeater-item]').length;
                                        for (let i = 0; i <
                                            invoiceItemCount; i++) {
                                            errors[
                                                    `invoice_items[${i}][description]`
                                                ] =
                                                errors[
                                                    `invoice_items.${i}.description`
                                                ]?.[0];
                                            errors[
                                                    `invoice_items[${i}][price]`] =
                                                errors[
                                                    `invoice_items.${i}.price`]
                                                ?.[
                                                    0
                                                ];
                                            errors[
                                                    `invoice_items[${i}][tax_type]`
                                                ] =
                                                errors[
                                                    `invoice_items.${i}.tax_type`
                                                ]
                                                ?.[0];
                                            errors[`invoice_items[${i}][id]`] =
                                                errors[
                                                    `invoice_items.${i}.id`]?.[
                                                    0
                                                ];

                                            delete errors[
                                                `invoice_items.${i}.description`
                                            ];
                                            delete errors[
                                                `invoice_items.${i}.price`];
                                            delete errors[
                                                `invoice_items.${i}.tax_type`
                                            ];
                                            delete errors[
                                                `invoice_items.${i}.id`];
                                        }
                                        $(form).validate().showErrors(errors);
                                    } else {
                                        Swal.fire({
                                            title: 'An error occurred',
                                            text: error,
                                            icon: 'error',
                                        });
                                    }
                                }
                            });
                        }
                    };
                    if (subscription_status == subscription_statuses['CANCELLED']) {
                        confirmationHandler({
                            value: true
                        });
                    } else {
                        Swal.fire({
                            title: 'Are you sure you want to ' +
                                (
                                    route().current('invoices.subscription.edit') ?
                                    'update this ' :
                                    'create a '
                                ) +
                                (
                                    $('[name=subscription_type]').val() ==
                                    "{{ config('custom.subscription_types.yearly', '1') }}" ?
                                    'yearly' :
                                    'monthly'
                                ) +
                                ' subscription invoice?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-outline-danger ms-1'
                            },
                            buttonsStyling: false
                        }).then(confirmationHandler);
                    }
                },
                errorPlacement: function(error, element) {
                    if (element.attr("name") == "currency_id") {
                        error.insertAfter($('#prj_budget').parent());
                    } else if (element.attr("name") == "prj_budget") {
                        error.insertAfter($('#prj_budget').parent());
                    } else if (element.hasClass('select2') && element.next('.select2-container')
                        .length) {
                        error.insertAfter(element.next('.select2-container'));
                    } /*else if (element.attr('name') === 'discount') {
                        if (!$('#discount-popup').is(':visible')) {
                            $('#discount-popup').show();
                        }
                        error.insertAfter(element);
                    }*/ else {
                        error.insertAfter(element);
                    }
                }
            });

            let client_id_select = $('#client_id');
            client_id_select.on('select2:open', function() {
                if (!$(document).find('.add-new-client').length) {
                    $(document)
                        .find('.select2-results__options')
                        .before(
                            '<div class="add-new-client btn btn-flat-success cursor-pointer rounded-0 text-start mb-50 p-50 w-100" data-bs-toggle="modal" data-bs-target="#addClientModal">' +
                            feather.icons['plus'].toSvg({
                                class: 'font-medium-1 me-50'
                            }) +
                            '<span class="align-middle">Add New Customer</span></div>'
                        );
                }
            });

            $(document).on('click', '.add-new-client', function() {
                client_id_select.select2('close').trigger('select2:closing');
            });

            client_id_select.on('change', function(e) {
                clientIdChanged($(this));
            });

            /* Default Client id selected function call to show note */
            clientIdChanged();

            function clientIdChanged(clientSelectEl = $('#client_id')) {
                let selectionOption = clientSelectEl.find(':selected')
                if (selectionOption) {
                    let client_id = selectionOption.data('encrypted-id');
                    if (client_id) {
                        selectPreferredSalesPerson(client_id);
                    }
                }
            }

            function selectPreferredSalesPerson(clientId) {
                $.ajax({
                    url: route('clients.preferred-sales-person', clientId),
                    success: function(response) {
                        $("#invoice_notes").html('');
                        if (response.sales_person_id) {
                            $('#user_id').val(response.sales_person_id ?? '').trigger(
                                'change');
                        }
                        if(response.notes){
                            $.each(response.notes,function(index,value){
                                $("#invoice_notes").append(
                                    '<div class="col-12">'+
                                    '<h6 class="alert-heading mb-0">'+ value.note +'</h6>'+
                                    '</div>');
                            });
                        }
                    },
                });
            }

            // Phone Number
            $(document).on('input', '#phone', function() {
                let inputVal = this.value;
                let regex = /^(\+)?([0-9]+(\s)?)+$/;
                if (!regex.test(inputVal)) {
                    let cleanedVal = inputVal.replace(/[^0-9+\s]/g, '');
                    if (cleanedVal.startsWith('+')) {
                        cleanedVal = '+' + cleanedVal.replace(/[^\d\s]/g, '');
                    } else {
                        cleanedVal = cleanedVal.replace(/[^\d\s]/g, '');
                    }
                    this.value = cleanedVal.trim();
                }
            });

            // focus searchbox when select2 dropdown opened
            $(document).on('select2:open', (e) => {
                const selectId = e.target.id
                $(".select2-search__field[aria-controls='select2-" + selectId + "-results']").each(function(
                    key,
                    value,
                ) {
                    value.focus();
                })
            })
            $('#basic-icon-default-email').select2({
                tags: true,
                dropdownParent: $('#addClientForm').get(0),
            });
            const addClientForm = $('#addClientForm');

            var rules = {
                'name': {
                    required: true
                },
                'email[]': {
                    required: true,
                    validEmails: true,
                },
                'phone': {
                    required: false
                },
                'address_line_1': {
                    required: false
                },
                'address_line_2': {
                    required: false
                },
                'city': {
                    required: false
                },
                'country': {
                    required: false
                },
                'plant_a_tree': {
                    required: false
                },
                'zip_code': {
                    required: false,
                }
            };

            var messages = {
                'name': {
                    required: "Please enter name"
                },
                'email[]': {
                    required: "Please enter email",
                    validEmails: "Please enter a valid email",
                },
            };
            // Form Validation
            if (addClientForm.length) {
                addClientForm.validate({
                    rules,
                    messages,
                    submitHandler: function(form, event) {
                        event.preventDefault();
                        let handleSubmit = (form) => {
                            $('#addClientSubmitBtn').prop('disabled', true);
                            $('#addClientModal > .modal-dialog').block({
                                message: '<div class="spinner-border text-warning" role="status"></div>',
                                css: {
                                    backgroundColor: 'transparent',
                                    border: '0'
                                },
                                overlayCSS: {
                                    backgroundColor: '#fff',
                                    opacity: 0.8
                                }
                            });
                            var myFormdata = new FormData(form);
                            $.ajax({
                                url: form.action,
                                method: 'POST',
                                data: myFormdata,
                                contentType: false,
                                processData: false,
                                success: function(response) {
                                    if (response.errors) {
                                        $(form).validate().showErrors(response.errors);
                                    } else {
                                        $('#addClientModal').modal('hide');
                                        toastr.success(null,
                                            "Customer added successfully!");
                                        form.reset();
                                    }
                                    if (response.client) {
                                        let option = new Option(response.client.name,
                                            response
                                            .client.id);
                                        client_id_select.append(option).val(response
                                                .client.id)
                                            .trigger('change');
                                    }
                                    $('#addClientModal > .modal-dialog').unblock();
                                    $('#addClientSubmitBtn').prop('disabled', false);
                                },
                                error: function(xhr, status, error) {
                                    $('#addClientModal > .modal-dialog').unblock();
                                    $('#addClientSubmitBtn').prop('disabled', false);
                                    if (xhr.status == 422) {
                                        $(form).validate().showErrors(JSON.parse(xhr
                                            .responseText).errors);
                                    } else {
                                        Swal.fire({
                                            title: 'An error occurred',
                                            text: error,
                                            icon: 'error',
                                        });
                                    }
                                }
                            });
                        }
                        if (!$('#plant_a_tree').prop('checked')) {
                            handleSubmit(form);
                            return;
                        }
                        Swal.fire({
                            title: 'Are you sure you want to plant a tree?',
                            text: "You can revert this by editing this user before sending him invoice mail!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-outline-danger ms-1'
                            },
                            buttonsStyling: false
                        }).then(function(result) {
                            if (!result.value) {
                                return;
                            }
                            handleSubmit(form);
                        });
                    }
                });
            }

            /*$('#discount-label').on('click', function(e) {
                var popup = $('#discount-popup');
                if (popup.is(':visible')) {
                    popup.hide();
                } else {
                    popup.show();
                }
            });*/

            /*$(document).on('click', function(event) {
                var popup = $('#discount-popup');
                if (!$(event.target).closest('#discount-popup, #discount-label').length) {
                    popup.hide();
                }
            });*/
        });
    </script>
@endsection
