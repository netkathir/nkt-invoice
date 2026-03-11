<div class="modal fade" id="billableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="billableModalTitle">Billable Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="billableForm" class="needs-validation" novalidate>
                    <input type="hidden" name="id" id="bi_id">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Entry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="entry_date" id="bi_entry_date" required>
                            <div class="invalid-feedback" data-err="entry_date">Entry Date is required.</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select" name="client_id" id="bi_client_id" required></select>
                            <div class="invalid-feedback" data-err="client_id">Client is required.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" id="bi_description" rows="4"></textarea>
                            <div class="invalid-feedback" id="bi_description_feedback" data-err="description" style="display:none;">Description is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="quantity" id="bi_quantity" required>
                            <div class="invalid-feedback" data-err="quantity">Quantity is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="unit_price" id="bi_unit_price" required>
                            <div class="invalid-feedback" data-err="unit_price">Unit Price is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount</label>
                            <input class="form-control" id="bi_amount_preview" value="0.00" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Billing Month (Optional)</label>
                            <input class="form-control" name="billing_month" id="bi_billing_month" placeholder="Mar 2026" pattern="^([0-9]{4}-[0-9]{2}|[A-Za-z]{3} [0-9]{4})$" title="Use Mar 2026 (or YYYY-MM for older records)">
                            <div class="invalid-feedback" data-err="billing_month">Billing Month must be like Mar 2026.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="bi_status" required>
                                <option value="Pending">Pending</option>
                                <option value="Billed">Billed</option>
                            </select>
                            <div class="invalid-feedback" data-err="status">Status is required.</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveBillable">Save</button>
            </div>
        </div>
    </div>
</div>
