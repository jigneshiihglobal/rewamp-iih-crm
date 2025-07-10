<div class="modal fade text-start" id="addFollowUpEmailModal" tabindex="-1" aria-labelledby="addFollowUpEmailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="addFollowUpEmailForm">
                <input type="hidden" name="lead_id">
                <input type="hidden" name="type" value="{{ App\Enums\FollowUpType::EMAIL }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFollowUpEmailModalLabel">Add Follow Up Mail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-6 form-group">
                            <label for="to" class="form-label">To <span class="text-danger">*</span></label>
                            <select name="to[]" id="to" class="form-select select2 select2-size-sm"
                                multiple='multiple'></select>
                        </div>
                        <div class="col-12 col-md-6 form-group">
                            <label for="bcc" class="form-label">BCC <span class="text-danger">*</span></label>
                            <select name="bcc[]" id="bcc" class="form-select select2 select2-size-sm"
                                multiple='multiple'></select>
                        </div>
                        <div class="col-12 col-md-6 form-group">
                            <label for="follow_up_at" class="form-label">Follow up at <span
                                    class="text-danger">*</span></label>
                            <div>
                                <input type="text" name="follow_up_at" id="follow_up_at"
                                    class="form-control form-control-sm" style="width: 100%">
                            </div>
                        </div>
                        <div class="col-12 form-group">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 form-group">
                            <label for="contentQuill" class="form-label">Content <span
                                    class="text-danger">*</span></label>
                            <div id="contentQuill"></div>
                            <input type="hidden" name="content" id="content" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary" id="addFollowUpEmailSubmitBtn">Add</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
