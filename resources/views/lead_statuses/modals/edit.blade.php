<!-- Modal to edit lead status starts-->
<div class="modal edit-lead-status-modal fade" id="editLeadStatusModal">
    <div class="modal-dialog">
        <form class="edit-lead-status modal-content pt-0" id="editLeadStatusForm">
            <input type="hidden" name="lead_status_id" id="edit_lead_status_id" >
            <div class="modal-header mb-1">
                <h5 class="modal-title">Edit Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="form-group">
                    <label class="form-label" for="edit_title">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_title" name="title" />
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_css_class">Color <span class="text-danger">*</span></label>
                    <select name="css_class" id="edit_css_class" class="form-select select2">
                        <option value="">Select color</option>
                        @foreach (\App\Helpers\CSSHelper::$color_class_map as $color => $class)
                            <option value="{{ $class }}">{{$color}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-1">
                    <button type="submit" class="btn btn-primary me-1 data-submit" id="editLeadStatusSubmitBtn">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Modal to edit lead status Ends-->
