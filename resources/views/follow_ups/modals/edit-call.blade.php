<div class="modal fade text-start" id="editFollowUpCallModal" tabindex="-1" aria-labelledby="editFollowUpCallModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editFollowUpCallForm">
                <input type="hidden" name="id">
                <input type="hidden" name="type" value="call">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editFollowUpCallModalLabel">Edit Follow Up Call Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-12 form-group">
                            <label for="follow_up_date" class="form-label">Follow up date <span
                                    class="text-danger">*</span></label>
                            <div>
                                <input type="text" name="follow_up_date" id="follow_up_date"
                                    class="form-control form-control-sm" style="width: 100%;">
                            </div>
                        </div>
                        <div class="col-md-6 col-12 form-group">
                            <label for="follow_up_time" class="form-label">Follow up time <span
                                    class="text-danger">*</span></label>
                            <div>
                                <input type="text" name="follow_up_time" id="follow_up_time"
                                    class="form-control form-control-sm" style="width: 100%;">
                            </div>
                        </div>
                        <div class="col-12 form-group">
                            <label for="sales_person_phone" class="form-label">Phone Number <span
                                    class="text-danger">*</span></label>
                            <select name="sales_person_phone[]" id="sales_person_phone"
                                class="form-select select2 select2-size-sm" multiple='multiple'></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary" id="editFollowUpCallSubmitBtn">Save</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
