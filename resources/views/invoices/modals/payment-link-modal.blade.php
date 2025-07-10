<div class="modal fade text-start" id="paymentLinkModal" tabindex="-1" aria-labelledby="paymentLinkModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="paymentLinkModalLabel">Add Payment Link</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentLinkForm" class="form form-horizontal"
                    action="{{ route('invoices.payments.link.store', $invoice->encrypted_id) }}">
                    @csrf
                    <input type="hidden" name="invoice_id" value="">
                    <div class="row">
                        <div class="col-12">
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <label for="payment_link" class="col-form-label">Payment Link</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control form-control-sm" name="payment_link" id="payment_link" value="{{old('payment_link', $invoice->payment_link)}}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary btn-sm" id="paymentLinkSubmitBtn">Save</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
