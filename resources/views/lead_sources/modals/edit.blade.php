<!-- Modal to edit lead source starts-->
<div class="modal edit-lead-source-modal fade" id="editLeadSourceModal">
    <div class="modal-dialog">
        <form class="edit-lead-source modal-content pt-0" id="editLeadSourceForm">
            <input type="hidden" name="lead_source_id" id="edit_lead_source_id">
            <div class="modal-header mb-1">
                <h5 class="modal-title">Edit Lead Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="form-group">
                    <label class="form-label" for="edit_title">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_title" name="title" />
                </div>
                <div class="mt-1">
                    <button type="submit" class="btn btn-primary me-1 data-submit"
                        id="editLeadSourceSubmitBtn">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Modal to edit lead source Ends-->
