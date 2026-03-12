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
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="user_password" autocomplete="new-password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#user_password" aria-label="Show password" aria-pressed="false">
                                    <span class="pw-icon pw-icon-eye" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M1.5 12s3.5-7.5 10.5-7.5S22.5 12 22.5 12 19 19.5 12 19.5 1.5 12 1.5 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span class="pw-icon pw-icon-eye-off d-none" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M10.6 10.6a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6.5 6.7C3.7 8.8 2 12 2 12s3.5 7.5 10 7.5c1.6 0 3.1-.3 4.4-.9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9.9 4.8C10.6 4.6 11.3 4.5 12 4.5 19 4.5 22 12 22 12s-1.1 2.8-3.4 4.9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            <div class="invalid-feedback" data-err="password"></div>
                            <div class="form-text user-pw-hint">Leave blank to keep the existing password.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger user-pw-required">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="confirm_password" id="user_confirm_password" autocomplete="new-password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#user_confirm_password" aria-label="Show confirm password" aria-pressed="false">
                                    <span class="pw-icon pw-icon-eye" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M1.5 12s3.5-7.5 10.5-7.5S22.5 12 22.5 12 19 19.5 12 19.5 1.5 12 1.5 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span class="pw-icon pw-icon-eye-off d-none" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M10.6 10.6a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6.5 6.7C3.7 8.8 2 12 2 12s3.5 7.5 10 7.5c1.6 0 3.1-.3 4.4-.9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9.9 4.8C10.6 4.6 11.3 4.5 12 4.5 19 4.5 22 12 22 12s-1.1 2.8-3.4 4.9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </button>
                            </div>
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
