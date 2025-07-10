<div class="modal fade text-start" id="editFollowUpEmailModal" tabindex="-1" aria-labelledby="editFollowUpEmailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="editFollowUpEmailForm">
                <input type="hidden" name="id">
                <input type="hidden" name="type" value="email">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editFollowUpEmailModalLabel">Edit Follow Up Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-6 form-group">
                            <label for="to" class="form-label">To <span class="text-danger">*</span></label>
                            <select name="to[]" id="to" class="form-select select2 select2-size-sm"
                                multiple='multiple'></select>
                        </div>
                        <div class="col-12 col-md-6 form-group">
                            <label for="bcc" class="form-label">BCC</label>
                            <select name="bcc[]" id="bcc" class="form-select select2 select2-size-sm"
                                multiple='multiple'></select>
                        </div>
                        <div class="col-12 col-md-6 form-group">
                            <label for="follow_up_date" class="form-label">Follow up date <span
                                    class="text-danger">*</span></label>
                            <div>
                                <input type="text" name="follow_up_date" id="follow_up_date"
                                    class="form-control form-control-sm" style="width: 100%">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 form-group">
                            <label for="follow_up_time" class="form-label">Follow up time <span
                                    class="text-danger">*</span></label>
                            <div>
                                <input type="text" name="follow_up_time" id="follow_up_time"
                                    class="form-control form-control-sm" style="width: 100%">
                            </div>
                        </div>
                        <div class="col-12 form-group">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-md-4 form-group">
                            <label for="email_signature_id" class="form-label">
                                Email Signature
                                <span class="text-danger">
                                    *
                                </span>
                            </label>
                            <select name="email_signature_id" id="email_signature_id"
                                class="form-select select2 select2-size-sm">
                                @foreach (Auth::user()->email_signatures as $sign)
                                    <option value="{{ $sign->id }}">{{ $sign->sign_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 form-group">
                            <label for="smtp_credential_id" class="form-label">
                                SMTP
                                <span class="text-danger">
                                    *
                                </span>
                            </label>
                            <select name="smtp_credential_id" id="smtp_credential_id"
                                class="form-select select2 select2-size-sm">
                                @foreach (Auth::user()->smtp_credentials as $smtp)
                                    <option value="{{ $smtp->id }}">
                                        {{ $smtp->smtp_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 form-group">
                            <label for="contentQuill" class="form-label">Content <span
                                    class="text-danger">*</span></label>
                            <div id="contentQuill"></div>
                            <input type="hidden" name="content" id="content" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary" id="editFollowUpSubmitBtn">Save</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
