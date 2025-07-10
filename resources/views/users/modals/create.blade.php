<!-- Modal to add new user starts-->
<div class="modal modal-slide-in add-user-modal fade" id="addUserModal">
    <div class="modal-dialog">
        <form class="add-user modal-content pt-0" action="{{ route('users.store') }}" method="POST" id="addUserForm">
            @csrf
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name"
                        value="{{ old('first_name') }}" />
                    @error('first_name')
                        <span id="first_name-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name"
                        value="{{ old('last_name') }}" />
                    @error('last_name')
                        <span id="last_name-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                    <input type="text" id="email" class="form-control" name="email"
                        value="{{ old('email') }}" />
                    @error('email')
                        <span id="email-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="gender">Gender <span class="text-danger">*</span></label>
                    <select id="gender" class="form-select select2" name="gender">
                        <option value="male" {{ 'male' == old('gender') ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ 'female' == old('gender') ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ 'other' == old('gender') ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <span id="gender-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                    <select id="role" class="form-select select2" name="role">
                        @unlessrole('Superadmin')
                            @php
                                $roles = $roles->except(3);
                            @endphp
                        @endunlessrole
                        @foreach ($roles as $role)
                            @if($role->name != 'Admin')
                            <option value="{{ $role->name }}"
                                {{ $role->name == old('role', 'User') ? 'selected' : '' }}>
                                {{ $role->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('role')
                        <span id="role-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                @role('Superadmin')
                <div class="form-group">
                    <label for="workspaces" class="form-label">Workspaces</label>
                    <div class="form-group row custom-options-checkable g-1 mb-1">
                        @foreach ($workspaces as $workspace)
                        <div class="col-md-4">
                            <input class="custom-option-item-check" type="checkbox" name="workspaces[]" id="{{$workspace->encrypted_id}}" value="{{$workspace->id}}" @if ($workspace->id == auth()->user()->workspace_id) checked @endif />
                            <label class="custom-option-item text-center p-1" for="{{$workspace->encrypted_id}}">
                                @if ($workspace->slug == 'iih-global')
                                    <div class="avatar bg-light-warning p-1">
                                        <img src="{{ asset('app-assets/images/logo/icon-logo.svg') }}" alt="IIH Global" class="avatar-content" >
                                    </div>
                                @elseif ($workspace->slug == 'shalin-designs')
                                    <div class="avatar bg-light-warning p-1">
                                        <img src="{{ asset('shalin-designs/img/icon-logo.png') }}" alt="Shalin Designs" class="avatar-content" >
                                    </div>
                                @else
                                <i data-feather="briefcase" class="font-large-1 mb-75"></i>
                                @endif                                <span class="custom-option-item-title h4 d-block">{{$workspace->name}}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endrole
                <div class="form-group">
                    <label class="form-label" for="timezone">Timezone <span class="text-danger">*</span></label>
                    <select id="timezone" class="form-select select2" name="timezone">
                        @foreach (timezone_identifiers_list() as $timezone)
                            <option value="{{ $timezone }}"
                                {{ $timezone == old('timezone', 'Asia/Kolkata') ? 'selected' : '' }}>
                                {{ $timezone }}</option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <span id="timezone-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group" style="margin-top: 14px">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                            value="1" aria-describedby="is_active_checkbox_msg" checked />
                        <label class="form-check-label" for="is_active">Is Active (Click on
                            checkbox to activate user)</label>
                    </div>
                </div>
                <div class="mt-1">
                    <button type="submit" class="btn btn-primary me-1 data-submit" id="addUserSubmitBtn">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Modal to add new user Ends-->
