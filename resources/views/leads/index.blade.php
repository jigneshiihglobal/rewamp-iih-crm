@extends('layouts.app')

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/forms/cleave/cleave.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/forms/cleave/addons/cleave-phone.us.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/canvas-confetti/confetti.browser.js?v=' . config('versions.js')) }}"></script>
    <script
        src="{{ asset('app-assets/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/forms/cleave/cleave.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/forms/cleave/addons/cleave-phone.us.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/katex.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/highlight.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/quill.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/promises/promises.min.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('vendor-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/katex.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/monokai-sublime.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.snow.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.bubble.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/custom/bootstrap-datepicker.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
          href="{{ asset('app-assets/css/custom/congratulations-model.css?v=' . config('versions.css')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500&display=swap" rel="stylesheet">
    <style>
        div#contentQuill {
            height: 140px;
        }

        #addFollowUpCallForm .flatpickr-wrapper,
        #addFollowUpEmailForm .flatpickr-wrapper {
            width: 100%;
        }

        #addFollowUpModal .custom-option-item {
            --bs-bg-opacity: 0.2;
            color: #655b75;
        }

        #addFollowUpModal .custom-option-item-title {
            color: #655b75;
        }


        #contentQuill {
            min-height: 240px;
            resize: vertical;
            overflow: auto;
        }
    </style>
@endsection

@section('page-css')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/plugins/forms/form-quill-editor.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
@endsection

