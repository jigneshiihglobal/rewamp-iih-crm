<div class="modal fade text-start" id="invoicePreviewModal" tabindex="-1" aria-labelledby="invoicePreviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="invoicePreviewModalLabel">Invoice Preview</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="height: 80vh">
                @php
                    if(Route::is('credit_notes.show')) {
                        $src = isset($credit_note) ? route('credit_notes.preview', ['credit_note' => $credit_note->encrypted_id, 'v' => config('versions.pdf')]) : '';
                    }
                    else if(Route::is('clients.show')){
                    $src = isset($clients) ? route('invoices.preview', ['invoice' => isset($invoice[0]->encrypted_id)?$invoice[0]->encrypted_id:0, 'v' => config('versions.pdf')]) : '';
                    } else {
                        $src = isset($invoice) ? route('invoices.preview', ['invoice' => $invoice->encrypted_id, 'v' => config('versions.pdf')]) : '';
                    }
                @endphp
                <iframe src="{{ $src }}" frameborder="0" height="100%"
                    width="100%" seamless id="invoicePreviewIFrame"></iframe>
            </div>
        </div>
    </div>
</div>
