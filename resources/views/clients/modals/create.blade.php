<!-- Modal to add new client starts-->
<div class="modal modal-slide-in new-client-modal fade" id="addClientModal">
    <div class="modal-dialog">
        <form class="modal-content pt-0" action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data"
            id="addClientForm">
            @csrf
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Add Customer</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="form-group">
                    <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control dt-full-name" id="name" name="name"
                        value="{{ old('name') }}" />
                    @error('name')
                        <span id="name-error" class="error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-email">Email Address <span
                            class="text-danger">*</span></label>
                    <select name="email[]" id="basic-icon-default-email" class="form-control dt-email" multiple='multiple'></select>
                    @error('email')
                        <span id="email-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone number</label>
                    <input type="text" id="phone" class="form-control dt-phone" name="phone"
                        value="{{ old('phone') }}" />
                    @error('phone')
                        <span id="phone-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="address_line_1">Address line 1</label>
                    <textarea name="address_line_1" id="address_line_1" class="form-control">{{ old('address_line_1') }}</textarea>
                    @error('address_line_1')
                        <span id="address_line_1-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="address_line_2">Address line 2</label>
                    <textarea name="address_line_2" id="address_line_2" class="form-control">{{ old('address_line_2') }}</textarea>
                    @error('address_line_2')
                        <span id="address_line_2-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="city">City</label>
                    <input type="text" id="city" class="form-control" name="city"
                        value="{{ old('city') }}" />
                    @error('city')
                        <span id="city-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="zip_code">Post Code</label>
                    <input type="text" id="zip_code" class="form-control dt-zip_code" name="zip_code"
                        value="{{ old('zip_code') }}" />
                    @error('zip_code')
                        <span id="zip_code-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="country_id">Country</label>
                    <select id="country_id" class="select2 form-select" name="country_id">
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
                    <label class="form-label" for="vat_number">Vat Number</label>
                    <input type="text" id="vat_number" class="form-control dt-vat_number" name="vat_number"
                        value="{{ old('vat_number') }}" />
                    @error('vat_number')
                        <span id="vat_number-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                @if(Auth::user()->hasRole(['Admin','Superadmin','Marketing']))
                    <div class="form-group">
                        <label class="form-label" for="sales_user_id">Client access</label>
                        <select id="sales_user_id" class="select2 form-select" name="sales_user_id[]" multiple>
                            @if(isset($users) && !empty($users))
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->email }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                @endif
                @workspace('iih-global')
                    @if(Auth::user()->hasRole(['Admin','Superadmin','Marketing']))
                        <div class="form-group mt-75">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="plant_a_tree" name="plant_a_tree"
                                    aria-describedby="plant_a_tree_checkbox_msg" value="1" />
                                <label class="form-check-label" for="plant_a_tree">Plant a tree</label>
                            </div>
                        </div>
                    @endif
                @endworkspace
                <div class="mt-1">
                    <button type="submit" class="btn btn-primary me-1 data-submit"
                        id="addClientSubmitBtn">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Modal to add new client Ends-->
