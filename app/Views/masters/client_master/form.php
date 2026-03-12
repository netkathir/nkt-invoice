<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clientModalTitle">Add Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="clientForm" class="needs-validation" novalidate>
                    <input type="hidden" name="id" id="client_id">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Company Name</label>
                            <input class="form-control" name="name" id="client_name" placeholder="Optional">
                            <div class="invalid-feedback" data-err="name"></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                            <input class="form-control" name="contact_person" id="client_contact_person" required>
                            <div class="invalid-feedback" data-err="contact_person">Contact Person is required.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="client_email" placeholder="Optional">
                            <div class="invalid-feedback" data-err="email">Please enter a valid email.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone</label>
                            <input class="form-control" name="phone" id="client_phone" placeholder="Optional">
                            <div class="invalid-feedback" data-err="phone">Phone must be 50 characters or less.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="client_address" rows="3" placeholder="Optional"></textarea>
                            <div class="invalid-feedback" data-err="address"></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Billing Address</label>
                            <textarea class="form-control" name="billing_address" id="client_billing_address" rows="3" placeholder="Optional"></textarea>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="client_same_as_address">
                                <label class="form-check-label" for="client_same_as_address">Same as Address</label>
                            </div>
                            <div class="invalid-feedback d-block" data-err="billing_address"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">City</label>
                            <input class="form-control" name="city" id="client_city" placeholder="Optional">
                            <div class="invalid-feedback" data-err="city"></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">State</label>
                            <input class="form-control" name="state" id="client_state" placeholder="Optional">
                            <div class="invalid-feedback" data-err="state"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Country</label>
                            <input class="form-control" name="country" id="client_country" placeholder="Optional">
                            <div class="invalid-feedback" data-err="country"></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Postal Code</label>
                            <input class="form-control" name="postal_code" id="client_postal_code" placeholder="Optional">
                            <div class="invalid-feedback" data-err="postal_code"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveClient">Save</button>
            </div>
        </div>
    </div>
</div>
