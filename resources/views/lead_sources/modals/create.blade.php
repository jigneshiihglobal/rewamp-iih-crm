<!-- Modal to add new lead source starts-->
<div class="modal add-lead-source-modal fade" id="addLeadSourceModal">
    <div class="modal-dialog">
        <form class="add-lead-source modal-content pt-0" action="{{ route('lead_sources.store') }}" method="POST" id="addLeadSourceForm">
            @csrf
            <div class="modal-header mb-1">
                <h5 class="modal-title">Add Lead Source</h5>
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
                <div class="mt-1">
                    <button type="submit" class="btn btn-primary me-1 data-submit" id="addLeadSourceSubmitBtn">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Modal to add new lead source Ends-->
