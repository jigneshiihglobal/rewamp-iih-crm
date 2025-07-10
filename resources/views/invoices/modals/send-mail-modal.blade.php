<div class="modal fade text-start" id="sendMailModal" tabindex="-1" aria-labelledby="sendMailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="sendMailForm" method="POST" action="{{ route('invoices.send-mail', $invoice->encrypted_id) }}">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="sendMailModalLabel">Send Mail</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="to" class="form-label">To <span class="text-danger">*</span></label>
                        <select name="to[]" id="to" class="form-select select2" multiple='multiple'></select>
                    </div>
                    <div class="form-group">
                        <label for="bcc" class="form-label">BCC <span class="text-danger">*</span></label>
                        <select name="bcc[]" id="bcc" class="form-select select2" multiple='multiple'></select>
                    </div>
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="subject" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contentQuill" class="form-label">Content <span class="text-danger">*</span></label>
                        <div id="contentQuill"></div>
                        <input type="hidden" name="content" id="content">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="sendMailSubmitBtn">Send Mail</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
