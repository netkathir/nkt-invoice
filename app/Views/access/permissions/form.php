<div class="modal fade" id="permModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permModalTitle">Add Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="permForm" class="needs-validation" novalidate>
                    <input type="hidden" name="id" id="perm_id">
                    <div class="mb-3">
                        <label class="form-label">Permission Key <span class="text-danger">*</span></label>
                        <input class="form-control" name="key" id="perm_key" maxlength="191" required placeholder="e.g. roles.view">
                        <div class="invalid-feedback" data-err="key"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Label <span class="text-danger">*</span></label>
                        <input class="form-control" name="label" id="perm_label" maxlength="191" required placeholder="e.g. View roles">
                        <div class="invalid-feedback" data-err="label"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Module</label>
                        <input class="form-control" name="module" id="perm_module" maxlength="100" placeholder="e.g. Access">
                        <div class="invalid-feedback" data-err="module"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="perm_description" rows="3"></textarea>
                        <div class="invalid-feedback" data-err="description"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSavePerm">Save</button>
            </div>
        </div>
    </div>
</div>

