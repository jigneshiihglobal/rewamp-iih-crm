<input type="hidden" id="remindervalue" name="remindervalue">
<input type="hidden" id="invoiceid" name="invoiceid">
<!-- invoices Payment Reminder enable Modal -->
<div class="modal fade" id="payment_reminder_enable_model" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Payment Reminder</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <i class="alt_circle" data-feather="alert-circle"></i>
                                <h4> Are you sure? </h4>
                                <label for="enable_invoice" class="invoice_message">  </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 text-center">
                        <button type="submit" class="btn btn-primary me-1 waves-effect waves-float waves-light" id="payment_reminder">Yes</button>
                        <button type="button" class="btn btn-outline-secondary" id="payment_reminder_enable_close">cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- invoices already paid Modal -->
<div class="modal fade" id="invoice_already_paid_model" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Payment Reminder</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <label for="enable_invoice"> Invoice already paid so do not enable/disable payment reminder! </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 text-center">
                        <button type="button" class="btn btn-outline-secondary" id="invoice_already_paid_close">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
