<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalTitle">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm" class="needs-validation" novalidate>
                    <input type="hidden" name="id" id="role_id">
                    <div class="mb-3">
                        <label class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input class="form-control" name="name" id="role_name" maxlength="191" required>
                        <div class="invalid-feedback" data-err="name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="role_description" rows="3"></textarea>
                        <div class="invalid-feedback" data-err="description"></div>
                    </div>
                    <?php if (authz()->isSuperAdmin()): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" name="is_super" id="role_is_super">
                            <label class="form-check-label" for="role_is_super">Super Admin role (full access)</label>
                        </div>
                        <div class="form-text">Use with caution. Super Admin bypasses permission checks.</div>
                    <?php endif; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveRole">Save</button>
            </div>
        </div>
    </div>
</div>

