@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
@endsection

@section('page-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <style>
        input.form-control.form-control-sm.flatpickr.flatpickr-input {
            background-color: #fff;
        }
    </style>
@endsection

@section('content')
    <section>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-1">
                        <h4 class="card-title">Create Expenses</h4>
                    </div>
                    <hr class="m-0">
                    <div class="card-body p-75">
                        <form class="form repeater" id="createExpensesForm" action="{{ route('expenses.store-many') }}"
                            method="POST">
                            @csrf
                            @if (Route::is('expenses.edit'))
                                @method('PUT')
                            @endif
                            <div id="expenses">
                                <template id="expense_item">
                                    <div class="d-flex justify-content-between bg-light  p-25 pb-0 mb-50 border rounded expense_item"
                                        data-list-index="0">
                                        <div class="flex-grow-1">
                                            <div class="row mx-0">
                                                <div class="col-xxl-2 col-lg-3 col-md-4 col-sm-6 col-12 px-50">
                                                    <div class="mb-1">
                                                        <label class="form-label position-relative">Customer <span
                                                                class="text-danger">*</span>
                                                        </label>
                                                        <div class="position-relative">
                                                            <select class="select2-size-sm form-select select2"
                                                                name="client_id" data-rule-required="true"
                                                                data-name="client_id"
                                                                data-msg-required="Please select a customer">
                                                                <option value="">Select a customer</option>
                                                                @foreach ($clients as $client)
                                                                    <option value="{{ $client->id }}"
                                                                        data-encrypted-id="{{ $client->encrypted_id }}">
                                                                        {{ $client->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-lg-3 col-md-4 col-sm-6 col-12 px-50">
                                                    <div class="mb-1">
                                                        <label class="form-label">Project Name <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            placeholder="Project Name" name="project_name"
                                                            data-name="project_name" autocomplete="off"
                                                            data-rule-required="true"
                                                            data-msg-required="Please enter project name" />
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-12 px-50">
                                                    <div class="mb-1">
                                                        <label class="form-label">Expense Date <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm flatpickr"
                                                            placeholder="Expense Date" name="expense_date"
                                                            data-name="expense_date" value="{{ date('d/m/Y') }}"
                                                            autocomplete="off" data-rule-required="true"
                                                            data-msg-required="Please select expense date"
                                                            data-rule-validDate="true"
                                                            data-msg-validDate="Please select valid date in DD/MM/YYYY format" />
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 col-12 px-50">
                                                    <div class="mb-1">
                                                        <label class="form-label">Expense Type <span
                                                                class="text-danger">*</span></label>
                                                        <div class="position-relative"><select
                                                                class="select2-size-sm form-select select2"
                                                                name="expense_type_id" data-name="expense_type_id"
                                                                data-rule-required="true"
                                                                data-msg-required="Please select expense type">
                                                                @foreach ($expense_types as $expense_type)
                                                                    <option value="{{ $expense_type->id }}"
                                                                        data-encrypted-id="{{ $expense_type->encrypted_id }}">
                                                                        {{ $expense_type->title }}</option>
                                                                @endforeach
                                                            </select></div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-12 px-50">
                                                    <div class="mb-1">
                                                        <label class="form-label">Expense Sub Type
                                                            <span class="text-danger">*</span></label>
                                                        <div class="position-relative"><select
                                                                class="select2-size-sm form-select select2"
                                                                name="expense_sub_type_id" data-name="expense_sub_type_id"
                                                                data-rule-required="true"
                                                                data-msg-required="Please select expense sub type">
                                                                @foreach ($expense_sub_types as $expense_sub_type)
                                                                    <option value="{{ $expense_sub_type->id }}"
                                                                        data-encrypted-id="{{ $expense_sub_type->encrypted_id }}">
                                                                        {{ $expense_sub_type->title }}</option>
                                                                @endforeach
                                                            </select></div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-12 px-50">
                                                    <div class="mb-1">
                                                        <label class="form-label">Amount <span
                                                                class="text-danger">*</span></label>
                                                        <input type="number" class="form-control form-control-sm"
                                                            placeholder="Amount" name="amount" data-name="amount"
                                                            autocomplete="off" data-rule-required="true"
                                                            data-msg-required="Please enter amount" />
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-lg-2 col-md-3 col-sm-4 col-12 px-50">
                                                    <div class="mb-1">
                                                        <label class="form-label">Currency <span
                                                                class="text-danger">*</span></label>
                                                        <div class="position-relative"><select
                                                                class="select2-size-sm form-select select2"
                                                                name="currency_id" data-name="currency_id"
                                                                data-rule-required="true"
                                                                data-msg-required="Please select currency">
                                                                @foreach ($currencies as $currency)
                                                                    <option value="{{ $currency->id }}"
                                                                        {{ $currency->id == 2 ? 'selected' : '' }}
                                                                        data-symbol="{{ $currency->symbol }}">
                                                                        {{ $currency->symbol }} - {{ $currency->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-12 px-50">
                                                    <div class="mb-1">
                                                        <label class="form-label">Type <span
                                                                class="text-danger">*</span></label>
                                                        <div class="position-relative"><select
                                                                class="select2-size-sm form-select select2" name="type"
                                                                data-name="type" data-rule-required="true"
                                                                data-msg-required="Please select type">
                                                                <option value="{{ App\Enums\ExpenseType::ONE_OFF }}">
                                                                    One-off</option>
                                                                <option value="{{ App\Enums\ExpenseType::RECURRING }}">
                                                                    Recurring</option>
                                                            </select></div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-12 px-50 frequencyWrapper"
                                                    style="display: none;">
                                                    <div class="mb-1">
                                                        <label class="form-label">Frequency <span
                                                                class="text-danger">*</span></label>
                                                        <div class="position-relative"><select
                                                                class="select2-size-sm form-select select2"
                                                                name="frequency" data-name="frequency">
                                                                <option value="{{ App\Enums\ExpenseFrequency::MONTHLY }}">
                                                                    Monthly</option>
                                                                <option value="{{ App\Enums\ExpenseFrequency::YEARLY }}">
                                                                    Yearly</option>
                                                            </select></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-1">
                                            <button type="button"
                                                class="btn btn-icon btn-outline-danger btn-sm me-0 mt-0 deleteExpenseBtn"
                                                style="display: none;">
                                                <i data-feather="x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="row">
                                <div class="col-12 align-items-start">
                                    <div class="float-start">
                                        <button type="button" class="btn btn-outline-info btn-sm" id="createExpenseBtn">
                                            <i data-feather="plus-circle"></i>
                                            Add Expense
                                        </button>
                                    </div>
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

            $(document).on("select2:open", () => {
                document.querySelector(".select2-container--open .select2-search__field").focus()
            })

            $.validator.addMethod("validDate", function(value, element) {
                return this.optional(element) || moment(value, "DD/MM/YYYY").isValid();
            }, "Please enter a valid date in the format DD/MM/YYYY");

            const expenseTypes = @json(\App\Enums\ExpenseType::all());
            const $tpl = $('template');
            const $expensesList = $('#expenses');
            const $expenseItems = $('.expense_item')
            let expenseItemCount = 0
            let maxIndex = 0;

            const validator = $('#createExpensesForm').validate({
                submitHandler: function(form) {
                    let handleSubmit = (form) => {
                        $('#createExpenseBtn').prop('disabled', true);
                        $('#createExpensesForm').block({
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
                                    let errors = {};

                                    Object.keys(response?.errors).forEach(
                                        errKey => {
                                            let errKeyArr = errKey?.split('.');

                                            if (errKeyArr?.length) {
                                                let tempArr = errKeyArr.map((val,
                                                    i) => {


                                                    if (i == 0) {
                                                        return val;
                                                    }
                                                    if (i == 3 && (
                                                            errKeyArr[2] ===
                                                            'to' ||
                                                            errKeyArr[2] ===
                                                            'bcc')) {
                                                        return `[]`;
                                                    }
                                                    return `[${val}]`;
                                                });
                                                errors[tempArr.join('')] = response
                                                    .errors?.[errKey] ?? '';
                                            }
                                        });


                                    $(form).validate().showErrors(errors);
                                } else {
                                    toastr.success(
                                        null,
                                        "Expenses added successfully!");
                                    form.reset();
                                    validator.resetForm();
                                }
                                $('#createExpensesForm').unblock();
                                $('#createExpenseBtn').prop('disabled', false);

                                window.location.href = route('expenses.index');
                            },
                            error: function(xhr, status, error) {
                                $('#createExpensesForm').unblock();
                                $('#createExpenseBtn').prop('disabled', false);
                                if (xhr.status == 422) {
                                    let errors = {};
                                    Object.keys(xhr.responseJSON?.errors).forEach(
                                        errKey => {
                                            let errKeyArr = errKey?.split('.');
                                            if (errKeyArr?.length) {
                                                let tempArr = errKeyArr.map((val,
                                                    i) => {
                                                    if (i == 0) {
                                                        return val;
                                                    }
                                                    if (i == 3 && (
                                                            errKeyArr[2] ===
                                                            'to' ||
                                                            errKeyArr[2] ===
                                                            'bcc')) {
                                                        return `[]`;
                                                    }
                                                    return `[${val}]`;
                                                });
                                                errors[tempArr.join('')] = xhr
                                                    .responseJSON
                                                    .errors?.[errKey] ?? '';
                                            }
                                        });
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
                    handleSubmit(form);
                },
                errorPlacement: function(error, element) {
                    if (element.hasClass('select2')) {
                        error.appendTo(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            createExpense();

            function setDefaults($expenseItem, defaults = {}) {
                $newItem.find("[name=client_id]").val(defaults?.client_id);
                $newItem.find("[name=project_name]").val(defaults?.project_name);
                $newItem.find("[name=expense_date]").val(defaults?.expense_date);
                $newItem.find("[name=expense_type_id]").val(defaults?.expense_type_id);
                $newItem.find("[name=expense_sub_type_id]").val(defaults?.expense_sub_type_id);
                $newItem.find("[name=amount]").val(defaults?.amount);
                $newItem.find("[name=curency_id]").val(defaults?.curency_id);
                $newItem.find("[name=type]").val(defaults?.type);
                $newItem.find("[name=frequency]").val(defaults?.frequency);
            }

            function createExpense(defaults = {}) {

                let $newItem = $tpl.contents().filter(function() {
                    return this.nodeType === Node.ELEMENT_NODE || (this.nodeType === Node.TEXT_NODE && $
                        .trim(this.nodeValue) !== '');
                }).clone();
                $newItem.hide().appendTo($expensesList)
                $newItem.attr('data-list-index', maxIndex);
                if (expenseItemCount > 0) {
                    $('.deleteExpenseBtn').show();
                }
                $newItem.slideDown(function() {
                    expenseItemCount++;
                    maxIndex++;
                });

                renameInputs();

                $newItem.on('click', ".deleteExpenseBtn", removeBtnClickHandler);
                $newItem.on('change', `[name="expenses[${maxIndex}][type]"]`, typeChangeHandler);
                $newItem.on('change', `[name="expenses[${maxIndex}][expense_type_id]"]`, expenseTypeChangeHandler)

                initSelect2($newItem);
                initFlatpickr($newItem);
                copyPreviousData($newItem);

                if (feather) {
                    feather.replace({
                        height: 14,
                        width: 14,
                    });
                }
            }

            function renameInputs() {
                $('.expense_item').each(function(index) {
                    let $item = $(this)
                    let itemIndex = $item.data('list-index')
                    $item.find('[data-name]').each(function(inputIndex) {
                        let $anyInput = $(this);
                        let name = $anyInput.data('name');
                        $anyInput.attr('name', `expenses[${itemIndex}][${name}]`)
                    })
                })
            }

            function initSelect2($parent) {
                $parent.find('select.select2').each(function(select2Index) {
                    let $select = $(this);
                    let config = {
                        width: "100%",
                        dropdownAutoWidth: true,
                        dropdownParent: $select.parent(),
                        containerCssClass: 'select-sm'
                    };
                    $select.select2(config);
                });
            }

            function initFlatpickr($parent) {
                $parent.find('input.flatpickr').each(function(fpI) {
                    $(this).flatpickr({
                        dateFormat: 'd/m/Y'
                    })
                })
            }

            function destroySelect2($parent) {
                $parent.find('select.select2').each(function(select2Index) {
                    $(this).select2('destroy')
                });
            }

            function destroyFlatpickr($parent) {
                $parent.get(0)?.querySelector('input.flatpickr')?._flatpickr?.destroy();
            }

            function removeBtnClickHandler(e) {
                Swal.fire({
                    title: 'Are you sure you want to delete this row?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {

                        let $btn = $(e.target);
                        let $item = $btn.closest('.expense_item');
                        let itemIndex = $item.data('list-index');

                        $item.off(
                            'click',
                            ".deleteExpenseBtn",
                            removeBtnClickHandler
                        );

                        $item.off(
                            'change',
                            `[name="expenses[${itemIndex}][type]"]`,
                            typeChangeHandler
                        );

                        $item.on(
                            'change',
                            `[name="expenses[${maxIndex}][expense_type_id]"]`,
                            expenseTypeChangeHandler
                        )

                        destroySelect2($item)
                        destroyFlatpickr($item)

                        $item.slideUp(function() {
                            if (expenseItemCount === 2) {
                                $('.deleteExpenseBtn').hide();
                            }
                            $(this).remove();
                            expenseItemCount--
                        });
                    }
                })

            }

            function typeChangeHandler(e) {
                let $item = $(this).closest('.expense_item')
                let $frequencyWrapper = $item.find('.frequencyWrapper')
                if ($(this).val() === expenseTypes?.RECURRING) {
                    $frequencyWrapper.show();
                } else {
                    $frequencyWrapper.hide();
                }

            }

            function expenseTypeChangeHandler(e) {
                let selectedExpenseType = $(this).val();
                let $item = $(this).closest('.expense_item')
                let itemIndex = $item.data('list-index')
                let $expenseSubTypeSelect = $item.find(`[name="expenses[${itemIndex}][expense_sub_type_id]"]`)
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
            }

            $(document).on('click', "#createExpenseBtn", (event) => createExpense())

            function copyPreviousData($item) {
                let itemIndex = Number($item.attr('data-list-index'));
                let itemCount = $('#expenses').find('.expense_item').length;
                if(itemCount <= 1) return;
                let $prevItem = $item.prev();
                if($prevItem.find('[data-name=client_id]').val()) {
                    $item.find('[data-name=client_id]').val($prevItem.find('[data-name=client_id]').val());
                    $item.find('[data-name=client_id]').trigger('change.select2');
                }
                if($prevItem.find('[data-name=project_name]').val()) {
                    $item.find('[data-name=project_name]').val($prevItem.find('[data-name=project_name]').val())
                }
                if($prevItem.find('[data-name=expense_date]').val()) {
                    let fp = $item.find('[data-name=expense_date]').get(0)._flatpickr;
                    let prevFp = $prevItem.find('[data-name=expense_date]').get(0)._flatpickr;
                    if(fp && prevFp) fp.setDate(prevFp.selectedDates);
                }
                if($prevItem.find('[data-name=currency_id]').val()) {
                    $item.find('[data-name=currency_id]').val($prevItem.find('[data-name=currency_id]').val());
                    $item.find('[data-name=currency_id]').trigger('change.select2');
                }
            }
        });
    </script>
@endsection
