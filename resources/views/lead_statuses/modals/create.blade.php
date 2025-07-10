<!-- Modal to add new lead status starts-->
<div class="modal add-lead-status-modal fade" id="addLeadStatusModal">
    <div class="modal-dialog">
        <form class="add-lead-status modal-content pt-0" action="{{ route('lead_statuses.store') }}" method="POST" id="addLeadStatusForm">
            @csrf
            <div class="modal-header mb-1">
                <h5 class="modal-title">Add Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="form-group">
                    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title"
                        value="{{ old('title') }}" />
                    @error('title')
                        <span id="title-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="css_class">Color <span class="text-danger">*</span></label>
                    <select name="css_class" id="css_class" class="form-select select2">
                        <option value="">Select color</option>
                        @foreach (\App\Helpers\CSSHelper::$color_class_map as $color => $class)
                            <option value="{{ $class }}">{{$color}}</option>
                        @endforeach
                    </select>
                    @error('css_class')
                        <span id="css_class-error" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-1">
                    <button type="submit" class="btn btn-primary me-1 data-submit" id="addLeadStatusSubmitBtn">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Modal to add new lead status Ends-->
