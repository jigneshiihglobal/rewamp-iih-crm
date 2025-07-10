<div class="modal fade text-start" id="createNewInvoiceModal" tabindex="-1" aria-labelledby="createNewInvoiceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createNewInvoiceModalLabel">Create Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row custom-options-checkable g-1">
                    <a href="{{ route('invoices.one-off.create') }}" class="col-md-6 form-group">
                        <label class="custom-option-item bg-danger text-center text-center p-1" for="createNewOneOffBtn">
                            <i data-feather="file-text" class="font-large-1 mb-75"></i>
                            <span class="custom-option-item-title h4 d-block">One-off</span>
                        </label>
                    </a>
                    <a href="{{ route('invoices.subscription.create') }}" class="col-md-6 form-group">
                        <label class="custom-option-item bg-warning text-center p-1" for="createNewSubBtn">
                            <i data-feather="repeat" class="font-large-1 mb-75"></i>
                            <span class="custom-option-item-title h4 d-block">Subscription</span>
                        </label>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
