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

        $permModel = new PermissionModel();
        $perms = $permModel->orderBy('module', 'ASC')->orderBy('label', 'ASC')->findAll();

        $selected = [];
        $rows = db_connect()->table('role_permissions')->select('permission_id')->where('role_id', $roleId)->get()->getResultArray();
        foreach ($rows as $r) {
            $selected[(int) ($r['permission_id'] ?? 0)] = true;
        }

        $byModule = [];
        foreach ($perms as $p) {
            $module = trim((string) ($p['module'] ?? ''));
            if ($module === '') $module = 'General';
            $byModule[$module][] = $p;
        }
        ksort($byModule);

        return view('access/roles/permissions', [
            'title'      => 'Assign Permissions',
            'active'     => 'roles',
            'role'       => $role,
            'isSuperRole'=> $isSuperRole,
            'byModule'   => $byModule,
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

