@extends('layouts.app')

@section('page-css')
@endsection

@section('content')
    <section id="systemSettings">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-1 border-bottom">
                        <h4 class="card-title font-medium-1">System Settings</h4>
                    </div>
                    <div class="card-body px-1 py-25">
                        <form class="validate-form" id="updateSettingsForm" action="{{ route('system-settings.update') }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-12 mb-1">
                                    <label class="form-label" for="whitelisted_ips">
                                        Whitelisted IPs
                                        <i class="text-primary" data-feather='info' data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="The administrator won't get login mail notification when logged in from these IPs."
                                            data-bs-original-title="The administrator won't get login mail notification when logged in from these IPs."></i>
                                    </label>
                                    <select id="whitelisted_ips" name="whitelisted_ips[]"
                                        class="select2 form-select select2-size-sm" multiple='multiple'>
                                        @if (!empty($system_settings))
                                            @foreach ($system_settings->whitelisted_ips as $ip)
                                                <option value="{{$ip}}" selected>{{$ip}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-1">
                                    <label class="form-label" for="login_mail_recipients">
                                        Login Mail Recipient
                                        <i class="text-primary" data-feather='info' data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="The administrator will get login mail notification on emails provided here."
                                            data-bs-original-title="The administrator will get login mail notification on emails provided here."></i>
                                    </label>
                                    <select id="login_mail_recipients" name="login_mail_recipients[]"
                                        class="select2 form-select select2-size-sm" multiple='multiple'>
                                        @if (!empty($system_settings))
                                            @foreach ($system_settings->login_mail_recipients as $email)
                                                <option value="{{$email}}" selected>{{$email}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-50 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-sm"
                                        id="updateSettingsSubmitBtn">Save</button>
                                </div>
                            </div>
                        </form>
                        <!--/ form -->
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('page-vendor-js')
@endsection

@section('custom-js')
    <script>
        $(document).ready(function() {
            $('.select2').each(function() {
                let $select = $(this);
                $select.wrap('<div class="position-relative"></div>');
                let config = {
                    containerCssClass: 'select-sm',
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $select.parent().get(0),
                    tags: true,
                };
                $select.select2(config);
            });

            const $updateSettingsForm = $('#updateSettingsForm');
            const $updateSettingsSubmitBtn = $('#updateSettingsSubmitBtn');
            $updateSettingsForm.validate({
                rules: {
                    'whitelisted_ips[]': {
                        required: true,
                        validIps: true,
                    },
                    'login_mail_recipients[]': {
                        required: true,
                        validEmails: true,
                    },
                },
                messages: {
                    'whitelisted_ips[]': {
                        required: "Please enter IP addresses",
                        validIps: "Please enter valid IP addresses"
                    },
                    'login_mail_recipients[]': {
                        required: "Please enter emails addresses",
                        validIps: "Please enter valid email addresses"
                    },
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $updateSettingsSubmitBtn.prop('disabled', true);
                    $updateSettingsForm.block({
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
                        data: new FormData(form),
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                toastr.success(null, "Settings updated successfully!");
                            }
                            $updateSettingsForm.unblock();
                            $updateSettingsSubmitBtn.prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            $updateSettingsForm.unblock();
                            $updateSettingsSubmitBtn.prop('disabled', false);
                            if (xhr.status == 422) {
                                $(form).validate().showErrors(JSON.parse(xhr.responseText)
                                    .errors);
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
            });
        });
    </script>
@endsection
