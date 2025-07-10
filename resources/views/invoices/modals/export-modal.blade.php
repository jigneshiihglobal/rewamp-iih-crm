<div class="modal fade text-start" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exportModalLabel">Export Invoices</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm" class="form form-horizontal"
                    action="{{ route('invoices.export') }}" method="POST">
                    @csrf
                    <div id="exportHiddenInputs">
                        <input type="hidden" name="filter_created_at" >
                        <input type="hidden" name="filter_created_at_range" >
                        <input type="hidden" name="filter_payment_source_id" >
                        <input type="hidden" name="filter_client_id" class="cls_client_id">
                        <input type="hidden" name="filter_company_id" >
                        <input type="hidden" name="filter_payment" >
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h4>Applied filters: </h4>
                            <ul id="appliedExportFilters" class="list-unstyled" ></ul>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm me-1"
                                id="exportSubmitBtn">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade text-start" id="exportBankModal" tabindex="-1" aria-labelledby="exportBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exportBankModalLabel">Export Invoices</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm" class="form form-horizontal"
                    action="{{ route('invoices.bank_export') }}" method="POST">
                    @csrf
                    <div id="exportHiddenInputs">
                        <input type="hidden" name="filter_created_at" >
                        <input type="hidden" name="filter_created_at_range" >
                        <input type="hidden" name="filter_payment_source_id" >
                        <input type="hidden" name="filter_client_id" class="cls_client_id">
                        <input type="hidden" name="filter_company_id" >
                        <input type="hidden" name="filter_payment" >
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h4>Applied filters: </h4>
                            <ul id="appliedExportFilters" class="list-unstyled" ></ul>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm me-1"
                                id="exportBankSubmitBtn">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade text-start" id="paymentReceiptExportModal" tabindex="-1" aria-labelledby="paymentReceiptExportModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="paymentReceiptExportModalLabel">Invoices Payment Receipt Export</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="paymentReceiptClose"></button>
            </div>
            <div class="modal-body">
                <form id="paymentReceiptExportForm" class="form form-horizontal"
                      action="{{ route('invoices.payment_receipt_export') }}" method="POST">
                    @csrf
                    <div id="paymentReceiptExportHiddenInputs">
                        <input type="hidden" name="export_from_date" >
                        <input type="hidden" name="export_to_date" >
                        <input type="hidden" name="export_payment_source_id" >
                        <input type="hidden" name="export_client_id">
                        <input type="hidden" name="export_company_id" >
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h4>Below Filter Applied: </h4>
                            <ul id="appliedReceiptExportFilters" class="list-unstyled" ></ul>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm me-1"
                                    id="paymentReceiptExportSubmitBtn">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"
                                    aria-label="Close" id="paymentReceiptClose">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
