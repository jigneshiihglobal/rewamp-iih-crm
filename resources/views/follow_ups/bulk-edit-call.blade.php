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
    <link rel="stylesheet" href="{{ asset('app-assets/css/custom/flatpickr.css?v=' . config('versions.css')) }}">
    <style>
        .flatpickr-wrapper {
            width: 100%;
        }

        .flatpickr-input,
        .ql-toolbar {
            background: #fff;
        }
    </style>
@endsection

@section('content')
    <section>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header flex-column align-items-start p-1 pb-50">
                        <h4 class="card-title">Add / Edit Follow Up Reminders</h4>
                        @if ($completed_follow_up_count)
                            <ul class="mb-0 mt-50 custom-bg-Gray p-25 list-unstyled rounded">
                                @foreach ($latest_completed_follow_ups as $follow_up_date)
                                    <li>
                                        Follow up reminder sent at
                                        {{ $follow_up_date->timezone(Auth::user()->timezone)->format(App\Helpers\DateHelper::FOLLOW_UP_DATE) }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <hr class="m-0">
                    <div class="card-body py-50 px-1">
                        <form class="form repeater" id="followUpForm" method="POST"
                            action="{{ route('leads.follow-ups.bulk-update', [$lead->encrypted_id, App\Enums\FollowUpType::CALL]) }}">
                            @csrf
                            <div data-repeater-list="lead_follow_ups" class="d-flex flex-column gap-1">
                                @forelse ($follow_ups as $follow_up)
                                    <div data-repeater-item
                                        class="d-flex justify-content-between p-50 border bg-light rounded">
                                        <input type="hidden" name="follow_up_id" value="{{ $follow_up->id }}">
                                        <div class="flex-fill row ">
                                            <div class="col-12">
                                                <h5>Follow up #<span
                                                        class="followUpNumber">{{ $completed_follow_up_count + $loop->iteration }}</span>
                                                </h5>
                                            </div>
                                            <div class="col-md-6 col-12 form-group">
                                                <label for="sales_person_phone" class="form-label">Sales Person Phone <span
                                                        class="text-danger">*</span></label>
                                                <select name="sales_person_phone"
                                                    class="form-select select2-size-sm select2" multiple='multiple'
                                                    data-rule-required="true" data-msg-required="Please enter phone number"
                                                    data-rule-validPhones="true"
                                                    data-msg-validPhones="Please enter valid phone number">
                                                    @foreach ($follow_up->sales_person_phone as $sales_person_phone)
                                                        <option value="{{ $sales_person_phone }}" selected>
                                                            {{ $sales_person_phone }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3 col-12 form-group">
                                                <label for="follow_up_date" class="form-label">Follow up date <span
                                                        class="text-danger">*</span></label>
                                                <div>
                                                    <input type="text" name="follow_up_date"
                                                        class="form-control form-control-sm" style="width: 100%;"
                                                        value="{{ $follow_up->follow_up_at->timezone(Auth::user()->timezone)->format(App\Helpers\DateHelper::FOLLOW_UP_DATE_DATE) }}"
                                                        data-rule-required="true"
                                                        data-msg-required="Please enter follow up date"
                                                        data-rule-validDateTimeFormat="DD/MM/YYYY"
                                                        data-msg-validDateTimeFormat="Please enter date in format DD/MM/YYYY">
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12 form-group">
                                                <label for="follow_up_time" class="form-label">Follow up time <span
                                                        class="text-danger">*</span></label>
                                                <div>
                                                    <input type="text" name="follow_up_time"
                                                        class="form-control form-control-sm" style="width: 100%;"
                                                        value="{{ $follow_up->follow_up_at->timezone(Auth::user()->timezone)->format(App\Helpers\DateHelper::FOLLOW_UP_DATE_HOUR) }}"
                                                        data-rule-required="true"
                                                        data-msg-required="Please enter follow up time"
                                                        data-rule-validDateTimeFormat="hh:mm A"
                                                        data-msg-validDateTimeFormat="Please enter time in format hh:mm A">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pb-1 ps-1 ">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm btn-icon rounded-circle"
                                                title="Remove Follow Up" data-bs-toggle="tooltip" data-repeater-delete
                                                @if ($follow_ups->count() === 1) style="display: none;" @endif>
                                                <i data-feather="trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div data-repeater-item
                                        class="d-flex justify-content-between p-50 border bg-light rounded">
                                        <input type="hidden" name="follow_up_id">
                                        <div class="flex-fill row">
                                            <div class="col-12">
                                                <h5>Follow up #<span
                                                        class="followUpNumber">{{ $completed_follow_up_count + 1 }}</span>
                                                </h5>
                                            </div>
                                            <div class="col-md-6 col-12 form-group">
                                                <label for="sales_person_phone" class="form-label">Sales Person Phone <span
                                                        class="text-danger">*</span></label>
                                                <div class="position-relative">
                                                    <select name="sales_person_phone"
                                                        class="form-select select2-size-sm select2" multiple='multiple'
                                                        data-rule-required="true"
                                                        data-msg-required="Please enter phone number"
                                                        data-rule-validPhones="true"
                                                        data-msg-validPhones="Please enter valid phone number">
                                                        @if (auth()->user()->phone)
                                                            <option value="{{ auth()->user()->phone }}" selected>
                                                                {{ auth()->user()->phone }}
                                                            </option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12 form-group">
                                                <label for="follow_up_date" class="form-label">Follow up date <span
                                                        class="text-danger">*</span></label>
                                                <div>
                                                    <input type="text" name="follow_up_date"
                                                        class="form-control form-control-sm" style="width: 100%;"
                                                        value="{{ now()->addHour()->timezone(Auth::user()->timezone)->format('d/m/Y') }}"
                                                        data-rule-required="true"
                                                        data-msg-required="Please enter follow up date"
                                                        data-rule-validDateTimeFormat="DD/MM/YYYY"
                                                        data-msg-validDateTimeFormat="Please enter date in format DD/MM/YYYY">
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12 form-group">
                                                <label for="follow_up_time" class="form-label">Follow up time <span
                                                        class="text-danger">*</span></label>
                                                <div>
                                                    <input type="text" name="follow_up_time"
                                                        class="form-control form-control-sm" style="width: 100%;"
                                                        value="{{ now()->addHour()->timezone(Auth::user()->timezone)->format(App\Helpers\DateHelper::FOLLOW_UP_DATE_HOUR) }}"
                                                        data-rule-required="true"
                                                        data-msg-required="Please enter follow up time"
                                                        data-rule-validDateTimeFormat="hh:mm A"
                                                        data-msg-validDateTimeFormat="Please enter time in format hh:mm A">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pb-1 ps-1">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm btn-icon rounded-circle"
                                                title="Remove Follow Up" data-bs-toggle="tooltip" data-repeater-delete
                                                style="display: none;">
                                                <i data-feather="trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                            <div class="row mt-50">
                                <div class="col-12 align-items-start">
                                    <div class="float-start">
                                        <button data-repeater-create type="button" class="btn btn-outline-info btn-sm">
                                            <i data-feather="plus-circle"></i>
                                            <span>Add new follow up</span>
                                        </button>
                                    </div>
                                    <div class="float-end me-2">
                                        <a href="{{ route('leads.index') }}"
                                            class="btn btn-outline-secondary btn-sm">Cancel</a>
                                        <button type="submit" class="btn btn-primary btn-sm"
                                            id="FollowUpSubmitBtn">Save</button>
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
    <script src="{{ asset('app-assets/vendors/js/forms/repeater/jquery.repeater.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/extensions/moment.min.js?v=' . config('versions.js')) }}"></script>
@endsection
@section('custom-js')
    <script src="{{ asset('app-assets/js/custom/flatpickr.js?v=' . config('versions.js')) }}"></script>
    <script>
        $(document).ready(function() {
            const lead = @json($lead);
            const authuser = @json(auth()->user());
            const completedFollowUpCount = @json($completed_follow_up_count);

            if (feather) feather.replace();

            function initForm() {
                $('.select2-container').remove();
                $('.select2').each(function() {
                    let $this = $(this);
                    $this.select2({
                        tags: true,
                        containerCssClass: 'select-sm',
                        width: '100%',
                        dropdownParent: $('body')
                    });
                });
                $('.select2-container').css('width', '100%');
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
                $(`[data-repeater-item]`).each(function(i) {
                    $(this).find('.followUpNumber').text(Number(completedFollowUpCount) + 1 + i);
                    let followUpDateInput = document.querySelector(
                        `[name="lead_follow_ups[${i}][follow_up_date]"]`);
                    let followUpTimeInput = document.querySelector(
                        `[name="lead_follow_ups[${i}][follow_up_time]"]`);
                    if (!followUpDateInput?._flatpickr) {
                        flatpickr(followUpDateInput, {
                            minDate: 'today',
                            dateFormat: "d/m/Y",
                            allowInput: true,
                        });
                    }
                    if (!followUpTimeInput?._flatpickr) {
                        flatpickr(followUpTimeInput, {
                            allowInput: true,
                            enableTime: true,
                            noCalendar: true,
                            time_24hr: false,
                            minuteIncrement: 15,
                            dateFormat: "G:i K",
                        });
                    }
                })
            }
            const followUpTypes = @json(App\Enums\FollowUpType::all());
            const followUps = @json($follow_ups);
            const hourLater = new Date();
            hourLater.setHours(hourLater.getHours() + 1);
            const repeaterInstance = $('.repeater').repeater({
                ready: function(setIndexes) {
                    setIndexes();
                    initForm();
                },
                defaultValues: {
                    sales_person_phone: authuser?.phone ?? '',
                    follow_up_date: flatpickr.formatDate(hourLater, "d/m/Y"),
                    follow_up_time: flatpickr.formatDate(hourLater, "G:i K"),
                },
                show: function() {
                    let len = $('[data-repeater-list=lead_follow_ups] [data-repeater-item]').length;
                    if (len > 1) {
                        $('[data-repeater-list=lead_follow_ups] [data-repeater-item] [data-repeater-delete]')
                            .show();
                    }
                    initForm();
                    $(this).slideDown();
                },
                hide: function(deleteElement) {
                    let len = $('[data-repeater-list=lead_follow_ups] [data-repeater-item]')
                        .length;
                    if (len <= 2) {
                        $('[data-repeater-list=lead_follow_ups] [data-repeater-item] [data-repeater-delete]')
                            .hide();
                    }
                    $(this).slideUp(function() {
                        deleteElement();
                        initForm();
                    });
                },
            });

            $.validator.addMethod("validDateTimeFormat", function(value, element, param) {
                console.log(value, element, param);
                return this.optional(element) || moment(value, param).isValid();
            }, "Please enter a valid date in the format {0}");

            var emailValidator = $('#followUpForm').validate({
                ignore: [],
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#FollowUpSubmitBtn').prop('disabled', true);
                    $('#followUpForm').block({
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
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.errors) {
                                let errors = {};
                                Object.keys(response?.errors).forEach(errKey => {
                                    let errKeyArr = errKey?.split('.');
                                    if (errKey === 'lead_follow_ups') {
                                        Swal.fire({
                                            title: 'An error occurred',
                                            text: xhr.responseJSON?.errors
                                                ?.lead_follow_ups,
                                            icon: 'error',
                                        });
                                        return;
                                    }

                                    if (errKeyArr?.length) {
                                        let tempArr = errKeyArr.map((val, i) => {
                                            if (i == 0) {
                                                return val;
                                            }
                                            if (i == 3 && errKeyArr[2] ===
                                                "sales_person_phone") {
                                                return `[]`;
                                            }
                                            return `[${val}]`;
                                        });
                                        errors[tempArr.join('')] = response
                                            .errors?.[errKey] ?? '';
                                    }
                                });

                                console.log('errors', errors);
                                $(form).validate().showErrors(errors);
                            } else {
                                toastr.success(null, "Follow ups updated successfully!");
                            }
                            $('#followUpForm').unblock();
                            $('#FollowUpSubmitBtn').prop('disabled', false);
                            window.location.href = route('leads.index');
                        },
                        error: function(xhr, status, error) {
                            $('#followUpForm').unblock();
                            $('#FollowUpSubmitBtn').prop('disabled', false);
                            if (xhr.status == 422) {
                                let errors = {};

                                Object.keys(xhr.responseJSON?.errors).forEach(errKey => {
                                    let errKeyArr = errKey?.split('.');

                                    if (errKeyArr?.length) {
                                        let tempArr = errKeyArr.map((val, i) => {

                                            console.log('val', val)
                                            console.log('i', i)

                                            if (i == 0) {
                                                return val;
                                            }
                                            if (i == 3 && errKeyArr[2] ===
                                                "sales_person_phone") {
                                                return `[]`;
                                            }
                                            return `[${val}]`;
                                        });
                                        errors[tempArr.join('')] = xhr.responseJSON
                                            .errors?.[errKey] ?? '';
                                    }
                                });

                                console.log('errors', errors);

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
                    if (element.hasClass('select2')) {
                        error.appendTo(element.parent())
                    } else {
                        error.insertAfter(element);
                    }
                }
            });
        });
    </script>
@endsection
