<?php

namespace App\Controllers;

use App\Libraries\Authz;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use Throwable;

class RolesController extends BaseController
{
    private function authz(): Authz
    {
        return new Authz(db_connect(), service('session'));
    }

    /**
     * Ensure all permission keys referenced by ModuleRegistry::$permissionMatrix
     * exist in the database (idempotent). This prevents "Not seeded" rows on
     * Role Permissions page when migrations haven't been run on a target server.
     *
     * @param array<string, array<string, array{read?:list<string>,write?:list<string>,delete?:list<string>}>> $spec
     */
    private function ensurePermissionMatrixSeeded(array $spec): void
    {
        $db = db_connect();
        if (! $db->tableExists('permissions')) {
            return;
        }

        $metaByKey = [];
        foreach ($spec as $module => $pages) {
            foreach ($pages as $label => $levels) {
                foreach (['read', 'write', 'delete'] as $lvl) {
                    foreach ((array) ($levels[$lvl] ?? []) as $k) {
                        $k = trim((string) $k);
                        if ($k === '' || ! str_contains($k, '.')) {
                            continue;
                        }
                        if (! isset($metaByKey[$k])) {
                            $metaByKey[$k] = [
                                'key'    => $k,
                                'label'  => (string) $label,
                                'module' => (string) $module,
                            ];
                        }
                    }
                }
            }
        }

        if ($metaByKey === []) {
            return;
        }

        try {
            $keys = array_keys($metaByKey);
            $existingRows = $db->table('permissions')
                ->select(['id', 'key'])
                ->whereIn('key', $keys)
                ->get()
                ->getResultArray();

            $existingKeys = [];
            foreach ($existingRows as $r) {
                $ek = (string) ($r['key'] ?? '');
                if ($ek !== '') {
                    $existingKeys[$ek] = true;
                }
            }

            $now = date('Y-m-d H:i:s');
            $toInsert = [];
            foreach ($metaByKey as $k => $meta) {
                if (isset($existingKeys[$k])) {
                    continue;
                }
                $toInsert[] = [
                    'key'         => $meta['key'],
                    'label'       => $meta['label'],
                    'module'      => $meta['module'],
                    'description' => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            if ($toInsert !== []) {
                $db->table('permissions')->insertBatch($toInsert);
            }

            // Best-effort: ensure Super Admin has all matrix permissions.
            if ($db->tableExists('roles') && $db->tableExists('role_permissions')) {
                $super = $db->table('roles')->select('id')->where('name', 'Super Admin')->get()->getRowArray();
                $superRoleId = (int) ($super['id'] ?? 0);
                if ($superRoleId > 0) {
                    $permRows = $db->table('permissions')
                        ->select('id')
                        ->whereIn('key', $keys)
                        ->get()
                        ->getResultArray();
                    $permIds = array_values(array_unique(array_filter(array_map(
                        static fn ($row) => (int) ($row['id'] ?? 0),
                        $permRows
                    ))));

                    if ($permIds !== []) {
                        $assignedRows = $db->table('role_permissions')
                            ->select('permission_id')
                            ->where('role_id', $superRoleId)
                            ->whereIn('permission_id', $permIds)
                            ->get()
                            ->getResultArray();

                        $assigned = [];
                        foreach ($assignedRows as $ar) {
                            $pid = (int) ($ar['permission_id'] ?? 0);
                            if ($pid > 0) {
                                $assigned[$pid] = true;
                            }
                        }

                        $rpInsert = [];
                        foreach ($permIds as $pid) {
                            if (isset($assigned[$pid])) {
                                continue;
                            }
                            $rpInsert[] = ['role_id' => $superRoleId, 'permission_id' => $pid];
                        }
                        if ($rpInsert !== []) {
                            $db->table('role_permissions')->insertBatch($rpInsert);
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            // Ignore and let the UI show "Not seeded" warning if the DB can't be written.
            return;
        }
    }

    public function index()
    {
        if ($resp = $this->authz()->require('roles.view')) {
            return $resp;
        }

        return view('access/roles/list', [
            'title'  => 'Roles',
            'active' => 'roles',
        ]);
    }

    public function list()
    {
        if (! $this->authz()->can('roles.view')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $rows = db_connect()
            ->table('roles r')
            ->select('r.id, r.name, r.description, r.is_super, r.created_at')
            ->select('(SELECT COUNT(*) FROM admin_roles ar WHERE ar.role_id = r.id) AS admins_count', false)
            ->select('(SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) AS permissions_count', false)
            ->orderBy('r.is_super', 'DESC')
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }

    public function save()
    {
        $id = (int) $this->request->getPost('id');
        $isEdit = $id > 0;

        $permission = $isEdit ? 'roles.edit' : 'roles.create';
        if (! $this->authz()->can($permission)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $roleModel = new RoleModel();

        $name = trim((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));

        $isSuper = (int) $this->request->getPost('is_super') === 1 ? 1 : 0;
        if (! $this->authz()->isSuperAdmin()) {
            $isSuper = 0;
        }

        if ($isEdit) {
            $existing = $roleModel->find($id);
            if (! $existing) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Role not found.']);
            }

            if (strtolower((string) ($existing['name'] ?? '')) === 'super admin') {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Super Admin role cannot be modified.',
                ]);
            }
        } else {
            if (strtolower($name) === 'super admin') {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Role name "Super Admin" is reserved.',
                ]);
            }
        }

        $rules = [
            'name' => 'required|max_length[191]|is_unique[roles.name,id,' . $id . ']',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Please fix the validation errors.',
                'errors'  => service('validation')->getErrors(),
            ]);
        }

        $payload = [
            'name'        => $name,
            'description' => $description !== '' ? $description : null,
            'is_super'    => $isSuper,
        ];
        if ($isEdit) {
            $payload['id'] = $id;
        }

        try {
            if (! $roleModel->save($payload)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please fix the validation errors.',
                    'errors'  => $roleModel->errors(),
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $isEdit ? 'Role updated.' : 'Role created.',
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete()
    {
        if (! $this->authz()->can('roles.delete')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid role.']);
        }

        $roleModel = new RoleModel();
        $role = $roleModel->find($id);
        if (! $role) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Role not found.']);
        }

        if ((int) ($role['is_super'] ?? 0) === 1 || strtolower((string) ($role['name'] ?? '')) === 'super admin') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Super Admin role cannot be deleted.']);
        }

        $assigned = db_connect()->table('admin_roles')->where('role_id', $id)->countAllResults();
        if ($assigned > 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'This role is assigned to admins and cannot be deleted.',
            ]);
        }

        try {
            $roleModel->delete($id);
            return $this->response->setJSON(['success' => true, 'message' => 'Role deleted.']);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function permissions(int $roleId)
    {
        if ($resp = $this->authz()->require('roles.assign_perms')) {
            return $resp;
        }

        $roleModel = new RoleModel();
        $role = $roleModel->find($roleId);
        if (! $role) {
            return redirect()->to(base_url('roles'))->with('error', 'Role not found.');
        }

        $isSuperRole = (int) ($role['is_super'] ?? 0) === 1 || strtolower((string) ($role['name'] ?? '')) === 'super admin';

        $rolesList = $roleModel->orderBy('is_super', 'DESC')->orderBy('name', 'ASC')->findAll();

        $selected = [];
        $rows = db_connect()->table('role_permissions')->select('permission_id')->where('role_id', $roleId)->get()->getResultArray();
        foreach ($rows as $r) {
            $selected[(int) ($r['permission_id'] ?? 0)] = true;
        }

        // Only show modules that exist in this project (no placeholders).
        $registry = config('ModuleRegistry');
        $spec = is_object($registry) && property_exists($registry, 'permissionMatrix')
            ? (array) $registry->permissionMatrix
            : [];

        // Prevent "Not seeded" warnings when new permission keys were deployed
        // but migrations were not re-run on the server.
        if ($spec !== []) {
            $this->ensurePermissionMatrixSeeded($spec);
        }

        $permModel = new PermissionModel();
        $perms = $permModel->orderBy('module', 'ASC')->orderBy('label', 'ASC')->findAll();

        $idByKey = [];
        foreach ($perms as $p) {
            $k = trim((string) ($p['key'] ?? ''));
            $pid = (int) ($p['id'] ?? 0);
            if ($k !== '' && $pid > 0) {
                $idByKey[$k] = $pid;
            }
        }

        /** @var array<string, array<string, array{page:string,label:string,read:list<int>,write:list<int>,delete:list<int>}>> $byModulePages */
        $byModulePages = [];
        foreach ($spec as $module => $pages) {
            foreach ($pages as $label => $levels) {
                $readIds = [];
                foreach (($levels['read'] ?? []) as $k) if (isset($idByKey[$k])) $readIds[] = (int) $idByKey[$k];
                $writeIds = [];
                foreach (($levels['write'] ?? []) as $k) if (isset($idByKey[$k])) $writeIds[] = (int) $idByKey[$k];
                $deleteIds = [];
                foreach (($levels['delete'] ?? []) as $k) if (isset($idByKey[$k])) $deleteIds[] = (int) $idByKey[$k];

                $pageKey = strtolower(preg_replace('/\\s+/', '_', (string) $label));
                $byModulePages[$module][$pageKey] = [
                    'page'   => $pageKey,
                    'label'  => $label,
                    'read'   => array_values(array_unique(array_filter($readIds))),
                    'write'  => array_values(array_unique(array_filter($writeIds))),
                    'delete' => array_values(array_unique(array_filter($deleteIds))),
                ];
            }
        }

        return view('access/roles/permissions', [
            'title'      => 'Assign Permissions',
            'active'     => 'role_permissions',
            'role'       => $role,
            'isSuperRole'=> $isSuperRole,
            'rolesList'  => $rolesList,
            'byModulePages' => $byModulePages,
            'selected'   => $selected,
        ]);
    }

    public function savePermissions(int $roleId)
    {
        if (! $this->authz()->can('roles.assign_perms')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $roleModel = new RoleModel();
        $role = $roleModel->find($roleId);
        if (! $role) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Role not found.']);
        }

        $isSuperRole = (int) ($role['is_super'] ?? 0) === 1 || strtolower((string) ($role['name'] ?? '')) === 'super admin';
        if ($isSuperRole) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Super Admin role permissions cannot be modified.']);
        }

        $permissionIds = (array) $this->request->getPost('permission_ids');
        $permissionIds = array_values(array_unique(array_filter(array_map('intval', $permissionIds))));

        $db = db_connect();
        $db->transStart();
        try {
            $db->table('role_permissions')->where('role_id', $roleId)->delete();
            if ($permissionIds !== []) {
                $batch = [];
                foreach ($permissionIds as $pid) {
                    $batch[] = ['role_id' => $roleId, 'permission_id' => $pid];
                }
                $db->table('role_permissions')->insertBatch($batch);
            }
            $db->transComplete();
        } catch (Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Permissions updated.']);
    }
}
