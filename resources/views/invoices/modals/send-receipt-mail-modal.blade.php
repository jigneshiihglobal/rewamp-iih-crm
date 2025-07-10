<div class="modal fade text-start" id="sendReceiptMailModal" tabindex="-1" aria-labelledby="sendMailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="sendReceiptMailForm" method="post" action="{{route('invoices.send-receipt-mail',$invoice->encrypted_id)}}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="sendMailModalLabel">Send Receipt Mail</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="receipt_to" class="form-label">To <span class="text-danger">*</span></label>
                        <select name="receipt_to[]" id="receipt_to" class="form-select select2" multiple='multiple'></select>
                    </div>
                    <div class="form-group">
                        <label for="receipt_bcc" class="form-label">BCC <span class="text-danger">*</span></label>
                        <select name="receipt_bcc[]" id="receipt_bcc" class="form-select select2" multiple='multiple'></select>
                    </div>
                    <div class="form-group">
                        <label for="receipt_subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="receipt_subject" id="receipt_subject" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="receipt" class="form-label">Receipt</label>
                        {{--<label for="custom_attach" class="custom-file-upload form-control" style="border: 1px solid #ccc;display: inline-block;padding: 6px 12px;cursor: pointer;">
                            <span class="choose_file">Choose Files</span>
                            <span class="no_file_chosen">No file chosen</span>
                        </label>
                        <input type="file" class="form-control" name="custom_attach[]" id="custom_attach" accept=".pdf,image/*" style="margin-bottom: 5px;display: none;" multiple>--}}
                    </div>
                    {{--<span style='color:red;margin-right: 10px;cursor: pointer' class="receiptFile">X</span>--}}<span class="custom_file_attach" id="receiptFile"></span>
                    <ul id="receiptNewFile"></ul>
                    <div class="form-group">
                        <label for="contentReceiptQuill" class="form-label">Content <span class="text-danger">*</span></label>
                        <div id="contentReceiptQuill"></div>
                        <input type="hidden" name="receiptContent" id="receiptContent">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="sendReceiptMailSubmitBtn">Send Mail</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
