<input type="hidden" id="marketing_mail_reminder_status" name="marketing_mail_reminder_status">
<input type="hidden" id="leadid" name="leadid">
<!-- Marketing Mail Status Change Modal -->
<div class="modal fade" id="marketing_mail_enable_model" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Marketing Mail Reminder</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <i class="alt_circle" data-feather="alert-circle"></i>
                                <h4> Are you sure? </h4>
                                <label for="enable_mail" class="lead_message">  </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 text-center">
                        <button type="submit" class="btn btn-primary me-1 waves-effect waves-float waves-light" id="mail_status_change">Yes</button>
                        <button type="button" class="btn btn-outline-secondary" id="marketing_mail_enable_close">cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
