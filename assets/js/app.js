(function () {
    window.BMS = window.BMS || {};
    const SIDEBAR_KEY = 'bms_sidebar_collapsed';

    function base(path) {
        return (window.APP_BASE_URL || '/') + String(path || '').replace(/^\/+/, '');
    }

    function notify(msg, type) {
        type = type || 'info';
        const cls = type === 'success' ? 'alert-success' : (type === 'danger' ? 'alert-danger' : 'alert-info');
        const $box = $('<div class="alert ' + cls + ' alert-dismissible fade show bms-alert mb-3" role="alert"></div>');
        $box.text(msg || '');
        $box.append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');

        const $modalBody = $('.modal.show .modal-body').first();
        if ($modalBody.length) {
            $modalBody.find('.bms-alert').remove();
            $modalBody.prepend($box);
        } else {
            $('.bms-alert').remove();
            $('main.container-fluid').prepend($box);
        }
        setTimeout(function () { $box.alert('close'); }, 4500);
    }

    function postJson(url, data) {
        return $.ajax({
            url: base(url),
            method: 'POST',
            data: data,
            dataType: 'json'
        });
    }

    function getJson(url, data) {
        return $.ajax({
            url: base(url),
            method: 'GET',
            data: data || {},
            dataType: 'json'
        });
    }

    function dtDefaults() {
        return {
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            // Move "entries per page" (length) control to bottom on all tables
            dom:
                "<'row g-2 align-items-center mb-2'<'col-12 col-md-6'i><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row g-2 align-items-center mt-2'<'col-12 col-md-6'l><'col-12 col-md-6 text-end'p>>",
        };
    }

    function formatUiDate(value, type) {
        const raw = String(value || '').trim();
        if (type === 'sort' || type === 'type') return raw;
        if (!raw) return '-';

        const datePart = raw.slice(0, 10);
        const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(datePart);
        if (!m) return raw;

        const yyyy = m[1];
        const mm = parseInt(m[2], 10);
        const dd = m[3];
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const mon = months[mm - 1] || '';
        if (!mon) return raw;
        return dd + ' ' + mon + ' ' + yyyy;
    }

    function renderBulletText(value, type) {
        const raw = String(value || '');
        if (type === 'sort' || type === 'type') return raw;

        let lines = [];
        if (raw.indexOf('<') !== -1 && /<li[\s>]/i.test(raw)) {
            try {
                const tmp = document.createElement('div');
                tmp.innerHTML = raw;
                const lis = tmp.querySelectorAll('li');
                if (lis && lis.length) {
                    lis.forEach(function (li) {
                        const t = String(li.innerText || li.textContent || '').trim();
                        if (t) lines.push(t);
                    });
                }
            } catch (e) {}
        }

        if (!lines.length) {
            lines = raw.split(/\r?\n/).map(function (l) { return l.trim(); }).filter(function (l) { return l !== ''; });
        }
        if (!lines.length) return '-';
        const safe = function (s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };
        return '<ul class="mb-0">' + lines.map(function (l) { return '<li>' + safe(l) + '</li>'; }).join('') + '</ul>';
    }

    function billableStatusBadge(status) {
        const v = String(status || '').trim();
        const cls = v === 'Billed' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle';
        const label = v === 'Billed' ? 'Billed' : 'Pending';
        return '<span class="badge rounded-pill ' + cls + '">' + label + '</span>';
    }

    function statusSelectHtml(tableName, recordId, currentValue, options) {
        const current = (currentValue === null || currentValue === undefined || currentValue === '') ? '' : String(currentValue);
        const opts = (options || []).map(function (v) {
            const selected = String(v) === current ? 'selected' : '';
            return '<option value="' + String(v).replace(/"/g, '&quot;') + '" ' + selected + '>' + v + '</option>';
        }).join('');

        return '' +
            '<select class="form-select form-select-sm bms-status-select" ' +
            'data-table="' + tableName + '" data-id="' + recordId + '" data-old="' + current.replace(/"/g, '&quot;') + '">' +
            opts +
            '</select>';
    }

    BMS.initDashboard = function () {
        const table = $('#dtRecentBillables').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('dashboard/recent-billable-items'), dataSrc: 'data' },
            order: [[1, 'desc']],
            columns: [
                { data: 'entry_no', render: function (d, t, row) { return d || ('BI-' + String(row.id).padStart(5, '0')); } },
                { data: 'entry_date', render: formatUiDate },
                { data: 'client_name' },
                { data: 'description', orderable: false, render: renderBulletText },
                { data: 'quantity', className: 'text-end' },
                { data: 'unit_price', className: 'text-end' },
                { data: 'amount', className: 'text-end' },
                { data: 'status', orderable: false, render: function (d, t, row) {
                    return billableStatusBadge(d);
                }},
                { data: null, orderable: false, render: function (row) {
                    const billedDisabled = row.status !== 'Pending' ? 'disabled' : '';
                    return '' +
                        '<a class="btn btn-sm btn-outline-primary me-1" href="' + base('billable-items?edit=' + row.id) + '">Edit</a>' +
                        '<button class="btn btn-sm btn-outline-success me-1 btn-bill-dash" type="button" ' + billedDisabled + '>Mark Billed</button>' +
                        '<button class="btn btn-sm btn-outline-danger btn-del-dash" type="button">Delete</button>';
                }},
            ],
        }));

        $('#dtRecentBillables tbody').on('click', 'button.btn-del-dash', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            if (!confirm('Delete this billable item?')) return;
            postJson('billable-items/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#dtRecentBillables tbody').on('click', 'button.btn-bill-dash', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            if (row.status !== 'Pending') return;
            postJson('billable-items/mark-billed', { id: row.id })
                .done(function (res) { notify(res.message || 'Updated.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.', 'danger'); });
        });
    };

    BMS.initClientMaster = function () {
        const $modal = new bootstrap.Modal(document.getElementById('clientModal'));
        const $saveBtn = $('#btnSaveClient');
        const $form = $('#clientForm');

        function setFormMode(mode) {
            const isView = mode === 'view';
            $form.find('input,select,textarea').prop('disabled', isView);
            $saveBtn.toggle(!isView);
        }

        function clearErrors() {
            $('#clientForm .is-invalid').removeClass('is-invalid');
            $('#clientForm [data-err]').text('');
        }

        const table = $('#dtClients').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('masters/client-master/list'), dataSrc: 'data' },
            order: [[4, 'desc']],
            columns: [
                { data: 'name', render: function (d, t, row) { return d || row.contact_person || row.email || row.phone || ('Client #' + row.id); } },
                { data: 'contact_person', render: function (d) { return d || '-'; } },
                { data: 'email', render: function (d) { return d || '-'; } },
                { data: 'phone', render: function (d) { return d || '-'; } },
                { data: 'created_at', render: formatUiDate },
                { data: null, orderable: false, render: function (row) {
                    return '' +
                        '<button class="btn btn-sm btn-outline-secondary me-1 btn-view" type="button">View</button>' +
                        '<button class="btn btn-sm btn-outline-primary me-1 btn-edit" type="button">Edit</button>' +
                        '<button class="btn btn-sm btn-outline-danger btn-del" type="button">Delete</button>';
                }},
            ],
        }));

        $('#btnAddClient').on('click', function () {
            clearErrors();
            $('#clientModalTitle').text('Add Client');
            $('#clientForm')[0].reset();
            $('#clientForm').removeClass('was-validated');
            $('#client_id').val('');
            setFormMode('edit');
            $modal.show();
        });

        $('#dtClients tbody').on('click', 'button.btn-view', function () {
            clearErrors();
            const row = table.row($(this).closest('tr')).data();
            $('#clientModalTitle').text('View Client');
            $('#clientForm').removeClass('was-validated');
            $('#client_id').val(row.id);
            $('#client_name').val(row.name);
            $('#client_contact_person').val(row.contact_person || '');
            $('#client_email').val(row.email || '');
            $('#client_phone').val(row.phone || '');
            setFormMode('view');
            $modal.show();
        });

        $('#dtClients tbody').on('click', 'button.btn-edit', function () {
            clearErrors();
            const row = table.row($(this).closest('tr')).data();
            $('#clientModalTitle').text('Edit Client');
            $('#clientForm').removeClass('was-validated');
            $('#client_id').val(row.id);
            $('#client_name').val(row.name);
            $('#client_contact_person').val(row.contact_person || '');
            $('#client_email').val(row.email || '');
            $('#client_phone').val(row.phone || '');
            setFormMode('edit');
            $modal.show();
        });

        $('#dtClients tbody').on('click', 'button.btn-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!confirm('Delete this client?')) return;
            postJson('masters/client-master/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#btnSaveClient').on('click', function () {
            clearErrors();
            const form = document.getElementById('clientForm');
            form.classList.add('was-validated');
            if (!form.checkValidity()) {
                notify('Please fill the required fields.', 'danger');
                return;
            }
            postJson('masters/client-master/save', $('#clientForm').serialize())
                .done(function (res) {
                    notify(res.message || 'Saved.', 'success');
                    $modal.hide();
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    notify(res.message || 'Save failed.', 'danger');
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (k) {
                            const $field = $('#clientForm [name="' + k + '"]');
                            $field.addClass('is-invalid');
                            $('#clientForm [data-err="' + k + '"]').text(res.errors[k]);
                        });
                    }
                });
        });
    };

    // Backward compatible alias (older views)
    BMS.initClients = BMS.initClientMaster;

    BMS.initRoles = function (opts) {
        opts = opts || {};
        const $modal = new bootstrap.Modal(document.getElementById('roleModal'));
        const $saveBtn = $('#btnSaveRole');
        const $form = $('#roleForm');

        function setFormMode(mode) {
            const isView = mode === 'view';
            $form.find('input,select,textarea').prop('disabled', isView);
            // Keep modal close button usable.
            $('#roleModal .btn-close').prop('disabled', false);
            $saveBtn.toggle(!isView);
        }

        function clearErrors() {
            $('#roleForm .is-invalid').removeClass('is-invalid');
            $('#roleForm [data-err]').text('');
        }

        const table = $('#dtRoles').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('roles/list'), dataSrc: 'data' },
            order: [[2, 'desc'], [0, 'asc']],
            columns: [
                { data: 'name', render: function (d) { return d || '-'; } },
                { data: 'description', render: function (d) { return d || '-'; } },
                { data: 'is_super', render: function (d) {
                    const v = parseInt(d || 0, 10) === 1;
                    return v ? '<span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle">Super</span>' : '<span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle">Role</span>';
                }},
                { data: 'admins_count', render: function (d) { return d || 0; } },
                { data: 'permissions_count', render: function (d) { return d || 0; } },
                { data: 'created_at', render: formatUiDate },
                { data: null, orderable: false, render: function (row) {
                    const btns = [];
                    btns.push('<button class="btn btn-sm btn-outline-secondary me-1 btn-view" type="button">View</button>');
                    if (opts.canEdit) btns.push('<button class="btn btn-sm btn-outline-primary me-1 btn-edit" type="button">Edit</button>');
                    if (opts.canAssignPerms) btns.push('<a class="btn btn-sm btn-outline-success me-1" href="' + base('roles/' + row.id + '/permissions') + '">Permissions</a>');
                    if (opts.canDelete) btns.push('<button class="btn btn-sm btn-outline-danger btn-del" type="button">Delete</button>');
                    return btns.join('');
                }},
            ],
        }));

        if (opts.canCreate) {
            $('#btnAddRole').on('click', function () {
                clearErrors();
                $('#roleModalTitle').text('Add Role');
                $('#roleForm')[0].reset();
                $('#roleForm').removeClass('was-validated');
                $('#role_id').val('');
                setFormMode('edit');
                $modal.show();
            });
        }

        $('#dtRoles tbody').on('click', 'button.btn-view', function () {
            clearErrors();
            const row = table.row($(this).closest('tr')).data();
            $('#roleModalTitle').text('View Role');
            $('#roleForm').removeClass('was-validated');
            $('#role_id').val(row.id);
            $('#role_name').val(row.name || '');
            $('#role_description').val(row.description || '');
            const $isSuper = $('#role_is_super');
            if ($isSuper.length) $isSuper.prop('checked', parseInt(row.is_super || 0, 10) === 1);
            setFormMode('view');
            $modal.show();
        });

        $('#dtRoles tbody').on('click', 'button.btn-edit', function () {
            clearErrors();
            const row = table.row($(this).closest('tr')).data();
            $('#roleModalTitle').text('Edit Role');
            $('#roleForm').removeClass('was-validated');
            $('#role_id').val(row.id);
            $('#role_name').val(row.name || '');
            $('#role_description').val(row.description || '');
            const $isSuper = $('#role_is_super');
            if ($isSuper.length) $isSuper.prop('checked', parseInt(row.is_super || 0, 10) === 1);
            setFormMode('edit');
            $modal.show();
        });

        $('#dtRoles tbody').on('click', 'button.btn-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            if (!confirm('Delete this role?')) return;
            postJson('roles/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#btnSaveRole').on('click', function () {
            clearErrors();
            const form = document.getElementById('roleForm');
            form.classList.add('was-validated');
            if (!form.checkValidity()) {
                notify('Please fill the required fields.', 'danger');
                return;
            }

            postJson('roles/save', $('#roleForm').serialize())
                .done(function (res) {
                    notify(res.message || 'Saved.', 'success');
                    $modal.hide();
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    notify(res.message || 'Save failed.', 'danger');
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (k) {
                            const $field = $('#roleForm [name="' + k + '"]');
                            $field.addClass('is-invalid');
                            $('#roleForm [data-err="' + k + '"]').text(res.errors[k]);
                        });
                    }
                });
        });
    };

    BMS.initRolePermissions = function (opts) {
        opts = opts || {};
        if (opts.locked) return;

        function updateModuleHeader(module) {
            const $items = $('.perm-checkbox[data-module="' + module.replace(/"/g, '\\"') + '"]');
            const checkedCount = $items.filter(':checked').length;
            const total = $items.length;
            const $header = $('.module-select-all[data-module="' + module.replace(/"/g, '\\"') + '"]');
            if (!$header.length) return;
            if (checkedCount === 0) {
                $header.prop('checked', false);
                $header.prop('indeterminate', false);
            } else if (checkedCount === total) {
                $header.prop('checked', true);
                $header.prop('indeterminate', false);
            } else {
                $header.prop('checked', false);
                $header.prop('indeterminate', true);
            }
        }

        $('.module-select-all').on('change', function () {
            const mod = $(this).data('module');
            const on = $(this).is(':checked');
            $('.perm-checkbox[data-module="' + String(mod).replace(/"/g, '\\"') + '"]').prop('checked', on);
            updateModuleHeader(mod);
        });

        $('.perm-checkbox').on('change', function () {
            const mod = $(this).data('module');
            updateModuleHeader(mod);
        });

        // init
        $('.module-select-all').each(function () {
            updateModuleHeader($(this).data('module'));
        });

        $('#btnSaveRolePerms').on('click', function () {
            const roleId = parseInt(opts.roleId || 0, 10);
            if (!roleId) return;
            postJson('roles/' + roleId + '/permissions', $('#rolePermsForm').serialize())
                .done(function (res) { notify(res.message || 'Saved.', 'success'); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.', 'danger'); });
        });
    };

    BMS.initPermissions = function (opts) {
        opts = opts || {};
        const $modal = new bootstrap.Modal(document.getElementById('permModal'));
        const $saveBtn = $('#btnSavePerm');
        const $form = $('#permForm');

        function setFormMode(mode) {
            const isView = mode === 'view';
            $form.find('input,select,textarea').prop('disabled', isView);
            $('#permModal .btn-close').prop('disabled', false);
            $saveBtn.toggle(!isView);
        }

        function clearErrors() {
            $('#permForm .is-invalid').removeClass('is-invalid');
            $('#permForm [data-err]').text('');
        }

        const table = $('#dtPermissions').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('permissions/list'), dataSrc: 'data' },
            order: [[2, 'asc'], [0, 'asc']],
            columns: [
                { data: 'key', render: function (d) { return d || '-'; } },
                { data: 'label', render: function (d) { return d || '-'; } },
                { data: 'module', render: function (d) { return d || '-'; } },
                { data: 'roles_count', render: function (d) { return d || 0; } },
                { data: 'created_at', render: formatUiDate },
                { data: null, orderable: false, render: function () {
                    const btns = [];
                    btns.push('<button class="btn btn-sm btn-outline-secondary me-1 btn-view" type="button">View</button>');
                    if (opts.canEdit) btns.push('<button class="btn btn-sm btn-outline-primary me-1 btn-edit" type="button">Edit</button>');
                    if (opts.canDelete) btns.push('<button class="btn btn-sm btn-outline-danger btn-del" type="button">Delete</button>');
                    return btns.join('');
                }},
            ],
        }));

        if (opts.canCreate) {
            $('#btnAddPermission').on('click', function () {
                clearErrors();
                $('#permModalTitle').text('Add Permission');
                $('#permForm')[0].reset();
                $('#permForm').removeClass('was-validated');
                $('#perm_id').val('');
                setFormMode('edit');
                $modal.show();
            });
        }

        $('#dtPermissions tbody').on('click', 'button.btn-view', function () {
            clearErrors();
            const row = table.row($(this).closest('tr')).data();
            $('#permModalTitle').text('View Permission');
            $('#permForm').removeClass('was-validated');
            $('#perm_id').val(row.id);
            $('#perm_key').val(row.key || '');
            $('#perm_label').val(row.label || '');
            $('#perm_module').val(row.module || '');
            $('#perm_description').val(row.description || '');
            setFormMode('view');
            $modal.show();
        });

        $('#dtPermissions tbody').on('click', 'button.btn-edit', function () {
            clearErrors();
            const row = table.row($(this).closest('tr')).data();
            $('#permModalTitle').text('Edit Permission');
            $('#permForm').removeClass('was-validated');
            $('#perm_id').val(row.id);
            $('#perm_key').val(row.key || '');
            $('#perm_label').val(row.label || '');
            $('#perm_module').val(row.module || '');
            $('#perm_description').val(row.description || '');
            setFormMode('edit');
            $modal.show();
        });

        $('#dtPermissions tbody').on('click', 'button.btn-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            if (!confirm('Delete this permission?')) return;
            postJson('permissions/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#btnSavePerm').on('click', function () {
            clearErrors();
            const form = document.getElementById('permForm');
            form.classList.add('was-validated');
            if (!form.checkValidity()) {
                notify('Please fill the required fields.', 'danger');
                return;
            }

            postJson('permissions/save', $('#permForm').serialize())
                .done(function (res) {
                    notify(res.message || 'Saved.', 'success');
                    $modal.hide();
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    notify(res.message || 'Save failed.', 'danger');
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (k) {
                            const $field = $('#permForm [name="' + k + '"]');
                            $field.addClass('is-invalid');
                            $('#permForm [data-err="' + k + '"]').text(res.errors[k]);
                        });
                    }
                });
        });
    };

    BMS.initAdminRolesEdit = function (opts) {
        opts = opts || {};
        const adminId = parseInt(opts.adminId || 0, 10);
        if (!adminId) return;

        $('#btnSaveAdminRoles').on('click', function () {
            postJson('admin-roles/' + adminId, $('#adminRolesForm').serialize())
                .done(function (res) { notify(res.message || 'Saved.', 'success'); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.', 'danger'); });
        });
    };

    BMS.initBillableItems = function () {
        const $modal = new bootstrap.Modal(document.getElementById('billableModal'));
        const hasTiny = typeof window.tinymce !== 'undefined' && document.getElementById('bi_description');
        const editorId = 'bi_description';
        let pendingDescriptionHtml = null;
        let isEnforcingBullets = false;
        const urlEditId = parseInt((new URLSearchParams(window.location.search)).get('edit') || '0', 10) || 0;
        let urlEditOpened = false;

        function getTinyEditor() {
            if (!hasTiny) return null;
            try {
                const ed = window.tinymce.get(editorId);
                if (ed) return ed;
                const active = window.tinymce.activeEditor;
                if (active && active.id === editorId) return active;
                return null;
            } catch (e) {
                return null;
            }
        }

        function escapeHtml(s) {
            return String(s || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function textFromHtml(html) {
            const tmp = document.createElement('div');
            tmp.innerHTML = String(html || '');
            return String(tmp.innerText || tmp.textContent || '')
                .replace(/\u00A0/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function bulletHtmlFromText(text) {
            const lines = String(text || '')
                .replace(/\u00A0/g, ' ')
                .split(/\r?\n/)
                .map(function (l) { return l.trim(); })
                .filter(function (l) { return l !== ''; });

            if (lines.length === 0) {
                return '<ul><li></li></ul>';
            }

            return '<ul>' + lines.map(function (l) { return '<li>' + escapeHtml(l) + '</li>'; }).join('') + '</ul>';
        }

        function enforceBulletOnly(editor) {
            if (!editor || isEnforcingBullets) return;
            isEnforcingBullets = true;
            try {
                const body = (typeof editor.getBody === 'function') ? editor.getBody() : null;
                const rawText = body ? (body.innerText || body.textContent || '') : (editor.getContent({ format: 'text' }) || '');
                let text = String(rawText).replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
                const html = (editor.getContent({ format: 'html' }) || '').trim();

                if (!text && html) {
                    text = textFromHtml(html);
                }

                if (!text) {
                    editor.setContent('<ul><li></li></ul>');
                    const li = editor.getBody().querySelector('li');
                    if (li) {
                        editor.selection.select(li, true);
                        editor.selection.collapse(false);
                    }
                    return;
                }

                setDescriptionValidity(true);

                // Force unordered list root
                if (!/^<ul[\s>]/i.test(html)) {
                    editor.setContent(bulletHtmlFromText(text));
                }
            } finally {
                isEnforcingBullets = false;
            }
        }

        function setDescriptionValidity(valid, msg) {
            const ed = getTinyEditor();
            const $fb = $('#bi_description_feedback');
            if ($fb.length) {
                if (msg) $fb.text(msg);
                $fb.toggle(!valid);
            }

            if (ed && typeof ed.getContainer === 'function') {
                $(ed.getContainer()).toggleClass('is-invalid', !valid);
            } else {
                $('#bi_description').toggleClass('is-invalid', !valid);
            }
        }

        function getDescriptionText() {
            const ed = getTinyEditor();
            if (ed) {
                const body = (typeof ed.getBody === 'function') ? ed.getBody() : null;
                const raw = body ? (body.innerText || body.textContent || '') : (ed.getContent({ format: 'text' }) || '');
                const t = String(raw).replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
                if (t) return t;
                return textFromHtml(ed.getContent({ format: 'html' }) || '');
            }
            return String($('#bi_description').val() || '')
                .replace(/<[^>]*>/g, ' ')
                .replace(/\u00A0/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function getDescriptionHtml() {
            const ed = getTinyEditor();
            if (ed) {
                return (ed.getContent() || '').trim();
            }
            return String($('#bi_description').val() || '').trim();
        }

        function setDescriptionHtml(html) {
            const ed = getTinyEditor();
            if (ed) {
                ed.setContent(html || '');
            } else {
                $('#bi_description').val(html || '');
                pendingDescriptionHtml = html || '';
            }
        }

        if (hasTiny) {
            // Initialize TinyMCE once
            if (!getTinyEditor()) {
                // Avoid native HTML5 required validation on a textarea hidden by TinyMCE.
                $('#bi_description').prop('required', false);

                window.tinymce.init({
                    selector: '#' + editorId,
                    height: 150,
                    menubar: false,
                    branding: false,
                    statusbar: false,
                    plugins: 'lists',
                    toolbar: false,
                    contextmenu: false,
                    paste_as_text: true,
                    forced_root_block: false,
                    valid_elements: 'ul,li,br',
                    content_style:
                        'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;font-size:14px;}' +
                        'ul{margin:0;padding-left:1.2rem;}' +
                        'li{margin:0;}',
                    setup: function (editor) {
                        editor.on('init', function () {
                            if (pendingDescriptionHtml !== null) {
                                editor.setContent(pendingDescriptionHtml);
                                pendingDescriptionHtml = null;
                            }
                            enforceBulletOnly(editor);
                        });

                        // Prevent exiting bullet list (no paragraphs)
                        editor.on('keydown', function (e) {
                            if (e.key === 'Enter') {
                                const ul = editor.dom.getParent(editor.selection.getNode(), 'ul');
                                if (!ul) {
                                    e.preventDefault();
                                    const body = (typeof editor.getBody === 'function') ? editor.getBody() : null;
                                    const raw = body ? (body.innerText || body.textContent || '') : (editor.getContent({ format: 'text' }) || '');
                                    editor.setContent(bulletHtmlFromText(raw));
                                    return;
                                }

                                const li = editor.dom.getParent(editor.selection.getNode(), 'li');
                                if (li) {
                                    const liText = String(li.textContent || '').replace(/\u00A0/g, ' ').trim();
                                    // If empty list item, don't allow TinyMCE to end the list into a paragraph.
                                    if (!liText) {
                                        e.preventDefault();
                                        // create another empty bullet if user expects a new line
                                        editor.execCommand('mceInsertContent', false, '<li></li>');
                                        return;
                                    }
                                }
                            }
                        });

                        editor.on('paste', function () {
                            setTimeout(function () { enforceBulletOnly(editor); }, 0);
                        });

                        editor.on('keyup input change NodeChange SetContent', function () {
                            const ok = getDescriptionText() !== '';
                            setDescriptionValidity(ok);
                            enforceBulletOnly(editor);
                        });
                    },
                });
            }

            // Repaint editor when modal opens (prevents layout glitches in hidden modals)
            const billableModalEl = document.getElementById('billableModal');
            if (billableModalEl) billableModalEl.addEventListener('shown.bs.modal', function () {
                const ed = getTinyEditor();
                if (ed && typeof ed.execCommand === 'function') {
                    try { ed.execCommand('mceRepaint'); } catch (e) {}
                }
                if (ed && pendingDescriptionHtml !== null) {
                    ed.setContent(pendingDescriptionHtml);
                    pendingDescriptionHtml = null;
                }
            });
        }

        function clearBillableErrors() {
            const $form = $('#billableForm');
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('[data-err]').each(function () {
                const $el = $(this);
                const def = $el.data('default');
                if (def !== undefined) {
                    $el.text(def);
                }
            });
            setDescriptionValidity(true);
        }

        // Capture default invalid-feedback texts once (so we can restore them after server errors).
        $('#billableForm [data-err]').each(function () {
            const $el = $(this);
            if ($el.data('default') === undefined) {
                $el.data('default', $el.text());
            }
        });

        // (Rich text validation handled via TinyMCE helpers above)

        function fillClients($select, clients, includeAllOption) {
            $select.empty();
            if (includeAllOption) {
                $select.append('<option value=\"\">All Clients</option>');
            } else {
                $select.append('<option value=\"\">Select Client</option>');
            }
            clients.forEach(function (c) {
                const label = c.name || c.contact_person || c.email || c.phone || ('Client #' + c.id);
                $select.append('<option value=\"' + c.id + '\">' + label + '</option>');
            });
        }

        function loadClients() {
            return getJson('masters/client-master/list').then(function (res) {
                const clients = (res && res.data) ? res.data : [];
                fillClients($('#filterClient'), clients, true);
                fillClients($('#bi_client_id'), clients, false);
                return clients;
            });
        }

        function updateAmountPreview() {
            const qty = parseFloat($('#bi_quantity').val()) || 0;
            const price = parseFloat($('#bi_unit_price').val()) || 0;
            $('#bi_amount_preview').val((qty * price).toFixed(2));
        }

        $('#bi_quantity,#bi_unit_price').on('input', updateAmountPreview);

        const table = $('#dtBillableItems').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: {
                url: base('billable-items/list'),
                dataSrc: 'data',
                data: function (d) {
                    d.client_id = $('#filterClient').val();
                    d.status = $('#filterStatus').val();
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: 'entry_no', render: function (d, t, row) { return d || ('BI-' + String(row.id).padStart(5, '0')); } },
                { data: 'entry_date', render: formatUiDate },
                { data: 'client_name' },
                { data: 'description', orderable: false, render: renderBulletText },
                { data: 'amount', className: 'text-end' },
                { data: 'status', orderable: false, render: function (d, t, row) {
                    return billableStatusBadge(d);
                }},
                { data: null, orderable: false, render: function (row) {
                    const billedDisabled = row.status !== 'Pending' ? 'disabled' : '';
                    return '' +
                        '<button class=\"btn btn-sm btn-outline-primary me-1 btn-edit\" type=\"button\">Edit</button>' +
                        '<button class=\"btn btn-sm btn-outline-danger me-1 btn-del\" type=\"button\">Delete</button>' +
                        '<button class=\"btn btn-sm btn-outline-success btn-billed\" type=\"button\" ' + billedDisabled + '>Mark as Billed</button>';
                }},
            ],
        }));

        $('#filterClient,#filterStatus').on('change', function () {
            table.ajax.reload();
        });

        function openAdd() {
            $('#billableModalTitle').text('Add Billable Item');
            $('#billableForm')[0].reset();
            $('#billableForm').removeClass('was-validated');
            $('#bi_id').val('');
            $('#bi_entry_date').val(new Date().toISOString().slice(0, 10));
            $('#bi_quantity').val('1');
            $('#bi_unit_price').val('0');
            (function () {
                const d = new Date();
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const v = (months[d.getMonth()] || 'Jan') + ' ' + d.getFullYear();
                $('#bi_billing_month').val(v);
            })();
            $('#bi_status').val('Pending');
            setDescriptionHtml('<ul><li></li></ul>');
            setDescriptionValidity(true);
            clearBillableErrors();
            updateAmountPreview();
            $modal.show();
        }

        function openEdit(row) {
            $('#billableModalTitle').text('Edit Billable Item');
            $('#billableForm').removeClass('was-validated');
            $('#bi_id').val(row.id);
            $('#bi_entry_date').val(row.entry_date || '');
            $('#bi_client_id').val(row.client_id || '');
            setDescriptionHtml(bulletHtmlFromText(row.description || ''));
            setDescriptionValidity(true);
            $('#bi_quantity').val(row.quantity || '0');
            $('#bi_unit_price').val(row.unit_price || '0');
            $('#bi_billing_month').val(row.billing_month || '');
            $('#bi_status').val(row.status || 'Pending');
            clearBillableErrors();
            updateAmountPreview();
            $modal.show();
        }

        $('#btnAddBillable').on('click', openAdd);

        $('#btnSaveBillable').on('click', function () {
            clearBillableErrors();
            const form = document.getElementById('billableForm');
            const edNow = getTinyEditor();
            if (edNow && typeof edNow.save === 'function') {
                try { edNow.save(); } catch (e) {}
            } else if (hasTiny && window.tinymce) {
                try { window.tinymce.triggerSave(); } catch (e) {}
            }
            const descOk = getDescriptionText() !== '';
            setDescriptionValidity(descOk);
            form.classList.add('was-validated');
            if (!form.checkValidity() || !descOk) {
                notify('Please fill the required fields.', 'danger');
                return;
            }

            const payloadArr = $('#billableForm').serializeArray();
            const ed = getTinyEditor();
            if (ed) enforceBulletOnly(ed);
            const descHtml = getDescriptionHtml() || '<ul><li></li></ul>';
            for (let i = payloadArr.length - 1; i >= 0; i--) {
                if (payloadArr[i] && payloadArr[i].name === 'description') {
                    payloadArr.splice(i, 1);
                }
            }
            payloadArr.push({ name: 'description', value: descHtml });

            postJson('billable-items/save', payloadArr)
                .done(function (res) {
                    notify(res.message || 'Saved.', 'success');
                    $('#billableForm').removeClass('was-validated');
                    setDescriptionValidity(true);
                    $modal.hide();
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    const msg = res.message || 'Save failed.';
                    if (res.errors && typeof res.errors === 'object') {
                        const firstKey = Object.keys(res.errors)[0];
                        const firstErr = firstKey ? res.errors[firstKey] : null;
                        notify(firstErr ? (msg + ' ' + firstErr) : msg, 'danger');
                    } else {
                        notify(msg, 'danger');
                    }
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (k) {
                            if (k === 'description') {
                                setDescriptionValidity(false, res.errors[k]);
                                return;
                            }
                            const $field = $('#billableForm [name="' + k + '"]');
                            if ($field.length) $field.addClass('is-invalid');
                            const $err = $('#billableForm [data-err="' + k + '"]');
                            if ($err.length) $err.text(res.errors[k]);
                        });
                    }
                });
        });

        $('#dtBillableItems tbody').on('click', 'button.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            openEdit(row);
        });

        $('#dtBillableItems tbody').on('click', 'button.btn-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!confirm('Delete this billable item?')) return;
            postJson('billable-items/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#dtBillableItems tbody').on('click', 'button.btn-billed', function () {
            const row = table.row($(this).closest('tr')).data();
            if (row.status !== 'Pending') return;
            postJson('billable-items/mark-billed', { id: row.id })
                .done(function (res) { notify(res.message || 'Updated.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.', 'danger'); });
        });

        loadClients().always(function () {
            // ensure default client filter exists
            if (!$('#filterClient').val()) $('#filterClient').val('');
            if (!$('#bi_entry_date').val()) $('#bi_entry_date').val(new Date().toISOString().slice(0, 10));
            updateAmountPreview();
        });

        // If navigated from Dashboard "Edit", auto-open modal for that record.
        table.on('xhr', function () {
            if (!urlEditId || urlEditOpened) return;
            const rows = table.rows().data().toArray();
            const found = rows.find(function (r) { return parseInt(r.id, 10) === urlEditId; });
            if (found) {
                urlEditOpened = true;
                openEdit(found);
                try {
                    const u = new URL(window.location.href);
                    u.searchParams.delete('edit');
                    window.history.replaceState({}, document.title, u.toString());
                } catch (e) {}
            }
        });
    };

    BMS.initProformas = function () {
        const table = $('#dtProformas').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('proforma/list'), dataSrc: 'data' },
            order: [[0, 'desc']],
            columns: [
                { data: 'proforma_number' },
                { data: 'proforma_date', render: formatUiDate },
                { data: 'client_name' },
                { data: 'total_amount', className: 'text-end' },
                { data: 'status', orderable: false, render: function (d, t, row) {
                    return statusSelectHtml('proforma_invoices', row.id, d || 'Draft', ['Draft', 'Posted']);
                }},
                { data: null, orderable: false, render: function (row) {
                    return '' +
                        '<a class="btn btn-sm btn-outline-primary me-1" href="' + base('proforma/show/' + row.id) + '">View</a>' +
                        '<a class="btn btn-sm btn-outline-secondary me-1" href="' + base('proforma/edit/' + row.id) + '">Edit</a>' +
                        '<button class="btn btn-sm btn-outline-danger btn-pf-del" type="button">Delete</button>';
                }},
            ],
        }));

        $('#dtProformas tbody').on('click', 'button.btn-pf-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            if (!confirm('Delete this proforma invoice?')) return;
            postJson('proforma/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });
    };

    BMS.initProformaCreate = function () {
        let table = null;

        function recalcTotal() {
            let total = 0;
            $('#dtProformaItems input.pf-chk:checked').each(function () {
                const amt = parseFloat($(this).data('amount')) || 0;
                total += amt;
            });
            $('#pf_total').text(total.toFixed(2));
            $('#btnSaveProforma').prop('disabled', ($('#dtProformaItems input.pf-chk:checked').length === 0));
        }

        function initTable() {
            if (table) {
                table.destroy();
                $('#dtProformaItems').empty().append(
                    '<thead><tr>' +
                    '<th><input class="form-check-input" type="checkbox" id="pf_chkAll"></th>' +
                    '<th>Entry No</th><th>Date</th><th>Description</th><th>Qty</th><th>Unit Price</th><th>Amount</th><th>Billing Month</th>' +
                    '</tr></thead>'
                );
            }

            table = $('#dtProformaItems').DataTable($.extend(true, {}, dtDefaults(), {
                ajax: function (_data, callback) {
                    const clientId = $('#pf_client_id').val();
                    if (!clientId) {
                        callback({ data: [] });
                        return;
                    }
                    getJson('proforma/getPendingItems/' + clientId)
                        .done(function (res) { callback(res); })
                        .fail(function () { callback({ data: [] }); });
                },
                order: [[2, 'desc']],
                columns: [
                    { data: null, orderable: false, render: function (row) {
                        return '<input class="form-check-input pf-chk" type="checkbox" value="' + row.id + '" data-amount="' + row.amount + '">';
                    }},
                    { data: 'entry_no', render: function (d, t, row) { return d || ('BI-' + String(row.id).padStart(5, '0')); } },
                    { data: 'entry_date', render: formatUiDate },
                    { data: 'description', orderable: false, render: renderBulletText },
                    { data: 'quantity', className: 'text-end' },
                    { data: 'unit_price', className: 'text-end' },
                    { data: 'amount', className: 'text-end' },
                    { data: 'billing_month', render: function (d) { return d || '-'; } },
                ],
            }));

            $('#pf_chkAll').off('change').on('change', function () {
                const checked = $(this).is(':checked');
                $('#dtProformaItems input.pf-chk').prop('checked', checked);
                recalcTotal();
            });

            $('#dtProformaItems').off('change', 'input.pf-chk').on('change', 'input.pf-chk', recalcTotal);

            table.on('xhr', function () {
                $('#pf_chkAll').prop('checked', false);
                $('#pf_total').text('0.00');
                $('#btnSaveProforma').prop('disabled', true);
            });
        }

        $('#pf_client_id').on('change', function () {
            initTable();
        });

        initTable();

        $('#btnSaveProforma').on('click', function () {
            const clientId = $('#pf_client_id').val();
            const pfDate = ($('#pf_date').val() || '').trim();
            const ids = [];
            $('#dtProformaItems input.pf-chk:checked').each(function () { ids.push(parseInt($(this).val(), 10)); });
            if (!clientId) {
                notify('Client is required.', 'danger');
                return;
            }
            if (!pfDate) {
                notify('Proforma Date is required.', 'danger');
                return;
            }
            if (ids.length === 0) {
                notify('Select at least one billable item.', 'danger');
                return;
            }

            postJson('proforma/save', {
                client_id: clientId,
                item_ids: ids,
                proforma_date: pfDate,
                billing_from: $('#pf_from').val(),
                billing_to: $('#pf_to').val(),
            })
                .done(function (res) {
                    notify(res.message || 'Proforma created.', 'success');
                    if (res.proforma && res.proforma.id) {
                        window.location.href = base('proforma/show/' + res.proforma.id);
                    } else {
                        window.location.href = base('proforma');
                    }
                })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.', 'danger'); });
        });
    };

    BMS.initProformaEdit = function () {
        let table = null;

        function recalcTotal() {
            let total = 0;
            $('#dtProformaItems input.pf-chk:checked').each(function () {
                total += (parseFloat($(this).data('amount')) || 0);
            });
            $('#pf_total').text(total.toFixed(2));
            $('#btnUpdateProforma').prop('disabled', ($('#dtProformaItems input.pf-chk:checked').length === 0));
        }

        const proformaId = parseInt($('#pf_id').val(), 10) || 0;
        const clientId = $('#pf_client_id').val();

        table = $('#dtProformaItems').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: function (_data, callback) {
                if (!clientId || !proformaId) {
                    callback({ data: [] });
                    return;
                }
                // For edit, load pending items + currently included items (server returns included=1).
                getJson('proforma/edit-items', { client_id: clientId, proforma_id: proformaId })
                    .done(function (res) { callback(res); })
                    .fail(function () { callback({ data: [] }); });
            },
            order: [[2, 'desc']],
            columns: [
                { data: null, orderable: false, render: function (row) {
                    const checked = row.included ? 'checked' : '';
                    return '<input class="form-check-input pf-chk" type="checkbox" value="' + row.id + '" data-amount="' + row.amount + '" ' + checked + '>';
                }},
                { data: 'entry_no', render: function (d, t, row) { return d || ('BI-' + String(row.id).padStart(5, '0')); } },
                { data: 'entry_date', render: formatUiDate },
                { data: 'description', orderable: false, render: renderBulletText },
                { data: 'quantity', className: 'text-end' },
                { data: 'unit_price', className: 'text-end' },
                { data: 'amount', className: 'text-end' },
                { data: 'billing_month', render: function (d) { return d || '-'; } },
            ],
        }));

        $('#dtProformaItems').on('draw.dt', function () {
            $('#pf_chkAll').prop('checked', false);
            recalcTotal();
        });

        $('#pf_chkAll').off('change').on('change', function () {
            const checked = $(this).is(':checked');
            $('#dtProformaItems input.pf-chk').prop('checked', checked);
            recalcTotal();
        });

        $('#dtProformaItems').off('change', 'input.pf-chk').on('change', 'input.pf-chk', recalcTotal);

        $('#btnUpdateProforma').on('click', function () {
            const ids = [];
            $('#dtProformaItems input.pf-chk:checked').each(function () { ids.push(parseInt($(this).val(), 10)); });
            if (ids.length === 0) return;
            const pfDate = ($('#pf_date').val() || '').trim();
            if (!pfDate) {
                notify('Proforma Date is required.', 'danger');
                return;
            }
            const pfStatus = ($('#pf_status').val() || '').trim();
            if (!pfStatus) {
                notify('Status is required.', 'danger');
                return;
            }

            postJson('proforma/update', {
                proforma_id: proformaId,
                item_ids: ids,
                proforma_date: pfDate,
                billing_from: $('#pf_from').val(),
                billing_to: $('#pf_to').val(),
                status: pfStatus,
            })
                .done(function (res) {
                    notify(res.message || 'Updated.', 'success');
                    if (res.proforma && res.proforma.id) {
                        window.location.href = base('proforma/show/' + res.proforma.id);
                    }
                })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.', 'danger'); });
        });
    };

    $(function () {
        $.fn.dataTable && ($.fn.dataTable.defaults.language = { emptyTable: 'No records found.' });

        let sidebarTooltips = [];

        function closeSidebarCollapses() {
            document.querySelectorAll('.app-sidebar .collapse.show').forEach(function (el) {
                try {
                    bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).hide();
                } catch (e) {}
            });
        }

        function disableSidebarTooltips() {
            sidebarTooltips.forEach(function (t) {
                try { t.dispose(); } catch (e) {}
            });
            sidebarTooltips = [];
        }

        function enableSidebarTooltips() {
            disableSidebarTooltips();
            document.querySelectorAll('.app-sidebar [data-bms-title]').forEach(function (el) {
                const title = el.getAttribute('data-bms-title');
                if (!title) return;
                try {
                    sidebarTooltips.push(new bootstrap.Tooltip(el, {
                        title: title,
                        placement: 'right',
                        trigger: 'hover',
                        container: document.body,
                    }));
                } catch (e) {}
            });
        }

        function syncSidebarUI() {
            const collapsed = document.body.classList.contains('bms-sidebar-collapsed');
            if (collapsed) {
                closeSidebarCollapses();
                enableSidebarTooltips();
            } else {
                disableSidebarTooltips();
            }
        }

        try {
            const collapsed = localStorage.getItem(SIDEBAR_KEY) === '1';
            if (collapsed) {
                document.body.classList.add('bms-sidebar-collapsed');
                syncSidebarUI();
            }
        } catch (e) {}

        $('#btnToggleSidebar').on('click', function () {
            document.body.classList.toggle('bms-sidebar-collapsed');
            try {
                localStorage.setItem(SIDEBAR_KEY, document.body.classList.contains('bms-sidebar-collapsed') ? '1' : '0');
            } catch (e) {}
            syncSidebarUI();
        });

        // Prevent submenu toggles while collapsed (icons-only mode).
        $(document).on('click', '.app-sidebar a.nav-parent', function (e) {
            if (!document.body.classList.contains('bms-sidebar-collapsed')) return;
            e.preventDefault();
        });

        // Real-time status dropdown updates (all DataTables)
        $(document).on('change', 'select.bms-status-select', function () {
            const $sel = $(this);
            const tableName = $sel.data('table');
            const recordId = $sel.data('id');
            const statusValue = $sel.val();
            const oldVal = $sel.attr('data-old') || $sel.data('old') || null;

            $sel.prop('disabled', true);

            postJson('update-status', {
                table_name: tableName,
                record_id: recordId,
                status_value: statusValue,
            })
                .done(function (res) {
                    $sel.data('old', statusValue);
                    $sel.attr('data-old', statusValue);
                    notify((res && res.message) || 'Status updated successfully.', 'success');
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    notify(res.message || 'Status update failed.', 'danger');
                    if (oldVal !== null) {
                        $sel.val(oldVal);
                    }
                })
                .always(function () {
                    $sel.prop('disabled', false);
                });
        });
    });
})();
