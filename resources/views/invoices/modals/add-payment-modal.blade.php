<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 640px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentLabel">Payment Receipts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between my-1">
                    <div>
                        <h6>Add Payment Receipt</h6>
                    </div>
                    <div>
                        <label>Due Amount</label>
                        <span class="badge bg-light-danger rounded-pill due_amount"></span>
                    </div>
                </div>
                <form id="addPaymentForm" class="form form-horizontal">
                    <input type="hidden" name="invoice_id" value="">
                    <div class="row">
                        <div class="col-12 mb-1">
                            <div class="row">
                                <div class="col-sm-3">
                                    <label for="amount" class="col-form-label">Amount <span class="text-danger">*</span></label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control form-control-sm text-end" name="amount"
                                        id="amount" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <label for="paid_at" class="col-form-label">Paid At <span class="text-danger">*</span></label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm text-end"
                                            name="paid_at" id="paid_at" value="{{ date('d/m/Y') }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <label for="reference" class="col-form-label">Reference</label>
                                </div>
                                <div class="col-sm-9">
                                    <textarea class="form-control form-control-sm" name="reference" id="reference" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <label for="payment_source_id" class="col-form-label">Bank name</label>
                                </div>
                                <div class="col-sm-9">
                                    <select name="payment_source_id" id="payment_source_id" class="form-select select2">
                                        <option value="" selected>Select Bank Name</option>
                                        @foreach ($payment_sources as $src)
                                            <option value="{{ $src->id }}">{{$src->title ?? ''}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-12" form>
                            <div class="row mb-1">
                                <div class="col-sm-9 offset-sm-3">
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" name="notify_sales_person" id="notify_sales_person" value="1" checked>
                                        <label for="notify_sales_person" class="form-check-label">
                                            Send mail to the sales person?
                                            <span id="salesPersonName" class="text-primary"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary btn-sm" id="addPaymentSubmitBtn">Add</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Cancel</button>
                        </div>
                    </div>
                    <hr>
                </form>
                <h6>Added Payment Receipts</h6>
                <div id="paymentsTableContainer">
                    <div class="table-responsive">
                        <table class="table dt-head-right" id="paymentsTable">
                            <thead>
                                <tr>
                                    <th>
                                        Reference
                                    </th>
                                    <th>
                                        Paid At
                                    </th>
                                    <th>
                                        Bank
                                    </th>
                                    <th>
                                        Amount
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- invoices payment receipt update modal -->
<div class="modal fade" id="invoice-payment-edit-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Payment receipt update</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="paymentEditForm" class="form form-horizontal">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="invoice_id" id="invoice_id">
                        <input type="hidden" name="payment_id" id="payment_id">
                        <div class="col-12">
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <label for="paid_at" class="col-form-label">Paid At <span class="text-danger">*</span></label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm"
                                               name="paid_at" id="paid_edit_at" value="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="col-12">
                                <div class="row mb-1">
                                    <div class="col-sm-3">
                                        <label for="reference" class="col-form-label">Reference</label>
                                    </div>
                                    <div class="col-sm-9">
                                        <textarea class="form-control form-control-sm" name="reference" id="reference" rows="1"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <label for="payment_source_id" class="col-form-label">Bank Name</label>
                                </div>
                                <div class="col-sm-9">
                                    <select name="payment_source_id" id="payment_source_id" class="form-select select2 payment_source_id">
                                        <option value="" selected>Select Bank Name</option>
                                        @foreach ($payment_sources as $src)
                                            <option value="{{ $src->id }}">{{$src->title ?? ''}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-11 offset-sm-1 text-center mt-2">
                            <button type="submit" class="btn btn-primary me-1 waves-effect waves-float waves-light"
                                    id="payment_edit_submit_btn">Update</button>
                            <button type="button" class="btn btn-outline-secondary waves-effect"
                                    data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--/ End invoices payment edit modal -->


