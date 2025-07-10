<!-- expense notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered notes-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Expense Notes</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <input type="hidden" name="expense_id" id="expense_id">
            <div class="modal-body bg-light">
                <form class="form form-horizontal" id="addNoteForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="notes_method" value="">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-sm-1 pe-0">
                                    <label for="notes">
                                        Note <span class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="col-sm-11">
                                    <div class="mb-1">
                                        <textarea class="form-control" id="notes" name="note" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-11 offset-sm-1">
                            <button type="submit"
                                    class="btn btn-primary me-1 waves-effect waves-float waves-light"
                                    id="note_submit_btn">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary waves-effect"
                                    id="note_reset_btn">Reset</button>
                        </div>
                    </div>
                </form>
                <hr>
                <h3>Notes history</h3>
                <div id="expense_notes_list"></div>
            </div>
        </div>
    </div>
</div>
<!--/ expense notes Modal -->
