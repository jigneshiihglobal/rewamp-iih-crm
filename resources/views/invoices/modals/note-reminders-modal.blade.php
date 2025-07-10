<!-- invoices notes Modal -->
<div class="modal fade" id="noteRemindersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered notes-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Invoice Notes</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form class="form form-horizontal" id="noteReminderForm" method="POST">
                    @csrf
                    <input type="hidden" name="note_reminder_id" id="note_reminder_id">
                    <input type="hidden" name="_method" id="notes_reminder_method" value="">
                    <input type="hidden" id="without_vat" name="without_vat" value="">
                    <input type="hidden" id="vat_amount" name="vat_amount" value="">
                    <div class="row">
                        <div class="col-12">
                            <label for="notes">
                                Note <span class="text-danger">*</span>
                            </label>
                            <div class="mb-1 invoice-reminder-box">
                                <textarea class="form-control" id="notes" name="note" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="Customer"> Customer </label>
                            <div class="mb-1 invoice-reminder-box">
                                <select id="client_id" class="select2-size-sm form-select select2" name="client_ids[]">
                                    <option value="">Select a customer</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="numeric_value">Amount</label>
                            <input type="number" id="numeric_value" class="form-control" name="numeric_value" placeholder="00.00">
                        </div>
                        <div class="col-md-2 vat_amount_add">
                            <label for="vat_add">+ Vat</label>
                            <input type="checkbox" id="vat_add" class="form-check-input" name="vat_add">
                        </div>
                    </div>
                    <!-- New Numeric Input -->
                    <div class="row">
                        <div class="col-md-12" style="margin-top: 0px; margin-bottom: 10px; text-align: left;">
                            <div>
                                <button type="button" class="btn btn-primary me-1 waves-effect waves-float waves-light plus-btn" id="add_row_btn"><span style="font-size: 30px;">+</span></button>
                            </div>

                        </div>
                    </div>
                    <table id="numeric_value_table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Received Amount</th>
                                <th>Pending Amount</th>
                                <th>Received Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div id="hidden_inputs_container"></div>
                    <div class="row">
                        <div class="col-md-12" style="margin-top: 20px; margin-bottom: 30px; text-align: left;">
                            <div>
                                <button type="submit"
                                        class="btn btn-primary me-1 waves-effect waves-float waves-light"
                                        id="note_reminder_submit_btn">Submit</button>
                                <button type="reset" class="btn btn-outline-secondary waves-effect"
                                        id="note_reminder_reset_btn">Reset</button>
                            </div>

                        </div>
                    </div>
                </form><hr>
                <h3>Invoice Notes history</h3>
                <div id="note_reminders_list"></div>
            </div>
        </div>
    </div>
</div>
<!--/ invoices notes Modal -->
