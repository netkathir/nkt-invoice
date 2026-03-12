<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" novalidate>
                    <input type="hidden" name="id" id="user_id" value="">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="user_name" required>
                            <div class="invalid-feedback" data-err="name"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="user_email" required>
                            <div class="invalid-feedback" data-err="email"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" class="form-control" name="mobile" id="user_mobile" placeholder="Optional">
                            <div class="invalid-feedback" data-err="mobile"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role_id" id="user_role_id" required>
                                <option value="">Select role</option>
                                <?php foreach (($roles ?? []) as $r): ?>
                                    <option value="<?= (int) ($r['id'] ?? 0) ?>">
                                        <?= esc((string) ($r['name'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" data-err="role_id"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="user_status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback" data-err="status"></div>
                        </div>

                        <div class="col-12 col-md-6"></div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Password <span class="text-danger user-pw-required">*</span></label>
                            <input type="password" class="form-control" name="password" id="user_password" autocomplete="new-password">
                            <div class="invalid-feedback" data-err="password"></div>
                            <div class="form-text user-pw-hint">Leave blank to keep the existing password.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger user-pw-required">*</span></label>
                            <input type="password" class="form-control" name="confirm_password" id="user_confirm_password" autocomplete="new-password">
                            <div class="invalid-feedback" data-err="confirm_password"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnSaveUser">Save</button>
            </div>
        </div>
    </div>
</div>

