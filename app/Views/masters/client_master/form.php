<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clientModalTitle">Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="clientForm" class="needs-validation" novalidate>
                    <input type="hidden" name="id" id="client_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input class="form-control" name="name" id="client_name">
                            <div class="invalid-feedback" data-err="name"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                            <input class="form-control" name="contact_person" id="client_contact_person" required>
                            <div class="invalid-feedback" data-err="contact_person">Contact Person is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="client_email">
                            <div class="invalid-feedback" data-err="email">Please enter a valid email.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input class="form-control" name="phone" id="client_phone">
                            <div class="invalid-feedback" data-err="phone">Phone must be 50 characters or less.</div>
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
