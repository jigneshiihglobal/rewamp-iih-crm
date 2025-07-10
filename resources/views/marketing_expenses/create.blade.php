@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
@endsection

@section('page-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
@endsection

@section('content')
    <section>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-1">
                        <h4 class="card-title">{{isset($view_config['title']) ? $view_config['title'] : 'Create'}} Expense</h4>
                    </div>
                    <hr class="m-0">
                    <div class="card-body pt-1">
                        <form class="form" id="createExpenseForm"
                            action="{{ Route::is('marketing.expenses.edit') ? route('marketing.expenses.update', optional($expense)->encrypted_id) : route('marketing.expenses.store') }}"
                            method="POST">
                            @csrf
                            @if (Route::is('expenses.edit'))
                                @method('PUT')
                            @endif
                            <div class="row">
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label for="marketing_expense_date" class="form-label">Marketing Expense Date <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="marketing_expense_date" class="form-control form-control-sm"
                                            placeholder="Marketing Expense Date" name="marketing_expense_date"
                                            value="{{ old('marketing_expense_date', optional($expense)->marketing_expense_date ? $expense->marketing_expense_date->format('d/m/Y') : date('d/m/Y')) }}"
                                            autocomplete="off" />
                                        @error('marketing_expense_date')
                                            <span id="marketing_expense_date-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="marketing_expense_type_id">Expense Type <span
                                                class="text-danger">*</span></label>
                                        <select id="marketing_expense_type_id" class="select2-size-sm form-select select2 expense_type_select2"
                                            name="marketing_expense_type_id">
                                            @foreach ($expense_types as $expense_type)
                                                <option value="{{ $expense_type->id }}"
                                                    {{ $expense_type->id == old('marketing_expense_type_id',optional($expense)->marketing_expense_type_id) ? 'selected' : '' }}
                                                    data-encrypted-id="{{ $expense_type->encrypted_id }}">
                                                    {{ $expense_type->title}}</option>
                                            @endforeach
                                        </select>
                                        @error('marketing_expense_type_id')
                                            <span id="marketing_expense_type_id-error" class="error">{{ $message }}</span>
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
                                                        {{ $currency->id == old('currency_id', optional($expense)->currency_id ?? 2) ? 'selected' : '' }}
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
                                    <div class="mb-1">
                                        <label class="form-label" for="amount">Amount <span
                                                class="text-danger">*</span></label>
                                        <input type="number" id="amount" class="form-control form-control-sm"
                                            placeholder="Amount" name="amount" autocomplete="off"
                                            value="{{ old('amount', optional($expense)->amount) }}" />
                                        @error('amount')
                                            <span id="amount-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 align-items-start">
                                    <div class="float-end">
                                        <a href="{{ route('marketing.expenses.index') }}"
                                            class="btn btn-outline-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary"
                                            id="addExpenseSubmitBtn">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/extensions/moment.min.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('custom-js')
    <script>
        $(document).ready(function() {

            const expenseTypes = @json(App\Enums\ExpenseType::all());
            const $typeSelect = $('#type');
            const $createExpenseForm = $('form#createExpenseForm');
            const $addExpenseSubmitBtn = $('#addExpenseSubmitBtn');

            $.validator.addMethod("validDate", function(value, element) {
                return this.optional(element) || moment(value, "DD/MM/YYYY").isValid();
            }, "Please enter a valid date in the format DD/MM/YYYY");

            $.validator.addMethod("validType", function(value, element) {
                return ['0', '1', 0, 1].includes(value);
            });
            $(document).on('select2:open', (e) => {
                const selectId = e.target.id;
                $(".select2-search__field[aria-controls='select2-"+selectId+"-results']").each(function (key,value,){
                    value.focus();
                });
            });

            $('.select2').each(function() {
                let $this = $(this);
                $this.wrap('<div class="position-relative"></div>');

                let select2Config = {
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $this.parent(),
                    containerCssClass: 'select-sm',
                };

                $this.select2(select2Config);
            });

            $('.expense_type_select2').each(function() {
                let $this = $(this);
                $this.wrap('<div class="position-relative"></div>');

                let select2Config = {
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $this.parent(),
                    containerCssClass: 'select-sm',

                    placeholder: 'Please select expense type',
                    allowClear: true,
                    tags: true,
                    createTag: function(params) {
                        var term = $.trim(params.term);
                        if (term === '') {
                            return null;
                        }
                        return {
                            id: term,
                            text: term,
                            newTag: true
                        };
                    },
                    insertTag: function(data, tag) {
                        data.push(tag);
                    }
                };

                $this.select2(select2Config);
            });

            $typeSelect.on('change', function(e) {
                if ($(this).val() == expenseTypes?.['RECURRING'])
                    $frequencyWrapper.show();
                else
                    $frequencyWrapper.hide();

            });

            $('#marketing_expense_date').flatpickr({
                dateFormat: 'd/m/Y',
            });

            var validator = $createExpenseForm.validate({
                rules: {
                    marketing_expense_date: {
                        required: true,
                        validDate: true,
                    },
                    marketing_expense_type_id: {
                        required: true,
                    },
                    amount: {
                        required: true,
                        number: true,
                        range: [0.01, 999999],
                    },
                    currency_id: {
                        required: true,
                    },
                },
                messages: {
                    marketing_expense_date: {
                        required: "Please select expense date",
                        validDate: "Please select valid expense date",
                    },
                    marketing_expense_type_id: {
                        required: "Please select expense type",
                    },
                    amount: {
                        required: "Please enter amount",
                        number: "Please enter valid amount",
                        range: "Please enter value between {0} and {1}",
                    },
                    currency_id: {
                        required: "Please select currency",
                    },
                },
                submitHandler: function(form, event) {
                    event.preventDefault();

                    let handleSubmit = (form) => {
                        $addExpenseSubmitBtn.prop('disabled', true);
                        $createExpenseForm.block({
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
                                    validator.showErrors(response.errors);
                                } else {
                                    toastr.success(
                                        null,
                                        "Marketing Expense " +
                                        (
                                            route().current('marketing.expense.edit') ?
                                            "updated" :
                                            "added"
                                        ) +
                                        " successfully!");
                                    form.reset();
                                    validator.resetForm();
                                }
                                $createExpenseForm.unblock();
                                $addExpenseSubmitBtn.prop('disabled', false);

                                window.location.href = route('marketing.expenses.index');
                            },
                            error: function(xhr, status, error) {
                                $createExpenseForm.unblock();
                                $addExpenseSubmitBtn.prop('disabled', false);
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
                    if ($typeSelect.val() != '1') {
                        handleSubmit(form);
                        return;
                    }
                    Swal.fire({
                        title: 'Are you sure you want to add a recurring marketing expense?',
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
        });
    </script>
@endsection
