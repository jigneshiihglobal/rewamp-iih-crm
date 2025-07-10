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
                        <h4 class="card-title"> {{ $view_config['title'] ?? 'Create' }} Invoice</h4>
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
                                        <label class="form-label d-block" for="invoice_type">Invoice Type <span class="text-danger">*</span></label>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="invoice_type" id="invoice_type_one_off" value="0"
                                                class="form-check-input" {{ old('invoice_type', '0') == '0' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="invoice_type_one_off">One-off</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="invoice_type" id="invoice_type_subscription" value="1"
                                                class="form-check-input" {{ old('invoice_type') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="invoice_type_subscription">Subscription</label>
                                        </div>
                                        @error('invoice_type')
                                            <span id="invoice_type-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="client_id">Customer <span
                                                class="text-danger">*</span></label>
                                        <select id="client_id" class="select2-size-sm form-select select2" name="client_id">
                                            <option value="">Select a customer</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}"
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
                                                   value="{{ date('d-m-Y') }}"
                                                   autocomplete="off" readonly/>
                                        @else
                                            <input type="text" id="invoice_date" class="form-control form-control-sm"
                                                   placeholder="Invoice Date" name="invoice_date"
                                                   value="{{ old('invoice_date',date('d-m-Y')) }}"
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
                                            <input type="text" id="due_date" class="form-control form-control-sm"
                                                   placeholder="Due Date" name="due_date" autocomplete="off"
                                                   value="{{ old('due_date', date('d-m-Y', strtotime('+5 days'))) }}"/>
                                        @error('due_date')
                                            <span id="due_date-error" class="error">{{ $message }}</span>
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
                                                    {{ $currency->id == 2 ? 'selected' : '' }}
                                                    data-symbol="{{ $currency->symbol }}">
                                                    {{ $currency->symbol }} - {{ $currency->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('currency_id')
                                            <span id="currency_id-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12 d-none">
                                    <div class="mb-1 form-group">
                                        <label class="form-label" for="user_id">Sales Person</label>
                                        <select id="user_id" class="select2-size-sm form-select select2" name="user_id">
                                            <option value="" selected>Select Sales Person</option>
                                            @foreach ($sales_people as $sales_person)
                                                <option value="{{ $sales_person->id }}"
                                                    {{ $sales_person->id == Auth::user()->id ? 'selected' : '' }}>
                                                    {{ $sales_person->full_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <span id="user_id-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="client_name">Client's Name </label>
                                        <input type="text" id="client_name" class="form-control form-control-sm"
                                            name="client_name" autocomplete="off"
                                            value="" />
                                        @error('client_name')
                                            <span id="client_name-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12 d-none">
                                    <div class="mb-1 form-group">
                                        <label for="company_detail_id" class="form-label">Company <span
                                                class="text-danger">*</span></label></label>
                                        <select id="company_detail_id" class="select2-size-sm form-select select2"
                                            name="company_detail_id">
                                            <option value="" selected>Select Company</option>
                                            @foreach ($company_details as $company_detail)
                                                <option value="{{ $company_detail->id }}"
                                                    {{ $loop->first ? 'selected' : '' }}>
                                                    {{ $company_detail->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('company_detail_id')
                                            <span id="company_detail_id-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12" id="subscription_type_block" style="display: none;">
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
                                    </div>
                                </div>
                                <div class="col-12" id="invoiceItemsTable">
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
                                            <tbody data-repeater-list="user_invoice_items">
                                                @php $invoiceItemState = ''; @endphp
                                                <tr data-repeater-item>
                                                    <td class="align-top ps-0" style="width: 34%">
                                                        <textarea name="description" rows="1" class="form-control form-control-sm" required {{ $invoiceItemState }}></textarea>
                                                    </td>
                                                    <td class="align-top" style="width: 12%">
                                                        <input type="number"
                                                            class="form-control form-control-sm text-end amount_input"
                                                            name="price" required min="0.01" />
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
                                <div class="col-12 align-items-start pe-2 pe-sm-5">
                                    <div class="float-end">

                                        <a href="{{URL::previous()}}"
                                            class="btn btn-outline-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary me-1"
                                            id="oneOffInvoiceSubmitBtn">Submit</button>
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
        function toggleSubscriptionBlock() {
            const selectedType = $('input[name="invoice_type"]:checked').val();
            if (selectedType === '1') {
                $('#subscription_type_block').show();
            } else {
                $('#subscription_type_block').hide();
            }
        }
        $(document).ready(function() {
            /* Default Client id selected function call to show note */
            clientIdChanged();

             toggleSubscriptionBlock();

            // On change
            $('input[name="invoice_type"]').on('change', function () {
                toggleSubscriptionBlock();
            });
            
            if (route().current('sales_invoices.create')) {
                clientIdChanged();
            }

            function formatPrice(num) {
                try {
                    num = parseFloat(num) ?? num;
                    num = num?.toLocaleString(
                        'us', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }) ?? num;
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
            });


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
                    let len = $('[data-repeater-list=user_invoice_items] [data-repeater-item]').length;
                    $(this).slideDown();
                    $('[data-repeater-list=user_invoice_items] [data-repeater-item]').each(function(index,
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
                                    '[data-repeater-list=user_invoice_items] [data-repeater-item]')
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
                '#currency_id, #invoiceItemsTable tbody input, #invoiceItemsTable tbody select, #invoiceItemsTable tbody textarea',
                function(e) {
                    countTotals();
                });

            function countTotals() {
                let subTotal = 0,
                    grandTotal = 0,
                    vatTotal = 0;
                $('[data-repeater-item]').each(function(i, el) {
                    let price = $(el).find(`[name="user_invoice_items[${i}][price]"]`).val() * 1.000;
                    let tax_type = $(el).find(`[name="user_invoice_items[${i}][tax_type]"]`).val();
                    let tax_rate = 0;
                    subTotal += price;
                    if (tax_type === 'vat_20') {
                        tax_rate = 20;
                    }
                    let tax_amount = (price * tax_rate) / 100;
                    vatTotal += tax_amount;
                    let taxedPrice = price + tax_amount;
                    $(el).find(`[name='user_invoice_items[${i}][total_amount]']`).val(formatPrice(taxedPrice))
                    $(el).find(`[name='user_invoice_items[${i}][vat_amount]']`).val(formatPrice(tax_amount))
                    $(el).find(`[name='user_invoice_items[${i}][price_amount]']`).val(formatPrice(price))
                    grandTotal += taxedPrice;
                });
                var currency_symbol = $('#currency_id').find(':selected').data('symbol');

                $('#sub_total').text(currency_symbol + formatPrice(subTotal));
                $('#grand_total').text(currency_symbol + formatPrice(grandTotal));
                $('#tax_total').text(currency_symbol + formatPrice(vatTotal));

                return {
                    subTotal,
                    grandTotal,
                    vatTotal
                };
            }

            countTotals();
            var invoiceForm = $('#invoice_form').validate({
                ignore: [],
                rules: {
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
                    client_name: {
                        required: false,
                    },
                    'user_invoice_items[][price]': {
                        required: true,
                        number: true,
                        min: 0.01
                    },
                    'user_invoice_items[][description]': {
                        required: true
                    },
                    'user_invoice_items[][tax_type]': {
                        required: false
                    }
                },
                messages: {
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
                    company_detail_id: {
                        required: "Please select a company"
                    },
                    'user_invoice_items[][price]': {
                        required: "Please enter price",
                        number: "Please enter a number",
                        min: "Please enter number greater than {0}"
                    },
                    'user_invoice_items[][description]': {
                        required: "Please enter item description"
                    },
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#oneOffInvoiceSubmitBtn').prop('disabled', true);
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
                    let url = route('sales_invoices.store');
                    let method = 'POST';

                    $.ajax({
                        url,
                        method,
                        data: $(form).serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function(response) {
                            $(form).unblock();
                            $('#oneOffInvoiceSubmitBtn').prop('disabled', false);
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                                return;
                            }
                            window.location.href = route('sales_invoices.index');
                            toastr.success(null, "Mail sent successfully!");
                        },
                        error: function(xhr, status, error) {
                            $(form).unblock();
                            $('#oneOffInvoiceSubmitBtn').prop('disabled', false);
                            if (xhr.status == 422) {
                                let errors = xhr.responseJSON?.errors;
                                let invoiceItemCount = $('[data-repeater-item]').length;
                                for (let i = 0; i < invoiceItemCount; i++) {
                                    errors[`user_invoice_items[${i}][description]`] = errors[
                                        `user_invoice_items.${i}.description`]?.[0];
                                    errors[`user_invoice_items[${i}][price]`] = errors[
                                        `user_invoice_items.${i}.price`]?.[0];
                                    errors[`user_invoice_items[${i}][tax_type]`] = errors[
                                        `user_invoice_items.${i}.tax_type`]?.[0];
                                    errors[`user_invoice_items[${i}][id]`] = errors[
                                        `user_invoice_items.${i}.id`]?.[0];

                                    delete errors[`user_invoice_items.${i}.description`];
                                    delete errors[`user_invoice_items.${i}.price`];
                                    delete errors[`user_invoice_items.${i}.tax_type`];
                                    delete errors[`user_invoice_items.${i}.id`];
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
                },
                errorPlacement: function(error, element) {
                    if (element.attr("name") == "currency_id") {
                        error.insertAfter($('#prj_budget').parent());
                    } else if (element.attr("name") == "prj_budget") {
                        error.insertAfter($('#prj_budget').parent());
                    } else if (element.hasClass('select2') && element.next('.select2-container')
                        .length) {
                        error.insertAfter(element.next('.select2-container'));
                    } else {
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
        });
    </script>
@endsection
