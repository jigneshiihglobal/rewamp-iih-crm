<!-- Modal to edit client starts-->
<div class="modal modal-slide-in edit-client-modal fade" id="editClientModal">
    <div class="modal-dialog">
        <form class="edit-client modal-content pt-0" id="editClientForm">
            <input type="hidden" name="client_id" value="">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Edit Customer</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="form-group">
                    <label class="form-label" for="edit_name">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control dt-full-name" id="edit_name" name="name"
                        value="{{ old('name') }}" />
                    @error('name')
                        <span id="name-error" class="error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_email">Email Address <span class="text-danger">*</span></label>
                    <select name="email[]" id="edit_email" class="form-control dt-email" multiple='multiple'></select>
                    @error('email')
                        <span id="email-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_phone">Phone number</label>
                    <input type="text" id="edit_phone" class="form-control dt-phone" name="phone"
                        value="{{ old('phone') }}" />
                    @error('phone')
                        <span id="phone-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_address_line_1">Address line 1</label>
                    <textarea name="address_line_1" id="edit_address_line_1" class="form-control">{{ old('address_line_1') }}</textarea>
                    @error('address_line_1')
                        <span id="address_line_1-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_address_line_2">Address line 2</label>
                    <textarea name="address_line_2" id="edit_address_line_2" class="form-control">{{ old('address_line_2') }}</textarea>
                    @error('address_line_2')
                        <span id="address_line_2-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_city">City</label>
                    <input type="text" id="edit_city" class="form-control" name="city"
                        value="{{ old('city') }}" />
                    @error('city')
                        <span id="city-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_zip_code">Post Code</label>
                    <input type="text" id="edit_zip_code" class="form-control dt-zip_code" name="zip_code"
                        value="{{ old('zip_code') }}" />
                    @error('zip_code')
                        <span id="zip_code-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_country_id">Country</label>
                    <select id="edit_country_id" class="select2 form-select" name="country_id">
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
                    <label class="form-label" for="edit_vat_number">Vat Code</label>
                    <input type="text" id="edit_vat_number" class="form-control dt-vat_number" name="vat_number"
                        value="{{ old('vat_number') }}" />
                    @error('vat_number')
                        <span id="vat_number-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                @if(Auth::user()->hasRole(['Admin','Superadmin','Marketing']))
                    <div class="form-group">
                        <label class="form-label" for="edit_sales_user_id">Client access</label>
                        <select id="edit_sales_user_id" class="select2 form-select" name="edit_sales_user_id[]" multiple>
                            @if(isset($users) && !empty($users))
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->email }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                @endif
                @workspace('iih-global')
                <div class="form-group mt-75">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_plant_a_tree" name="plant_a_tree"
                            aria-describedby="edit_plant_a_tree_checkbox_msg" value="1" />
                        <label class="form-check-label" for="edit_plant_a_tree">Plant a tree</label>
                    </div>
                </div>
                @endworkspace
                <div class="mt-1">
                    <button type="submit" class="btn btn-primary me-1 data-submit"
                        id="editClientSubmitBtn">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Modal to edit client Ends-->
