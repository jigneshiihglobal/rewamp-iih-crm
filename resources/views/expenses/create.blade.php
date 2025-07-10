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
                        {{-- @dd($expense) --}}
                        <form class="form" id="createExpenseForm"
                            action="{{ Route::is('expenses.edit') ? route('expenses.update', optional($expense)->encrypted_id) : route('expenses.store') }}"
                            method="POST">
                            @csrf
                            @if (Route::is('expenses.edit'))
                                @method('PUT')
                            @endif
                            <div class="row">
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="client_id">Customer <span
                                                class="text-danger">*</span></label>
                                        <select id="client_id" class="select2-size-sm form-select select2" name="client_id">
                                            <option value="">Select a customer</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}"
                                                    {{ $client->id == old('client_id', optional($expense)->client_id) ? 'selected' : '' }}
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
                                        <label class="form-label" for="project_name">Project Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="project_name" class="form-control form-control-sm"
                                            placeholder="Project Name" name="project_name" autocomplete="off"
                                            value="{{ old('project_name', optional($expense)->project_name) }}" />
                                        @error('project_name')
                                            <span id="project_name-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label for="expense_date" class="form-label">Expense Date <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="expense_date" class="form-control form-control-sm"
                                            placeholder="Expense Date" name="expense_date"
                                            value="{{ old('expense_date', optional($expense)->expense_date ? $expense->expense_date->format('d/m/Y') : date('d/m/Y')) }}"
                                            autocomplete="off" />
                                        @error('expense_date')
                                            <span id="expense_date-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="expense_type_id">Expense Type <span
                                                class="text-danger">*</span></label>
                                        <select id="expense_type_id" class="select2-size-sm form-select select2"
                                            name="expense_type_id">
                                            @foreach ($expense_types as $expense_type)
                                                <option value="{{ $expense_type->id }}"
                                                    {{ $expense_type->id == old('expense_type_id', optional(optional($expense)->expense_sub_type)->expense_type_id) ? 'selected' : '' }}
                                                    data-encrypted-id="{{ $expense_type->encrypted_id }}">
                                                    {{ $expense_type->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('expense_type_id')
                                            <span id="expense_type_id-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="expense_sub_type_id">Expense Sub Type <span
                                                class="text-danger">*</span></label>
                                        <select id="expense_sub_type_id" class="select2-size-sm form-select select2"
                                            name="expense_sub_type_id">
                                            @foreach ($expense_sub_types as $expense_sub_type)
                                                <option value="{{ $expense_sub_type->id }}"
                                                    {{ $expense_sub_type->id == old('expense_sub_type_id', optional($expense)->expense_sub_type_id) ? 'selected' : '' }}
                                                    data-encrypted-id="{{ $expense_sub_type->encrypted_id }}">
                                                    {{ $expense_sub_type->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('expense_sub_type_id')
                                            <span id="expense_sub_type_id-error" class="error">{{ $message }}</span>
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
                                        <label class="form-label" for="type">Type <span
                                                class="text-danger">*</span></label>
                                        <select id="type" class="select2-size-sm form-select select2"
                                            name="type">
                                            <option value="{{ App\Enums\ExpenseType::ONE_OFF }}"
                                                {{ old('type', optional($expense)->type) == App\Enums\ExpenseType::ONE_OFF ? 'selected' : '' }}>
                                                One-off</option>
                                            <option value="{{ App\Enums\ExpenseType::RECURRING }}"
                                                {{ old('type', optional($expense)->type) == App\Enums\ExpenseType::RECURRING ? 'selected' : '' }}>
                                                Recurring</option>
                                        </select>
                                        @error('type')
                                            <span id="type-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-12"
                                    style="{{ old('type', optional($expense)->type) != App\Enums\ExpenseType::RECURRING ? 'display: none;' : '' }}"
                                    id="frequencyWrapper">
                                    <div class="mb-1">
                                        <label class="form-label" for="frequency">Frequency <span
                                                class="text-danger">*</span></label>
                                        <select id="frequency" class="select2-size-sm form-select select2"
                                            name="frequency">
                                            <option value="{{ App\Enums\ExpenseFrequency::MONTHLY }}"
                                                {{ old('type', optional($expense)->frequency) == App\Enums\ExpenseFrequency::MONTHLY ? 'selected' : '' }}>
                                                Monthly</option>
                                            <option value="{{ App\Enums\ExpenseFrequency::YEARLY }}"
                                                {{ old('type', optional($expense)->frequency) == App\Enums\ExpenseFrequency::YEARLY ? 'selected' : '' }}>
                                                Yearly</option>
                                        </select>
                                        @error('frequency')
                                            <span id="frequency-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 align-items-start">
                                    <div class="float-end">
                                        <a href="{{ route('expenses.index') }}"
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
            const $frequencyWrapper = $('#frequencyWrapper');
            const $expenseTypeSelect = $('#expense_type_id');
            const $expenseSubTypeSelect = $('#expense_sub_type_id');
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

            $typeSelect.on('change', function(e) {
                if ($(this).val() == expenseTypes?.['RECURRING'])
                    $frequencyWrapper.show();
                else
                    $frequencyWrapper.hide();

            });

            $('#expense_date').flatpickr({
                dateFormat: 'd/m/Y',
            });

            $expenseTypeSelect.on('change', function(e) {
                let selectedExpenseType = $(this).val();
                if (selectedExpenseType) {
                    $.ajax({
                        url: route('expense_types.expense_sub_types.index', selectedExpenseType),
                        success: function(data) {

                            $expenseSubTypeSelect
                                .empty()
                                .select2({
                                    data: data?.expense_sub_types ?
                                        data.expense_sub_types
                                        .map(expenseSubType => ({
                                            id: expenseSubType?.id,
                                            text: expenseSubType?.title
                                        })) : [],
                                    dropdownAutoWidth: true,
                                    width: '100%',
                                    dropdownParent: $expenseSubTypeSelect.parent(),
                                    containerCssClass: 'select-sm',
                                });
                        },
                        error: function(xhr, status, err) {
                            toastr.error(null, err);
                        }
                    });
                } else {
                    $expenseSubTypeSelect.empty().trigger('change');
                }
            });

            var validator = $createExpenseForm.validate({
                rules: {
                    client_id: {
                        required: true,
                    },
                    project_name: {
                        required: true,
                        maxlength: 100,
                    },
                    expense_date: {
                        required: true,
                        validDate: true,
                    },
                    expense_type_id: {
                        required: true,
                    },
                    expense_sub_type_id: {
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
                    type: {
                        required: true,
                        validType: true,
                    },
                    frequency: {
                        required: function(element) {
                            return $("#type").val() == '1';
                        },
                        validType: true,
                    },
                },
                messages: {
                    client_id: {
                        required: "Please select client",
                    },
                    project_name: {
                        required: "Please enter project name",
                        maxlength: 'Please enter shorter name',
                    },
                    expense_date: {
                        required: "Please select expense date",
                        validDate: "Please select valid expense date",
                    },
                    expense_type_id: {
                        required: "Please select expense type",
                    },
                    expense_sub_type_id: {
                        required: "Please select expense sub type",
                    },
                    amount: {
                        required: "Please enter amount",
                        number: "Please enter valid amount",
                        range: "Please enter value between {0} and {1}",
                    },
                    currency_id: {
                        required: "Please select currency",
                    },
                    type: {
                        required: "Please select type",
                        validType: "Please select a valid type",
                    },
                    frequency: {
                        required: "Please select frequency",
                        validType: "Please select a valid frequency",
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
                                        "Expense " +
                                        (
                                            route().current('expense.edit') ?
                                            "updated" :
                                            "added"
                                        ) +
                                        " successfully!");
                                    form.reset();
                                    validator.resetForm();
                                }
                                $createExpenseForm.unblock();
                                $addExpenseSubmitBtn.prop('disabled', false);

                                window.location.href = route('expenses.index');
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
                        title: 'Are you sure you want to add a recurring expense?',
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
