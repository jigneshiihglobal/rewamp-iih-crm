<div class="modal fade text-start" id="addFollowUpCallModal" tabindex="-1" aria-labelledby="addFollowUpCallModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addFollowUpCallForm">
                <input type="hidden" name="lead_id">
                <input type="hidden" name="type" value="{{ App\Enums\FollowUpType::CALL }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFollowUpCallModalLabel">Add Call Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 form-group">
                            <label for="follow_up_at" class="form-label">Follow up at <span
                                    class="text-danger">*</span></label>
                            <div>
                                <input type="text" name="follow_up_at" id="follow_up_at"
                                    class="form-control form-control-sm" style="width: 100%">
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
                    <button type="submit" class="btn btn-sm btn-primary" id="addFollowUpCallSubmitBtn">Add</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