@section('content')
    <div class="row">
        <div class="@role('Admin|Superadmin') col-md-10 @else col @endrole">
            <ul class="nav nav-pills mb-1" id="status_filter_list">
                @unlessrole('Marketing')
                <li class="nav-item">
                    <a class="nav-link @if (!request()->input('lead_status_id')) active @endif lead_status_filter_link bg-gradient lead_type_click"
                        href="#" data-lead-status-id="" data-classes=''>
                        <span class="fw-bold">All</span>
                    </a>
                </li>
                @endunlessrole
                @foreach ($lead_statuses as $status)
                    <li class="nav-item">
                        @if(Auth::user()->hasRole('Marketing') && ($status->title == 'New' || $status->title == 'Not Suitable'))
                            @if($status->title == 'New')
                            <a class="nav-link lead_status_filter_link position-relative {{ $status->css_class }} active lead_type_click" href="#" data-lead-status-id="{{ $status->id }}"
                                data-classes='{{ $status->css_class }}'>
                            @else
                            <a class="nav-link lead_status_filter_link position-relative bg-gradient @if (request()->input('lead_status_id') == $status->id) active @endif lead_type_click"
                                href="#" data-lead-status-id="{{ $status->id }}"
                                data-classes='{{ $status->css_class }}'>
                            @endif
                                <span class="fw-bold">{{ $status->title }}</span>
                                @if ($status->title == 'New')
                                    <span
                                        class="badge rounded-pill badge-up top-0 end-0 d-flex justify-content-center align-items-center {{ $status->css_class }} bg-gradient @if (!$status->leads_count) d-none @endif custom-badge"
                                        id="newLeadCount">{{ $status->leads_count }}</span>
                                @endif
                            </a>
                        @endif
                        @if(!Auth::user()->hasRole('Marketing'))
                            <a class="nav-link lead_status_filter_link position-relative bg-gradient @if (request()->input('lead_status_id') == $status->id) active @endif lead_type_click"
                               href="#" data-lead-status-id="{{ $status->id }}"
                               data-classes='{{ $status->css_class }}'>
                                <span class="fw-bold">{{ $status->title }}</span>
                                @if ($status->title == 'New')
                                    <span
                                        class="badge rounded-pill badge-up top-0 end-0 d-flex justify-content-center align-items-center {{ $status->css_class }} bg-gradient @if (!$status->leads_count) d-none @endif custom-badge"
                                        id="newLeadCount">{{ $status->leads_count }}</span>
                                @endif
                            </a>
                        @endif
                    </li>
                @endforeach

                @role('Admin|Superadmin|Marketing')
                    <li class="nav-item">
                        <a class="nav-link lead_status_filter_link bg-gradient lead_type_click" href="#" data-lead-status-id="deleted"
                            data-classes='bg-danger'>
                            <span class="fw-bold">Deleted</span>
                        </a>
                    </li>
                @endrole
            </ul>
        </div>
        @role('Admin|Superadmin')
            <div class="col-md-2 d-flex justify-content-end align-items-start">
                <a class=" me-1" data-bs-toggle="collapse" href="#leadFilters" role="button" aria-expanded="false"
                    aria-controls="leadFilters">
                    Advanced Search
                </a>
            </div>
        @endrole
    </div>
    @role('Admin|Superadmin')
        <div class="collapse row mb-1 align-items-center {{ request()->input('created_at') ? 'show' : '' }}" id="leadFilters">
            <div class="col-lg-3 col-md-4 col-sm-6 col-12 form-group">
                <label for="filter_created_at" class="form-label">Created At</label>
                <select id="filter_created_at" class="form-select select2">
                    <option value="">All</option>
                    {{-- <option value="today">Today</option> --}}
                    <option value="week" {{ request()->input('created_at') === 'week' ? 'selected' : '' }}>This week</option>
                    <option value="month" {{ request()->input('created_at') === 'month' ? 'selected' : '' }}>This month
                    </option>
                    <option value="last_month" {{ request()->input('created_at') === 'last_month' ? 'selected' : '' }}>Last
                        month</option>
                    <option value="3_months" {{ request()->input('created_at') === '3_months' ? 'selected' : '' }}>Last 3
                        months</option>
                    <option value="year" {{ request()->input('created_at') === 'year' ? 'selected' : '' }}>Current year
                    </option>
                    <option value="custom" {{ request()->input('created_at') === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-12 form-group filter_date_range"
                @if (request()->input('created_at') != 'custom') style="display: none" @endif>
                <label class="form-label">Custom Created At Range</label>
                <div class="input-group input-daterange">
                    <input type="text" class="form-control"
                        value="{{ request()->input('created_at_start', date('d/m/Y', strtotime('first day of this month'))) }}"
                        name="created_at_start" id="created_at_start" readonly>
                    <div class="input-group-addon mx-1 my-auto">to</div>
                    <input type="text" class="form-control" value="{{ request()->input('created_at_end', date('d/m/Y')) }}"
                        name="created_at_end" id="created_at_end" readonly>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-12 form-group">
                <label for="filter_assigned_to" class="form-label">Assigned to</label>
                <select id="filter_assigned_to" class="form-select select2">
                    <option value="">All</option>
                    @foreach ($users as $assignee)
                        <option value="{{ $assignee->id }}">{{ $assignee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3 col-xl-2 form-group">
                <label for="filter_lead_source_id" class="form-label">Source</label>
                <select id="filter_lead_source_id" class="form-select form-select-sm select2 select2-size-sm">
                    <option value="" @if (!request()->input('filter_lead_source_id')) selected @endif >All</option>
                    @foreach ($lead_sources as $source)
                        <option value="{{ $source->id }}" @if (request()->input('filter_lead_source_id') == $source->id) selected @endif >{{ $source->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-12 form-group mt-1 mt-lg-0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="filter_show_deleted"
                        @if (Auth::user()->show_deleted_leads) checked @endif>
                    <label class="form-check-label" for="filter_show_deleted">
                        Show Deleted
                    </label>
                </div>
            </div>
        </div>
    @endrole
    <section class="app-user-list">
        <!-- list and filter start -->
        <div class="card">
            <div class="card-datatable table-responsive pt-0 leads-table-wrapper">
                <table class="user-list-table table lead_tabel">
                    <thead class="table-light">
                        <tr>
                            <th>Source</th>
                            <th>Name</th>
                            @if(!Auth::user()->hasRole('Marketing'))
                                <th>Email</th>
                                <th>Phone</th>
                            @else
                                <th></th>
                                <th></th>
                            @endif
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Created Date</th>
                            <th>Updated Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- Modal to add new lead starts-->
            <div class="modal modal-slide-in new-user-modal fade" id="addLeadModal">
                <div class="modal-dialog">
                    <form class="add-new-user modal-content pt-0" action="{{ route('leads.store') }}" method="POST"
                        enctype="multipart/form-data" id="addLeadForm">
                        @csrf
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                        <div class="modal-header mb-1">
                            <h5 class="modal-title" id="exampleModalLabel">Add Lead</h5>
                        </div>
                        <div class="modal-body flex-grow-1">
                            <div class="form-group">
                                <label class="form-label" for="firstname">First Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control dt-full-name" id="firstname" name="firstname"
                                    value="{{ old('firstname') }}" />
                                @error('firstname')
                                    <span id="firstname-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="lastname">Last Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control dt-full-name" id="lastname" name="lastname"
                                    value="{{ old('lastname') }}" />
                                @error('lastname')
                                    <span id="lastname-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            {{-- @if(!Auth::user()->hasRole('Marketing')) --}}
                            <div class="form-group">
                                <label class="form-label" for="basic-icon-default-contact">Phone Number</label>
                                <input type="text" id="basic-icon-default-contact" class="form-control dt-contact"
                                    name="mobile" value="{{ old('mobile') }}" />
                                @error('mobile')
                                    <span id="mobile-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="basic-icon-default-email-create">Email Address </label>
                                <select name="email[]" id="basic-icon-default-email-create" class="form-control dt-email" multiple='multiple'></select>
                                <span id="email-errors" class="text-danger"></span>
                            </div>
                            {{-- @endif --}}
                            <div class="form-group">
                                <label class="form-label" for="country_select">Country </label>
                                <select id="country_select" class="select2 form-select" name="country_id">
                                    <option value="" selected>Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            {{ $country->id == old('country_id', 226) ? 'selected' : '' }}>
                                            {{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                    <span id="country_id-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="basic-icon-default-uname">Requirements</label>
                                <textarea name="requirement" id="requirements" class="form-control" rows="4">{{ old('requirement') }}</textarea>
                                @error('requirement')
                                    <span id="requirement-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="prj_budget">Project Budget</label>
                                <div class="input-group project_budget_input_grp">
                                    <select id="currency_select" class=" form-select" name="currency_id">
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}"
                                                {{ $currency->id == old('currency_id', 2) ? 'selected' : '' }}>
                                                {{ $currency->symbol }}</option>
                                        @endforeach
                                    </select>
                                    <select id="prj_budget" class="form-select" name="prj_budget">
                                        <option value="">Select Project Budget</option>
                                        <option value="0-500">0 to 500</option>
                                        <option value="500-2500">500 to 2500</option>
                                        <option value="2500-5000">2500 to 5000</option>
                                        <option value="5000">5000+</option>
                                    </select>
                                </div>
                                @error('prj_budget')
                                    <span id="prj_budget-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="lead_source_select">Lead Source <span
                                        class="text-danger">*</span></label>
                                <select id="lead_source_select" class="select2 form-select" name="lead_source_id">
                                    <option value="" selected disabled></option>
                                    @foreach ($lead_sources as $lead_source)
                                        <option value="{{ $lead_source->id }}" {{-- {{ $lead_source->id == old('lead_source_id', 5) ? 'selected' : '' }} --}}>
                                            {{ $lead_source->title }}</option>
                                    @endforeach
                                </select>
                                @error('lead_source_id')
                                    <span id="lead_source_id-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="lead_status">Lead Status <span
                                        class="text-danger">*</span></label>
                                <select id="lead_status" class="select2 form-select" name="lead_status_id">
                                    <option value="" selected disabled></option>
                                    @foreach ($lead_statuses as $lead_status)
                                        <option value="{{ $lead_status->id }}"
                                            {{ $lead_status->id == old('lead_status_id', 1) ? 'selected' : '' }}>
                                            {{ $lead_status->title }}</option>
                                    @endforeach
                                </select>
                                @error('lead_status_id')
                                    <span id="lead_status_id-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="assigned_to">Assigned to <span
                                        class="text-danger">*</span></label>
                                <select id="assigned_to" class="select2 form-select" name="assigned_to">
                                    <option value="" selected disabled></option>
                                    @foreach ($users as $user)
                                        @if(!$user->hasRole('Admin|Superadmin|Marketing'))
                                        <option value="{{ $user->id }}"
                                            {{ $user->id == old('assigned_to', Auth::id()) ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <span id="assigned_to-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="attachments">Attachments</label>
                                <input type="file" class="form-control" name="attachments[]" id="attachments"
                                    multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.tiff">
                                @error('attachments')
                                    <span id="attachments-error" class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mt-1">
                                <button type="submit" class="btn btn-primary me-1 data-submit"
                                    id="addLeadSubmitBtn">Submit</button>
                                <button type="reset" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal to add new lead Ends-->
            <!-- Modal to edit lead starts-->
            <div class="modal modal-slide-in new-user-modal fade" id="editLeadModal">
                <div class="modal-dialog">
                    <form class="modal-content pt-0" action="" method="POST" id="editLeadForm"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                        <div class="modal-header mb-1">
                            <h5 class="modal-title" id="exampleModalLabel">Edit Lead</h5>
                        </div>
                        <input type="hidden" name="id" value="" id="edit_id">
                        <div class="modal-body flex-grow-1">
                            <div class="form-group">
                                <label class="form-label" for="edit_firstname">First Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control dt-full-name" id="edit_firstname"
                                    name="firstname" value="" />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="edit_lastname">Last name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control dt-full-name" id="edit_lastname"
                                    name="lastname" value="" />
                            </div>
                            {{-- @if(!Auth::user()->hasRole('Marketing')) --}}
                                <div class="form-group">
                                    <label class="form-label" for="edit_mobile">Phone Number</label>
                                    <input type="text" id="edit_mobile" class="form-control dt-contact" name="mobile"
                                        value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="edit_email">Email Address </label>
                                    <select name="email[]" id="edit_email" class="form-control dt-email" multiple='multiple'></select>
                                    @error('email')
                                        <span id="email-error" class="error">{{ $message }}</span>
                                    @enderror
                                </div>
                            {{-- @endif --}}
                            <div class="form-group">
                                <label class="form-label" for="edit_country_id">Country</label>
                                <select id="edit_country_id" class="select2 form-select" name="country_id">
                                    <option value="">Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}">
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="edit_requirement">Requirements</label>
                                <textarea name="requirement" id="edit_requirement" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="edit_prj_budget">Project Budget</label>
                                <div class="input-group edit_project_budget_input_group">
                                    <select id="edit_currency_id" class="form-select" name="currency_id">
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}"
                                                {{ $currency->id == old('currency_id', 2) ? 'selected' : '' }}>
                                                {{ $currency->symbol }}</option>
                                        @endforeach
                                    </select>
                                    <select id="edit_prj_budget" class="form-select" name="prj_budget">
                                        <option value="">Select project budget</option>
                                        <option value="0-500">0 to 500</option>
                                        <option value="500-2500">500 to 2500</option>
                                        <option value="2500-5000">2500 to 5000</option>
                                        <option value="5000">5000+</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="edit_lead_source_id">Lead Source <span
                                        class="text-danger">*</span></label>
                                <select id="edit_lead_source_id" class="select2 form-select" name="lead_source_id">
                                    <option value="" selected disabled></option>
                                    @foreach ($lead_sources as $lead_source)
                                        <option value="{{ $lead_source->id }}">
                                            {{ $lead_source->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="edit_lead_status_id">Lead Status <span
                                        class="text-danger">*</span></label>
                                <select id="edit_lead_status_id" class="select2 form-select" name="lead_status_id">
                                    <option value="" selected disabled></option>
                                    @foreach ($lead_statuses as $lead_status)
                                        <option value="{{ $lead_status->id }}">
                                            {{ $lead_status->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="edit_assigned_to">Assigned to <span
                                        class="text-danger">*</span></label>
                                <select id="edit_assigned_to" class="select2 form-select" name="assigned_to">
                                    <option value="" selected disabled></option>
                                    @foreach ($users as $user)
                                        @if(!$user->hasRole('Admin|Superadmin|Marketing'))
                                        <option value="{{ $user->id }}">
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="edit_attachments">Add Attachments</label>
                                <input type="file" name="attachments[]" id="edit_attachments" class="form-control"
                                    multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.tiff">
                            </div>
                            <div class="mt-1">
                                <button type="submit" class="btn btn-primary me-1 data-submit"
                                    id="editLeadSubmitBtn">Submit</button>
                                <button type="reset" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal to edit lead Ends-->


            <!-- lead detail Modal -->
            <div class="modal fade" id="leadDetailModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-edit-user">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Lead Details</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                            <input type="hidden" name="lead_id">
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="table-active" width="17%">First Name</td>
                                            <td width="33%"><span id="detail_firstname"></span></td>
                                            <td class="table-active" width="17%">Last Name</td>
                                            <td width="33%"><span id="detail_lastname"></span></td>
                                        </tr>
                                        @if(!Auth::user()->hasRole('Marketing'))
                                        <tr>
                                            <td class="table-active" width="17%">Email</td>
                                            <td width="33%"><span id="detail_email" style="overflow-wrap:anywhere;"></span></td>
                                            <td class="table-active" width="17%">Phone Number</td>
                                            <td width="33%"><span id="detail_mobile"></span></td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="table-active" width="17%">Country</td>
                                            <td width="33%"><span id="detail_country"></span></td>
                                            <td class="table-active" width="17%">Project Budget</td>
                                            <td width="33%"><span id="detail_project_budget"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="table-active" width="17%">Lead Source</td>
                                            <td width="33%"><span id="detail_lead_source"></span></td>
                                            <td class="table-active" width="17%">Lead Status</td>
                                            <td width="33%"><span id="detail_lead_status"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="table-active" width="17%">Assigned to</td>
                                            <td width="33%"><span id="detail_assignee"></span></td>
                                            <td class="table-active" width="17%">Attachments</td>
                                            <td width="33%"><span id="detail_attachments"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="table-active" width="17%">Created At</td>
                                            <td width="33%"><span id="detail_created_at"></span></td>
                                            <td class="table-active" width="17%">Updated At</td>
                                            <td width="33%"><span id="detail_updated_at"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="table-active" width="17%">Requirements</td>
                                            <td colspan="3"><span id="detail_requirement"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ lead detail Modal -->

            <!-- lead notes Modal -->
            <div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered notes-modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Lead Notes</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <input type="hidden" name="lead_id" id="lead_id">
                        <div class="modal-body bg-light">
                            <form class="form form-horizontal" id="addNoteForm" method="POST">
                                @csrf
                                <input type="hidden" name="_method" id="notes_method" value="">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-sm-1 pe-0">
                                                <label for="notes">
                                                    Note <span class="text-danger">*</span>
                                                </label>
                                            </div>
                                            <div class="col-sm-11">
                                                <div class="mb-1">
                                                    <textarea class="form-control" id="notes" name="note" rows="4"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-11 offset-sm-1">
                                        <button type="submit"
                                            class="btn btn-primary me-1 waves-effect waves-float waves-light"
                                            id="note_submit_btn">Submit</button>
                                        <button type="reset" class="btn btn-outline-secondary waves-effect"
                                            id="note_reset_btn">Reset</button>
                                    </div>
                                </div>
                            </form>
                            <hr>
                            <h3>Notes history</h3>
                            <div id="lead_notes_list"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ lead notes Modal -->

            @include('leads.modals.Congratulations-modal')

            @include('leads.modals.export-modal')

            @include('leads.modals.marketing-mail')
        </div>
        <!-- list and filter end -->
    </section>

    @include('follow_ups.modals.add')

    {{-- @include('follow_ups.modals.call') --}}

    {{-- @include('follow_ups.modals.email') --}}

@endsection

@section('page-js')
    <script src="{{ asset('app-assets/js/scripts/forms/form-quill-editor.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/js/custom/flatpickr.js') }}"></script>
@endsection

@section('custom-js')
    <script>
        const followUpTypes = @json(App\Enums\FollowUpType::all());
        const isSuper = @json(auth()->user()->hasRole('Superadmin'));
        const isMarketing = @json(auth()->user()->hasRole('Marketing'));
        // const followUpStatuses = @json(App\Enums\FollowUpStatus::all());
        // const authuser = @json(auth()->user());
    </script>
    <script src="{{ asset('app-assets/js/pages/leads/index.js?v=' . config('versions.js')) }}" defer ></script>

    @unlessrole('Admin|Superadmin|Marketing')
        <script>
            if (leadsTable) {
                leadsTable.column(5).visible(false);
            }
        </script>
    @endunlessrole
@endsection
