<div class="modal fade text-start" id="exportLeadsModal" tabindex="-1" aria-labelledby="exportLeadsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form id="exportLeadsForm" method="POST" action="{{ route('leads.export-filtered') }}">
                @csrf
                <input type="hidden" id="lead_type" name="lead_type" value="">
                <div class="modal-header">
                    <h4 class="modal-title" id="exportLeadsModalLabel">Export Leads</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Created at</label>
                        <div class="input-group input-daterange2">
                            <input type="text" class="form-control"
                                value="{{ date('d/m/Y', strtotime('first day of this month')) }}"
                                name="export_created_at_start" id="export_created_at_start" readonly>
                            <div class="input-group-addon mx-1 my-auto">to</div>
                            <input type="text" class="form-control" value="{{ date('d/m/Y') }}"
                                name="export_created_at_end" id="export_created_at_end" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="exportLeadsSubmitBtn">Export</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
