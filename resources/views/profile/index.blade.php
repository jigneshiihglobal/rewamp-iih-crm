@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/animate/animate.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/file-uploaders/dropzone.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/custom/bootstrap-datepicker.css?v=' . config('versions.css')) }}">
    <style>
        form .form-label {
            font-weight: 700;
        }

        #emailSignaturePreviewContainer p {
            line-height: 1rem;
        }

        #emailSignaturePreviewContainer table td {
            padding: unset;
        }
    </style>
@endsection

@section('content')
    <section class="app-user-view-security">
        <div class="row">
            <!-- User Sidebar -->
            <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
                <!-- User Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="user-avatar-section">
                            <div class="d-flex align-items-center flex-column">
                                <div class="position-relative">
                                    <div class="profilePictureContainer mt-3 mb-2">
                                        <img class="rounded "
                                            src="@if (!$user->pic) {{ asset('app-assets/images/svg/user.svg') }}@else{{ url('storage/' . $user->pic) }} @endif"
                                            alt="User avatar" id="profilePic" />
                                    </div>

                                    <div class="position-absolute mt-3 top-0 start-100">
                                        <button class="btn btn-icon rounded-circle btn-flat-info waves-effect"
                                            id="editPicBtn" title="Change Profile Picture">
                                            <i data-feather="edit"></i>
                                        </button>
                                        <button class="btn btn-icon rounded-circle btn-flat-danger waves-effect"
                                            @if (!$user->pic) style='display: none' @endif id="removePicBtn"
                                            title="Remove Profile Picture">
                                            <i data-feather="trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="dropzoneContainer" style="display: none" class="position-relative">
                                    <div id="picDropzone" class="dropzone dropzone-area mb-1">
                                        <div class="dz-message"><i data-feather="upload"></i> Click here or drag and drop an
                                            image</div>
                                    </div>
                                    <div class="position-absolute top-0 start-100">
                                        <button class="btn btn-icon rounded-circle btn-flat-danger waves-effect"
                                            id="cancelPic" title="Cancel">
                                            <i data-feather="x-circle"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="alert alert-info p-1">
                                    <ul class="mb-0">
                                        <li>
                                            Please select a PNG, JPG or JPEG file only.
                                        </li>
                                        <li>
                                            File size must be less than or equal to 1 MB.
                                        </li>
                                    </ul>
                                </div>
                                <div class="user-info text-center">
                                    <h4>{{ $user->full_name }}</h4>
                                    <span class="badge bg-light-secondary">
                                        @if ($user->hasRole('Superadmin'))
                                            Superadmin
                                        @elseif ($user->hasRole('Admin'))
                                            Admin
                                        @else
                                            User
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /User Card -->
            </div>
            <!--/ User Sidebar -->

            <!-- User Content -->
            <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
                <!-- User Pills -->
                <ul class="nav nav-pills mb-2">
                    <li class="nav-item">
                        <a class="nav-link active" id="user-details" data-bs-toggle="pill" href="#userDetails"
                            aria-expanded="true">
                            <i data-feather="info" class="font-medium-3 me-50"></i>
                            <span class="fw-bold">Details</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="user-security" data-bs-toggle="pill" href="#userSecurity"
                            aria-expanded="false">
                            <i data-feather="lock" class="font-medium-3 me-50"></i>
                            <span class="fw-bold">Security</span>
                        </a>
                    </li>
                    @workspace('iih-global')
                        @unlessrole('Marketing')
                            <li class="nav-item">
                                <a class="nav-link" id="email-signature" data-bs-toggle="pill" href="#emailSignature"
                                    aria-expanded="false">
                                    <i data-feather="pen-tool" class="font-medium-3 me-50"></i>
                                    <span class="fw-bold">Email Signature</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="smtp-settings" data-bs-toggle="pill" href="#smtpSettings"
                                    aria-expanded="false">
                                    <i data-feather="mail" class="font-medium-3 me-50"></i>
                                    <span class="fw-bold">SMTP</span>
                                </a>
                            </li>
                        @endrole
                    @endworkspace
                    @role('Admin|Superadmin')
                        @if ($user->id != Auth::id())
                            <li class="nav-item">
                                <a class="nav-link" id="user-groups" data-bs-toggle="pill" href="#userGroups"
                                    aria-expanded="false">
                                    <i data-feather="users" class="font-medium-3 me-50"></i>
                                    <span class="fw-bold">Roles</span>
                                </a>
                            </li>
                        @endif
                    @endrole
                    @if (Route::is('users.show'))
                        <a class="ms-auto p-1" href="{{ route('users.index') }}">
                            <i data-feather="arrow-left" class="font-medium-3 me-50"></i>
                            <span class="fw-bold">Go to Users List</span>
                        </a>
                    @endif
                </ul>
                <!--/ User Pills -->
                <div class="tab-content">
                    <!-- user details -->
                    <div role="tabpanel" class="tab-pane active card" id="userDetails" aria-expanded="true"
                        aria-labelledby="user-details">
                        <div class="card-body">
                            <div class="form-container">
                                <form action="{{ route('profile.update', $user->encrypted_id) }}" method="POST"
                                    id="editUserForm" class="row" data-is-editing='false'>
                                    @csrf
                                    @method('PUT')
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="first_name">First Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="first_name" name="first_name" class="form-control "
                                            value="{{ $user->first_name }}" />
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="last_name">Last Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="last_name" name="last_name" class="form-control "
                                            value="{{ $user->last_name }}" />
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="email">Email <span
                                                class="text-danger">*</span></label>
                                        <input type="email" id="email" name="email" class="form-control "
                                            value="{{ $user->email }}" />
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="text" id="phone" name="phone" class="form-control "
                                            value="{{ $user->phone }}" />
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="dob">Birth date:</label>
                                        <div class="input-group">
                                            <input type="text" id="dob" name="dob" class="form-control "
                                                placeholder="dd/mm/yyyy"
                                                value="{{ $user->dob ? $user->dob->format(App\Helpers\DateHelper::DOB_DATE_FORMAT) : '' }}"
                                                autocomplete="off" aria-describedby="dob_icon" />
                                            <label class="input-group-text " id="dob_icon" for="dob"><i
                                                    data-feather="calendar"></i></label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="gender">Gender</label>
                                        <div class="mt-50">
                                            <div class="form-check form-check-inline ">
                                                <input class="form-check-input" type="radio" name="gender"
                                                    id="gender_male" value="male"
                                                    @if ($user->gender != 'female' || $user->gender == 'male') checked @endif />
                                                <label class="form-check-label" for="gender_male">Male</label>
                                            </div>
                                            <div class="form-check form-check-inline ">
                                                <input class="form-check-input" type="radio" name="gender"
                                                    id="gender_female" value="female"
                                                    @if ($user->gender == 'female') checked @endif />
                                                <label class="form-check-label" for="gender_female">Female</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="address">Address</label>
                                        <textarea id="address" name="address" class="form-control " rows="3">{{ $user->address }}</textarea>
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="city">City</label>
                                        <input type="text" id="city" name="city" class="form-control "
                                            value="{{ $user->city }}" />
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="state">State</label>
                                        <input type="text" id="state" name="state" class="form-control "
                                            value="{{ $user->state }}" />
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="country">Country</label>
                                        <select id="country" name="country" class="form-select select2 ">
                                            <option selected value="">Select country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->name }}"
                                                    @if ($country->name == $user->country || $country->id == 100) selected @endif>{{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="postal">Postal Code</label>
                                        <input type="text" id="postal" name="postal"
                                            class="form-control  phone-number-mask" value="{{ $user->postal }}" />
                                    </div>
                                    <div class="col-12 col-md-6 mb-1">
                                        <label class="form-label" for="timezone">Timezone</label>
                                        <select id="timezone" name="timezone" class="form-select select2 ">
                                            <option value="">Select timezone</option>
                                            @foreach (timezone_identifiers_list() as $timezone)
                                                <option value="{{ $timezone }}"
                                                    @if ($timezone == $user->timezone) selected @endif>{{ $timezone }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 text-end mt-2 pt-50 editUserFormBtns">
                                        <button type="submit" class="btn btn-primary me-1">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!--/ user details -->


                    <!-- Change Workspaces Access -->
                    <div class="tab-pane card" role="tabpanel" id="userSecurity" aria-expanded="false"
                        aria-labelledby="user-security">
                        @role('Superadmin')
                            @if ($user->id != Auth::id())
                                <h4 class="card-header">Workspaces Access</h4>
                                <div class="card-body">
                                    <form id="formWorkspacesAccess" method="POST"
                                        action="{{ route('profile.update-workspace-access', $user->encrypted_id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="row custom-options-checkable g-1 mb-1">
                                            {{-- <div class="col-md-6"> --}}
                                            @foreach ($workspaces as $workspace)
                                                <div class="col-md-4">
                                                    <input class="custom-option-item-check" type="checkbox"
                                                        name="workspaces[]" id="{{ $workspace->encrypted_id }}"
                                                        value="{{ $workspace->id }}"
                                                        @if ($user->workspaces->contains($workspace->id)) checked @endif />
                                                    <label class="custom-option-item text-center p-1"
                                                        for="{{ $workspace->encrypted_id }}">
                                                        @if ($workspace->slug == 'iih-global')
                                                            <div class="avatar bg-light-warning p-1">
                                                                <img src="{{ asset('app-assets/images/logo/icon-logo.svg') }}"
                                                                    alt="IIH Global" class="avatar-content">
                                                            </div>
                                                        @elseif ($workspace->slug == 'shalin-designs')
                                                            <div class="avatar bg-light-warning p-1">
                                                                <img src="{{ asset('shalin-designs/img/icon-logo.png') }}"
                                                                    alt="Shalin Designs" class="avatar-content">
                                                            </div>
                                                        @else
                                                            <i data-feather="briefcase" class="font-large-1 mb-75"></i>
                                                        @endif
                                                        <span
                                                            class="custom-option-item-title h4 d-block">{{ $workspace->name }}</span>
                                                    </label>
                                                </div>
                                                {{-- <div class="form-check mb-1 @if (!$loop->last) me-1 @endif">
                                                            <input class="form-check-input" name="workspaces[]" type="checkbox" value="{{$workspace->id}}" id="check-{{$workspace->slug}}" @if ($user->workspaces->contains($workspace->id)) checked @endif>
                                                            <label class="form-check-label" for="check-{{$workspace->slug}}">
                                                                {{$workspace->name}}
                                                            </label>
                                                        </div> --}}
                                            @endforeach
                                            {{-- </div> --}}
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
                                </div>
                                <hr>
                            @endif
                        @endrole
                        <h4 class="card-header">Change Password</h4>
                        <div class="card-body">
                            <form id="formChangePassword" method="POST"
                                action="{{ route('profile.update-password', $user->encrypted_id) }}">
                                @csrf
                                @method('PUT')
                                <div class="alert alert-warning mb-2" role="alert">
                                    <h6 class="alert-heading">Ensure that these requirements are met</h6>
                                    <div class="alert-body fw-normal">Minimum 6 characters long</div>
                                </div>

                                <div class="row">
                                    <div class="mb-2 col-md-6 form-password-toggle">
                                        <label class="form-label" for="password">New Password</label>
                                        <div class="input-group input-group-merge form-password-toggle">
                                            <input class="form-control" type="password" id="password" name="password"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                autocomplete="off" />
                                            <span class="input-group-text cursor-pointer">
                                                <i data-feather="eye"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-2 col-md-6 form-password-toggle">
                                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                                        <div class="input-group input-group-merge">
                                            <input class="form-control" type="password" name="password_confirmation"
                                                id="password_confirmation" autocomplete="off"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                            <span class="input-group-text cursor-pointer"><i
                                                    data-feather="eye"></i></span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary me-2">Change Password</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--/ Change Workspaces Access -->
                    @workspace('iih-global')
                        <div class="tab-pane card" role="tabpanel" id="emailSignature" aria-expanded="false"
                            aria-labelledby="email-signature">
                            <h4 class="card-header">Change Email Signature</h4>
                            <div class="card-body">
                                <div class="alert alert-info fade show" role="alert">
                                    <div class="alert-body d-flex align-items-center">
                                        <i data-feather="alert-circle" class="me-1 font-medium-1"></i>
                                        {{-- The email signature is used only in follow up mails sent to leads. --}}
                                        The email signatures are used only in follow up mails sent to leads.
                                    </div>
                                </div>
                                <form id="emailSignaturesForm" method="POST"
                                    action="{{ route('profile.signature.update', $user->encrypted_id) }}" class="repeater">
                                    @csrf
                                    @method('PUT')
                                    <div class="d-flex flex-column gap-1" data-custom-repeater-list="email_signatures">
                                        <template>
                                            <div class="row mx-0 pt-1 bg-light border rounded" data-custom-repeater-item>
                                                <input type="hidden" name="id" data-custom-repeater-name="id">
                                                <input type="hidden" name="signature" id="signature" value="">
                                                <div class="mb-2 col-md-3 form-group">
                                                    <label for="workspace_id" class="form-label">Workspace <span
                                                            class="text-danger">*</span></label>
                                                    <div class="position-relative">
                                                        <select name="workspace_id" class="form-select select2"
                                                            data-custom-repeater-name="workspace_id"
                                                            data-rule-required="true"
                                                            data-msg-required="Please select workspace">
                                                            @foreach ($user->workspaces as $ws)
                                                                <option value="{{$ws->id}}">{{$ws->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-2 col-md-3 form-group">
                                                    <label class="form-label">Signature Name <span
                                                            class="text-danger">*</span></label>
                                                    <input class="form-control" type="text" name="sign_name"
                                                        data-custom-repeater-name="sign_name" data-rule-required="true"
                                                        data-msg-required="Please enter signature name" />
                                                </div>
                                                <div class="mb-2 col-md-6 form-group">
                                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                                    <input class="form-control " type="text" name="name"
                                                        data-custom-repeater-name="name" data-rule-required="true"
                                                        data-msg-required="Please enter name" />
                                                </div>
                                                <div class="mb-2 col-md-6 form-group">
                                                    <label class="form-label" for="position">Position <span
                                                            class="text-danger">*</span></label>
                                                    <input class="form-control " type="text" name="position"
                                                        data-custom-repeater-name="position"
                                                        placeholder="ex. Business Development Executive"
                                                        data-rule-required="true" data-msg-required="Please enter position" />
                                                </div>
                                                <div class="mb-2 col-md-6 form-group">
                                                    <label class="form-label" for="email">Email <span
                                                            class="text-danger">*</span></label>
                                                    <input class="form-control " type="text" name="email"
                                                        data-custom-repeater-name="email" data-rule-required="true"
                                                        data-msg-required="Please enter email address" data-rule-email="true"
                                                        data-msg-email="Please enter a valid email address" />
                                                </div>
                                                <div class="mb-2 col-md-6 form-group">
                                                    <label for="mobile_number" class="form-label">Phone numbers <span
                                                            class="text-danger">*</span></label>
                                                    <div class="position-relative">
                                                        <select name="mobile_number[]" class="form-select select2"
                                                            multiple='multiple' data-custom-repeater-name="mobile_number"
                                                            data-rule-required="true"
                                                            data-msg-required="Please enter phone number"
                                                            data-rule-validPhones="true"
                                                            data-msg-validPhones="Please enter valid phone number">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-2 col-md-6 form-group">
                                                    <label class="form-label" for="image_link">Schedule Meeting Link</label>
                                                    <input class="form-control " type="text" name="image_link"
                                                        data-rule-required="false"
                                                        data-rule-url="true" data-msg-url="Please enter valid url"
                                                        data-custom-repeater-name="image_link" />
                                                </div>
                                                <div class="col ms-auto d-flex align-items-end justify-content-end">
                                                    <button type="button"
                                                        class="btn btn-icon btn-outline-info btn-sm mb-1 me-50"
                                                        title="Preview" data-bs-toggle="modal"
                                                        data-bs-target="#emailSignaturePreviewModal">
                                                        <i data-feather="eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-icon btn-outline-danger btn-sm mb-1 btvalue"
                                                        data-custom-repeater-delete data-custom-repeater-name="id" onclick="datas(this.value)">
                                                        <i data-feather="trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        <input type="hidden" name="id" data-custom-repeater-name="id">
                                        <div class="order-last">
                                            <div class="float-start">
                                                <button type="button" class="btn btn-outline-info me-1"
                                                    data-custom-repeater-create>
                                                    <i data-feather="plus-circle"></i>
                                                    Add Signature
                                                </button>
                                            </div>
                                            <div class="float-end">
                                                <button type="submit" class="btn btn-primary me-1">Save</button>
                                                {{-- <button type="button" class="btn btn-outline-info me-1"
                                                >Preview</button> --}}
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="tab-pane card" role="tabpanel" id="smtpSettings" aria-expanded="false"
                            aria-labelledby="smtp-settings">
                            <h4 class="card-header">SMTP Settings</h4>
                            <div class="card-body">
                                <div class="alert alert-info fade show" role="alert">
                                    <div class="alert-body d-flex align-items-center">
                                        <i data-feather="alert-circle" class="me-1 font-medium-1"></i>
                                        The SMTP settings are used only for follow up mails sent to leads.
                                    </div>
                                </div>
                                <form id="smtpSettingsForm" method="POST"
                                    action="{{ route('profile.smtp.update', $user->encrypted_id) }}" class="repeater">
                                    @csrf
                                    @method('PUT')
                                    <div class="row m-0 p-0 gap-1" data-custom-repeater-list="smtp_settings">
                                        <template>
                                            <div class="col-12 row rounded m-0 px-0 pt-50 pb-1 border bg-light"
                                                data-custom-repeater-item>
                                                <input type="hidden" name="id" data-custom-repeater-name="id">
                                                <input type="hidden" name="smtpbtn" id="smtpbtn" value="">
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="smtp_name">SMTP Name <span
                                                            class="text-danger">*</span></label>
                                                    <input class="form-control" type="text" name="smtp_name"
                                                        data-custom-repeater-name="smtp_name" data-rule-required="true"
                                                        data-msg-required="Please enter smtp name" />
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="from_name">From Name <span
                                                            class="text-danger">*</span></label>
                                                    <input class="form-control" type="text" name="from_name"
                                                        data-custom-repeater-name="from_name" data-rule-required="true"
                                                        data-msg-required="Please enter name" data-rule-minlength="3"
                                                        data-msg-minlength="Please enter at least 3 characters" />
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="from_address">From Email Address <span
                                                            class="text-danger">*</span></label>
                                                    <input class="form-control" type="text" name="from_address"
                                                        data-custom-repeater-name="from_address" data-rule-required="true"
                                                        data-msg-required="Please enter email address" data-rule-email="true"
                                                        data-msg-email="Please enter valid email address" />
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="host">Host <span
                                                            class="text-danger">*</span>
                                                        (smtp.gmail.com, smtp.live.com, smtp.mail.yahoo.com) </label>
                                                    <input class="form-control" type="text" name="host"
                                                        data-custom-repeater-name="host" data-rule-required="true"
                                                        data-msg-required="Please enter host name" />
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="port">Port <span
                                                            class="text-danger">*</span>
                                                        (25, 465, 587) </label>
                                                    <input class="form-control" type="text" name="port"
                                                        data-custom-repeater-name="port" data-rule-required="true"
                                                        data-msg-required="Please enter port" />
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="encryption">Encryption <span
                                                            class="text-danger">*</span> (tls, ssl) </label>
                                                    <input class="form-control" type="text" name="encryption"
                                                        data-custom-repeater-name="encryption" data-rule-required="true"
                                                        data-msg-required="Please enter encryption type" />
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="username">Username <span
                                                            class="text-danger">*</span></label>
                                                    <input class="form-control" type="text" name="username"
                                                        data-custom-repeater-name="username" data-rule-required="true"
                                                        data-msg-required="Please enter username" autocomplete="new-password"
                                                        autocomplete="off" />
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="secret">Password / Secret <span
                                                            class="text-danger">*</span></label>
                                                    <input class="form-control" type="password" name="secret"
                                                        data-custom-repeater-name="secret" data-rule-required="true"
                                                        data-msg-required="Please enter secret" autocomplete="new-password"
                                                        autocomplete="off" />
                                                </div>
                                                <div class="col form-group d-flex align-items-end justify-content-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm btn-icon mt-1" data-custom-repeater-name="id" onclick="smtpbtnvalue(this.value)"
                                                        style="display: none;" data-custom-repeater-delete>
                                                        <i data-feather="trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        <div class="order-last d-flex justify-content-between px-0">
                                            <div>
                                                <button type="button" class="btn btn-outline-info"
                                                    data-custom-repeater-create>
                                                    <i data-feather="plus-circle"></i>
                                                    Add SMTP
                                                </button>
                                            </div>
                                            <div>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endworkspace

                    {{-- Change Role --}}
                    @role('Admin|Superadmin')
                        @if ($user->id != Auth::id())
                            <div class="tab-pane card" role="tabpanel" id="userGroups" aria-expanded="false"
                                aria-labelledby="user-groups">
                                <h4 class="card-header">Change Role</h4>
                                <div class="card-body">
                                    <form id="formChangeGroup" method="POST"
                                        action="{{ route('profile.update-group', $user->encrypted_id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="row align-items-end">
                                            <div class="mb-2 col-md-6 form-group">
                                                <label class="form-label" for="group">Role <span
                                                        class="text-danger">*</span></label>
                                                <select id="group" class="select2 form-select" name="group">
                                                    <option disabled>Select role</option>
                                                    @unlessrole('Superadmin')
                                                        @php
                                                            $roles = $roles->except(3);
                                                        @endphp
                                                    @endunlessrole
                                                    @foreach ($roles as $role)
                                                        @if($role->name != 'Admin')
                                                            <option value="{{ $role->id }}"
                                                                @if ($user->hasRole($role)) selected @endif>
                                                                {{ $role->name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('group')
                                                    <span id="group-error" class="error">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="row"></div>
                                            <div class="mb-2 col-md-6 form-group">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="is_active"
                                                        name="is_active" value="1"
                                                        aria-describedby="is_active_checkbox_msg"
                                                        @if ($user->is_active) checked @endif>
                                                    <label class="form-check-label" for="is_active">Is Active (Click on
                                                        checkbox to activate user)</label>
                                                </div>
                                                @if($user->roles[0]->name == 'User')
                                                    <div class="form-check mt-1">
                                                        <input type="checkbox" class="form-check-input" id="is_invoice_access"
                                                            name="is_invoice_access" value="1"
                                                            aria-describedby="is_invoice_access_checkbox_msg"
                                                            @if ($user->is_invoice_access) checked @endif>
                                                        <label class="form-check-label" for="is_invoice_access">Sales Invoice Create Access</label>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary me-2">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endrole
                    {{-- Change Group --}}
                </div>

            </div>
            <!--/ User Content -->
        </div>
    </section>

    @include('profile.modals.email-signature-preview-modal')
@endsection

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/file-uploaders/dropzone.min.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('page-js')
    <script
        src="{{ asset('app-assets/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/js/custom/repeater.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('custom-js')
    <script>
        var select = $('.select2');
        function datas(id)
        {
            $("#signature").val(id);
        }

        function smtpbtnvalue(id){
            $("#smtpbtn").val(id);
        }
        Dropzone.autoDiscover = false;
        var hasPic = {{ $user->pic ? 1 : 0 }};
        var toggleRemovePicBtn = (show = false) => hasPic && show ?
            $('#removePicBtn').show() :
            $('#removePicBtn').hide();
        const editing_self = {{ $user->id == Auth::id() ? 1 : 0 }};
        $(document).on('select2:open', (e) => {
            if(!$(e.target).prop('multiple')) document.querySelector('.select2-search__field').focus();
        });
        $(document).ready(function() {
            repeaterInit($('form#emailSignaturesForm'), {
                afterCreated: function($item) {
                    let itemIndex = $item.data('custom-repeater-list-index');
                    $item.find('select.select2').each(function() {
                        let config = {
                            dropdownAutoWidth: true,
                            width: "100%",
                            dropdownParent: $(this).parent(),
                        };
                        if ($(this).attr('name') ===
                            `email_signatures[${itemIndex}][mobile_number][]`) {
                            config.tags = true;
                        }
                        $(this).select2(config);
                    });
                    if (feather) {
                        feather.replace({
                            height: 14,
                            width: 14,
                        });
                    }
                },
                beforeDestroyed: function($item, cb) {
                    Swal.fire({
                        title: 'Are you sure you want to remove this row?',
                        text: "You need to click Save to persist your settings!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-danger ms-1'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.value) {
                         let id = $('#signature').val();
                          $.ajax({
                            url: route('email_signatures.delete',id),
                            method: 'DELETE',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                id: id,
                            },
                            success: function(response) {
                                toastr.success(null,
                                    "email signatures deleted successfully!");

                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);

                            }
                        });
                            $item.find('select.select2').each(function() {
                                $(this).select2('destroy');
                            });
                            return cb(true);
                        }
                        return cb(false);
                    });
                },
                initialData: @json(
                    $user->email_signatures->map(function ($sign) {
                            $sign->mobile_number = explode('|', $sign->mobile_number);
                            return $sign;
                        })->toArray()),
                defaultData: {
                    name: @json($user->full_name ?? ''),
                    email: @json($user->email ?? ''),
                    workspace_id: @json($user->workspaces->first()->id),
                },
            })
            repeaterInit($('form#smtpSettingsForm'), {
                afterCreated: function($item) {
                    let itemIndex = $item.data('custom-repeater-list-index');
                    if (feather) {
                        feather.replace({
                            height: 14,
                            width: 14,
                        });
                    }
                },
                beforeDestroyed: function($item, cb) {
                    Swal.fire({
                        title: 'Are you sure you want to remove this row?',
                        text: "You need to click Save to persist your settings!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-danger ms-1'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.value) {
                             let id = $('#smtpbtn').val();
                          $.ajax({
                            url: route('smtp.delete',id),
                            method: 'DELETE',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                id: id,
                            },
                            success: function(response) {
                                toastr.success(null,
                                    "smtp deleted successfully!");

                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);

                            }
                        });
                            return cb(true);
                        }
                        return cb(false);
                    });
                },
                initialData: @json($user->smtp_credentials->toArray()),
                defaultData: {
                    name: @json($user->full_name ?? ''),
                    email: @json($user->email ?? ''),
                },
            })
            $('#dob').datepicker({
                format: 'dd/mm/yyyy',
                dateFormat: 'dd/mm/yyyy',
                dataFormat: 'dd/mm/yyyy',
                orientation: 'auto bottom'
            });
            $('#editUserForm').validate({
                rules: {
                    first_name: {
                        required: true
                    },
                    last_name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    }
                },
                messages: {
                    first_name: {
                        required: "Please enter first name"
                    },
                    last_name: {
                        required: "Please enter last name"
                    },
                    email: {
                        required: "Please enter email address",
                        email: "Please enter valid email address"
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                toastr.success(null, "Profile updated successfully!");
                                let user = response.user;
                                Object.keys(user).forEach(property => {
                                    if (property == 'gender') {
                                        $('input[name="gender"]:checked').prop(
                                            'checked', false);
                                        $(`#gender_${user?.gender}`).prop('checked',
                                            true);
                                    } else {
                                        $(`[name=${property}]`).val(user[property]);
                                    }
                                });
                            }
                        },
                        error: function(xhr, status, error) {
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
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                }
            });
            $('#editPicBtn').click(function(e) {
                $('#dropzoneContainer').show();
                toggleRemovePicBtn();
                $(this).hide();
            });

            $('#cancelPic').click(function(e) {
                $('#editPicBtn').show();
                $('#dropzoneContainer').hide();
                toggleRemovePicBtn(true);
            })

            var picDropzone = $('#picDropzone').dropzone({
                url: route('profile.picture', "{{ $user->encrypted_id }}"),
                paramName: 'file',
                maxFiles: 1,
                maxFilesize: 1000 * 1000,
                acceptedFiles: '.png,.jpg,.jpeg',
                resizeWidth: 512,
                resizeHeight: 512,
                resizeMethod: 'crop',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                },
                success: function(file, response) {
                    $('#profilePic').attr('src', response.path);
                    if (editing_self) $('#header_profile_pic').attr('src', response.path);
                    hasPic = 1;
                    toggleRemovePicBtn(true);
                },
                error: function(file, response) {
                    if (response?.errors?.file) {
                        toastr.error(null, response?.errors?.file);
                    } else if (response?.message) {
                        toastr.error(null, response?.message);
                    } else if (response?.error) {
                        toastr.error(null, response?.error);
                    } else if (response) {
                        toastr.error(null, response);
                    }
                },
                complete: function(file, response) {
                    $('#editPicBtn').show();
                    $('#dropzoneContainer').hide();
                    this.removeAllFiles();
                }
            });

            $('#removePicBtn').click(function(e) {
                Swal.fire({
                    title: 'Are you sure you want to delete profile picture?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: route('profile.picture.remove',
                                "{{ $user->encrypted_id }}"),
                            method: 'DELETE',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    if (editing_self) $('#header_profile_pic').attr(
                                        'src',
                                        "{{ asset('app-assets/images/svg/user.svg') }}"
                                    );
                                    $('#profilePic').attr('src',
                                        "{{ asset('app-assets/images/svg/user.svg') }}"
                                    );
                                    hasPic = 0;
                                    toggleRemovePicBtn();
                                    toastr.success(null,
                                        "Profile picture deleted successfully!");
                                }
                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                            }
                        });

                    }
                });

            });

            $('#formChangePassword').validate({
                rules: {
                    password: {
                        required: true,
                    },
                    password_confirmation: {
                        equalTo: '#password'
                    }
                },
                messages: {
                    password: {
                        required: "Please enter new password.",
                    },
                    password_confirmation: {
                        equalTo: 'Password confirmation does not match.'
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                toastr.success(null, "Password updated successfully!");
                                form.reset();
                            }
                        },
                        error: function(xhr, status, error) {
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
                errorPlacement: function(error, element) {
                    error.insertAfter($(element).parent());
                }
            });

            var select = $('.select2');
            if (select.length) {
                select.each(function() {
                    var $this = $(this);
                    if (!$this.parent().hasClass('position-relative')) $this.wrap(
                        '<div class="position-relative"></div>');
                    let config = {
                        dropdownAutoWidth: true,
                        width: '100%',
                        dropdownParent: $this.parent()
                    };
                    if ($this.attr('name') && $this.attr('name').endsWith('[]')) config.tags = true
                    $this.select2(config);
                });
            }

            $('#formChangeGroup').validate({
                rules: {
                    group: {
                        required: true
                    }
                },
                messages: {
                    group: {
                        required: "Please select a role!"
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                toastr.success(null, "Settings updated successfully!");
                            }
                        },
                        error: function(xhr, status, error) {
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
                errorPlacement: function(error, element) {
                    error.insertAfter($(element).parent());
                }
            });

            $('#formWorkspacesAccess').validate({
                rules: {
                    "workspaces[]": {
                        required: true,
                        minlength: 1
                    }
                },
                messages: {
                    'workspaces[]': {
                        required: 'Please select a workspace',
                        minlength: 'At lease 1 workspace must be checked'
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response, status, xhr) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                toastr.success(null, "Settings updated successfully!");
                            }
                        },
                        error: function(xhr, status, error) {
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
                errorPlacement: function(error, element) {
                    if ($(element).attr('name') == 'workspaces[]') {
                        error.insertAfter($(element).parent().parent());
                    } else {
                        error.insertAfter($(element).parent());
                    }
                }
            });

            $('#emailSignaturesForm').validate({
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.errors) {
                                let errors = {};
                                Object
                                    .keys(response?.errors)
                                    .forEach(errKey => {
                                        let errKeyArr = errKey?.split('.');
                                        if (errKey === 'email_signatures') {
                                            Swal.fire({
                                                title: 'An error occurred',
                                                text: xhr.responseJSON?.errors
                                                    ?.lead_follow_ups,
                                                icon: 'error',
                                            });
                                            return;
                                        }
                                        if (!errKeyArr?.length) return;
                                        let tempArr = errKeyArr.map((val, i) => {
                                            if (i == 0) return val;
                                            if (i == 3 && errKeyArr[2] ===
                                                'mobile_number') return `[]`;
                                            return `[${val}]`;
                                        });
                                        errors[tempArr.join('')] = response.errors?.[
                                            errKey
                                        ] ?? '';
                                    });
                                $(form).validate().showErrors(errors);
                            } else {
                                Swal.fire({
                                    title: 'Signature updated successfully!',
                                    icon: 'success',
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            if (xhr.status == 422) {
                                let errors = {};
                                Object.keys(xhr.responseJSON?.errors).forEach(errKey => {
                                    let errKeyArr = errKey?.split('.');
                                    if (!errKeyArr?.length) return
                                    let tempArr = errKeyArr.map((val, i) => {
                                        if (i == 0) return val;
                                        if (i == 3 && errKeyArr[2] ===
                                            'mobile_number') return `[]`;
                                        return `[${val}]`;
                                    });
                                    errors[tempArr.join('')] = xhr.responseJSON
                                        .errors?.[errKey] ?? '';
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
                    if (element.attr('data-custom-repeater-name') && element.attr('data-custom-repeater-name') === 'workspace_id') {
                        error.appendTo(element.parent());
                    } else if (element.attr('name') && element.attr('name').endsWith('[mobile_number][]')) {
                        error.appendTo(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                }
            });
            $('#smtpSettingsForm').validate({
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.errors) {
                                let errors = {};
                                Object
                                    .keys(response?.errors)
                                    .forEach(errKey => {
                                        let errKeyArr = errKey?.split('.');
                                        if (
                                            errKey === 'smtp_settings' ||
                                            (
                                                errKeyArr[0] === 'smtp_settings' &&
                                                errKeyArr.length === 2
                                            )
                                        ) {
                                            Swal.fire({
                                                title: 'An error occurred',
                                                text: xhr.responseJSON?.errors
                                                    ?.[errKey] ?? "",
                                                icon: 'error',
                                            });
                                            return;
                                        }
                                        if (!errKeyArr?.length) return;
                                        let tempArr = errKeyArr.map((val, i) => {
                                            if (i == 0) return val;
                                            return `[${val}]`;
                                        });
                                        errors[tempArr.join('')] = response.errors?.[
                                            errKey
                                        ] ?? '';
                                    });
                                $(form).validate().showErrors(errors);
                            } else {
                                Swal.fire({
                                    title: 'SMTP Settings updated successfully!',
                                    icon: 'success',
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            if (xhr.status == 422) {
                                let errors = {};
                                Object
                                    .keys(xhr?.responseJSON?.errors)
                                    .forEach(errKey => {
                                        let errKeyArr = errKey?.split('.');
                                        if (
                                            errKey === 'smtp_settings' ||
                                            (
                                                errKeyArr[0] === 'smtp_settings' &&
                                                errKeyArr.length === 2
                                            )
                                        ) {
                                            Swal.fire({
                                                title: 'An error occurred',
                                                text: xhr.responseJSON?.errors
                                                    ?.[errKey] ?? "",
                                                icon: 'error',
                                            });
                                            return;
                                        }
                                        if (!errKeyArr?.length) return;
                                        let tempArr = errKeyArr.map((val, i) => {
                                            if (i == 0) return val;
                                            return `[${val}]`;
                                        });
                                        errors[tempArr.join('')] = xhr?.responseJSON
                                            ?.errors?.[errKey] ?? '';
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
                    error.insertAfter(element);
                }
            });

            $('#emailSignaturePreviewModal').on('show.bs.modal', function(e) {
                let $item = $(e.relatedTarget).closest('[data-custom-repeater-item]');
                let payload = {
                    _token: $('meta[name=csrf-token]').attr('content')
                };
                let valid = true;
                $item.find("[data-custom-repeater-name]").each(function(i) {
                    let $el = $(this);
                    if (!$el.valid()) {
                        valid = false;
                        e.preventDefault();
                        return;
                    }
                    payload[$el.attr('data-custom-repeater-name')] = $el.val();
                    payload['workspace_id'] = $item.find('[data-custom-repeater-name=workspace_id]').val()
                })
                if (!valid) return;

                $('#emailSignaturePreviewModal > .modal-dialog .modal-content').block({
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
                    url: route('email_signatures.preview'),
                    data: payload,
                    method: 'POST',
                    success: function(response) {

                        $('#emailSignaturePreviewModal').find('#emailSignaturePreviewContainer')
                            .html(response);
                        $('#emailSignaturePreviewModal > .modal-dialog .modal-content')
                            .unblock();
                    },
                    error: function(xhr, status, error) {
                        e.preventDefault();
                        $('#emailSignaturePreviewModal > .modal-dialog .modal-content')
                            .unblock();
                        Swal.fire({
                            title: 'An error occurred',
                            text: error,
                            icon: 'error',
                        });
                    }
                });
            });

            $('#emailSignaturePreviewModal').on('hide.bs.modal', function(e) {
                $('#emailSignaturePreviewModal').find('#emailSignaturePreviewContainer').html('');
            });
        });
    </script>
@endsection
