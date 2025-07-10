@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/katex.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/monokai-sublime.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.snow.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.bubble.css?v=' . config('versions.css')) }}">
@endsection

@section('page-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/plugins/forms/form-quill-editor.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <link rel="stylesheet" href="{{ asset('app-assets/css/custom/flatpickr.css?v=' . config('versions.css')) }}">
    <style>
        .flatpickr-wrapper {
            width: 100%;
        }

        .flatpickr-input,
        .ql-toolbar,
        .contentQuill {
            background: #fff;
        }

        .contentQuill {
            min-height: 24vh;
            resize: vertical;
            overflow: auto;
        }

        .contentQuill .ql-editor {
            min-height: 24vh;
        }
    </style>
@endsection

@section('content')
    <section>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header flex-column align-items-start p-1 pb-50">
                        <h4 class="card-title">Add / Edit Follow Ups</h4>
                        @if ($completed_follow_up_count)
                            <ul class="mb-0 mt-50 custom-bg-Gray p-25 list-unstyled rounded">
                                @foreach ($latest_completed_follow_ups as $date)
                                    <li>
                                        Follow up sent at
                                        {{ $date->timezone(Auth::user()->timezone)->format(App\Helpers\DateHelper::FOLLOW_UP_DATE) }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <hr class="m-0">
                    <div class="card-body py-50 px-1">
                        <form class="form repeater" id="followUpForm" method="POST"
                            action="{{ route('leads.follow-ups.bulk-update', [$lead->encrypted_id, App\Enums\FollowUpType::EMAIL]) }}">
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
                                                <label for="to" class="form-label">To <span
                                                        class="text-danger">*</span></label>
                                                <select name="to" class="form-select select2-size-sm select2"
                                                    multiple='multiple' data-rule-required="true"
                                                    data-msg-required="Please enter email address"
                                                    data-rule-validEmails="true"
                                                    data-msg-validEmails="Please enter valid email address">
                                                    @foreach ($follow_up->to as $to)
                                                        <option value="{{ $to }}" selected>
                                                            {{ $to }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 col-12 form-group">
                                                <label for="bcc" class="form-label">BCC </label>
                                                <select name="bcc" class="form-select select2-size-sm select2"
                                                    multiple='multiple' data-rule-validEmails="true"
                                                    data-msg-validEmails="Please enter valid email address">
                                                 <!--   <option value="{{ Auth::user()->email }}">{{ Auth::user()->email }}</option> -->
                                                    @workspace('shalin-designs')
                                                        @if (config('shalin-designs.follow_ups.client_emails.bcc') &&
                                                                is_array(config('shalin-designs.follow_ups.client_emails.bcc')) &&
                                                                count(config('shalin-designs.follow_ups.client_emails.bcc')))
                                                            @foreach (config('shalin-designs.follow_ups.client_emails.bcc') as $shalinBcc)
                                                                <option value="{{ $shalinBcc }}">
                                                                    {{ $shalinBcc }}</option>
                                                            @endforeach
                                                        @endif
                                                    @endworkspace
                                                    @foreach ($follow_up->bcc as $bcc)
                                                        <option value="{{ $bcc }}" selected>
                                                            {{ $bcc }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 col-12 form-group">
                                                <label for="subject" class="form-label">Subject <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="subject" class="form-control form-control-sm"
                                                    value="{{ $follow_up->subject }}" data-rule-required="true"
                                                    data-msg-required="Please enter subject" data-rule-min-length="125"
                                                    data-msg-min-length="Please enter subject shorter than 125 characters">
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
                                            <div class="col-md-3 col-12 form-group">
                                                <label for="email_signature_id" class="form-label">
                                                    Email Signature
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <div class="position-relative">
                                                    <select name="email_signature_id"
                                                        class="form-select select2-size-sm select2"
                                                        data-rule-required="true"
                                                        data-msg-required="Please select a signature">
                                                        @foreach (Auth::user()->email_signatures as $sign)
                                                            <option value="{{ $sign->id }}"
                                                                @if ($sign->id == $follow_up->email_signature_id) selected @endif>
                                                                {{ $sign->sign_name ?? 'IIH Global' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12 form-group">
                                                <label for="smtp_credential_id" class="form-label">
                                                    SMTP
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <div class="position-relative">
                                                    <select name="smtp_credential_id"
                                                        class="form-select select2-size-sm select2"
                                                        data-rule-required="true"
                                                        data-msg-required="Please select a smtp to send the mail from">
                                                        @foreach (Auth::user()->smtp_credentials as $smtp)
                                                            <option value="{{ $smtp->id }}"
                                                                @if ($smtp->id == $follow_up->smtp_credential_id) selected @endif>
                                                                {{ $smtp->smtp_name ?? $smtp->host }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 form-group">
                                                <div>
                                                    <label for="contentQuill" class="form-label">Content <span
                                                            class="text-danger">*</span></label>
                                                    <div class="contentQuill">{!! $follow_up->content !!}
                                                    </div>
                                                    <input type="hidden" name="content" data-rule-required="true"
                                                        data-msg-required="Please enter content" />
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
                                                <label for="to" class="form-label">To <span
                                                        class="text-danger">*</span></label>
                                                <div class="position-relative">
                                                    <select name="to" class="form-select select2-size-sm select2"
                                                        multiple='multiple' data-rule-required="true"
                                                        data-msg-required="Please enter email address"
                                                        data-rule-validEmails="true"
                                                        data-msg-validEmails="Please enter valid email address">
                                                        @if ($lead->email)
                                                            <option value="{{ $lead->email }}" selected>
                                                                {{ $lead->email }}
                                                            </option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12 form-group">
                                                <label for="bcc" class="form-label">BCC</label>
                                                <div class="position-relative">
                                                    <select name="bcc" class="form-select select2-size-sm select2"
                                                        multiple='multiple' data-rule-validEmails="true"
                                                        data-msg-validEmails="Please enter valid email address">
                                                        <option value="{{ Auth::user()->email }}" selected>
                                                            {{ Auth::user()->email }}</option>
                                                        @workspace('shalin-designs')
                                                            @if (config('shalin-designs.follow_ups.client_emails.bcc') &&
                                                                    is_array(config('shalin-designs.follow_ups.client_emails.bcc')) &&
                                                                    count(config('shalin-designs.follow_ups.client_emails.bcc')))
                                                                @foreach (config('shalin-designs.follow_ups.client_emails.bcc') as $shalinBcc)
                                                                    <option value="{{ $shalinBcc }}" selected>
                                                                        {{ $shalinBcc }}</option>
                                                                @endforeach
                                                            @endif
                                                        @endworkspace
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12 form-group">
                                                <label for="subject" class="form-label">Subject <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="subject" class="form-control form-control-sm"
                                                    data-rule-required="true" data-msg-required="Please enter subject"
                                                    data-rule-min-length="125"
                                                    data-msg-min-length="Please enter subject shorter than 125 characters">
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
                                            <div class="col-md-3 col-12 form-group">
                                                <label for="email_signature_id" class="form-label">
                                                    Email Signature
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <div class="position-relative">
                                                    <select name="email_signature_id"
                                                        class="form-select select2-size-sm select2"
                                                        data-rule-required="true"
                                                        data-msg-required="Please select a signature">
                                                        @foreach (Auth::user()->email_signatures as $sign)
                                                            <option value="{{ $sign->id }}">
                                                                {{ $sign->sign_name ?? 'IIH Global' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12 form-group">
                                                <label for="smtp_credential_id" class="form-label">
                                                    SMTP
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <div class="position-relative">
                                                    <select name="smtp_credential_id"
                                                        class="form-select select2-size-sm select2"
                                                        data-rule-required="true"
                                                        data-msg-required="Please select a smtp">
                                                        @foreach (Auth::user()->smtp_credentials as $smtp)
                                                            <option value="{{ $smtp->id }}">
                                                                {{ $smtp->smtp_name ?? $smtp->host }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 form-group">
                                                <div>
                                                    <label for="contentQuill" class="form-label">Content <span
                                                            class="text-danger">*</span></label>
                                                    <div class="contentQuill"></div>
                                                    <input type="hidden" name="content" data-rule-required="true"
                                                        data-msg-required="Please enter content" />
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
    <script src="{{ asset('app-assets/vendors/js/editors/quill/katex.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/highlight.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/quill.min.js?v=' . config('versions.js')) }}"></script>
@endsection
@section('custom-js')
    <script src="{{ asset('app-assets/js/custom/flatpickr.js?v=' . config('versions.js')) }}"></script>
    <script>
        $(document).ready(function() {
            const lead = @json($lead);
            const authEmail = @json(Auth::user()->email);
            const completedFollowUpCount = @json($completed_follow_up_count);
            const ws = @json(Auth::user()->active_workspace->slug);
            const shalinBcc = @json(config('shalin-designs.follow_ups.client_emails.bcc', []));
            shalinBcc.push(authEmail);

            if (feather) feather.replace();

            function initForm() {

                $('.select2-container').remove();
                $('.select2').each(function() {
                    let $this = $(this);
                    if(!$this.parent().hasClass('position-relative')) $this.wrap("<div class='position-relative'></div>")
                    let config = {
                        tags: true,
                        containerCssClass: 'select-sm',
                        width: '100%',
                        dropdownParent: $this.parent()
                    };
                    if (!$this.attr('multiple')) config.tags = false;
                    $this.select2(config);
                });
                $('.select2-container').css('width', '100%');

                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }

                $(`[data-repeater-item]`).each(function(i) {
                    $(this).find('.followUpNumber').text(completedFollowUpCount + i + 1);
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
                    let $quill = $(this).find('.contentQuill');
                    let quill = $quill.get(0);
                    let contentQuill = Quill.find(quill);
                    let textChangeHandler = function(delta, oldDelta, source) {
                        if (contentQuill.getLength() === 1) {
                            $(`input[name="lead_follow_ups[${i}][content]"]`).val('');
                            return;
                        }
                        $(`input[name="lead_follow_ups[${i}][content]"]`).val(contentQuill.root
                            .innerHTML);
                    };
                    if (!contentQuill) {
                        contentQuill = new Quill(quill, {
                            theme: 'snow',
                            format: {
                                fontFamily: 'Public Sans'
                            }
                        });
                        if (contentQuill.getLength() > 1) {
                            $(`input[name="lead_follow_ups[${i}][content]"]`).val(contentQuill.root
                                .innerHTML);
                        } else {
                            $(`input[name="lead_follow_ups[${i}][content]"]`).val('');
                        }
                        contentQuill.on('text-change', textChangeHandler);
                    } else {
                        contentQuill.off('text-change');
                        contentQuill.on('text-change', textChangeHandler);
                    }
                })
            }
            const followUpTypes = @json(App\Enums\FollowUpType::all());
            const followUps = @json($follow_ups);
            const hourAfter = new Date();
            hourAfter.setHours(hourAfter.getHours() + 1);
            const repeaterInstance = $('.repeater').repeater({
                ready: function(setIndexes) {
                    setIndexes();
                    initForm();
                },
                defaultValues: {
                    to: lead?.email ?? '',
                    bcc: ws == 'shalin-designs' ? shalinBcc : (authEmail ?? ''),
                    follow_up_date: flatpickr.formatDate(hourAfter, "d/m/Y"),
                    follow_up_time: flatpickr.formatDate(hourAfter, "G:i K"),
                    email_signature_id: '',
                    smtp_credential_id: '',
                    subject: '',
                    content: '',
                },
                show: function() {
                    let len = $('[data-repeater-list=lead_follow_ups] [data-repeater-item]').length;
                    if (len > 1) {
                        $('[data-repeater-list=lead_follow_ups] [data-repeater-item] [data-repeater-delete]')
                            .show();
                    }
                    $(this).slideDown();
                    initForm();
                    $(this).find('.contentQuill').find('.ql-editor').html('');
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
                        url: route('leads.follow-ups.bulk-update', [
                            "{{ $lead->encrypted_id }}"
                        ]),
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
                                            if (i == 3 && (errKeyArr[2] ===
                                                    'to' || errKeyArr[2] ===
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


                                            if (i == 0) {
                                                return val;
                                            }
                                            if (i == 3 && (errKeyArr[2] ===
                                                    'to' || errKeyArr[2] ===
                                                    'bcc')) {
                                                return `[]`;
                                            }
                                            return `[${val}]`;
                                        });
                                        errors[tempArr.join('')] = xhr.responseJSON
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
