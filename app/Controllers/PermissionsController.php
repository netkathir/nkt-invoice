<?php

namespace App\Controllers;

use App\Libraries\Authz;
use App\Models\PermissionModel;
use Throwable;

class PermissionsController extends BaseController
{
    private function authz(): Authz
    {
        return new Authz(db_connect(), service('session'));
    }

    public function index()
    {
        if ($resp = $this->authz()->require('permissions.view')) {
            return $resp;
        }

        return view('access/permissions/list', [
            'title'  => 'Permissions',
            'active' => 'permissions',
        ]);
    }

    public function list()
    {
        if (! $this->authz()->can('permissions.view')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $authz = $this->authz();
        $adminId = $authz->currentAdminId();

        $b = db_connect()
            ->table('permissions p')
            ->select('p.id, p.key, p.label, p.module, p.description, p.created_at');

        // "Forms" listing: show only actual app modules/forms (exclude Access/RBAC items and blank modules).
        // Expected rows are created with a meaningful `module` like "Client Master", "Billable Items", etc.
        $b->where("p.key NOT LIKE '%.%'", null, false);
        $b->where("p.module IS NOT NULL", null, false);
        $b->where("TRIM(p.module) <> ''", null, false);
        $b->where("p.module <> 'Access'", null, false);

        if (! $authz->isSuperAdmin($adminId) && $adminId > 0) {
            $b->join('role_permissions rp', 'rp.permission_id = p.id', 'inner')
                ->join('admin_roles ar', 'ar.role_id = rp.role_id', 'inner')
                ->where('ar.admin_id', $adminId)
                ->groupBy('p.id');
        }

        $rows = $b
            ->orderBy('p.module', 'ASC')
            ->orderBy('p.key', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }

    public function save()
    {
        $id = (int) $this->request->getPost('id');
        $isEdit = $id > 0;

        $permission = $isEdit ? 'permissions.edit' : 'permissions.create';
        if (! $this->authz()->can($permission)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $permModel = new PermissionModel();

        $key = trim((string) $this->request->getPost('key'));
        $label = trim((string) $this->request->getPost('label'));
        $module = trim((string) $this->request->getPost('module'));
        $description = trim((string) $this->request->getPost('description'));

        $rules = [
            'key'   => 'required|max_length[191]|is_unique[permissions.key,id,' . $id . ']',
            'label' => 'required|max_length[191]',
            'module'=> 'permit_empty|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Please fix the validation errors.',
                'errors'  => service('validation')->getErrors(),
            ]);
        }

        $payload = [
            'key'         => $key,
            'label'       => $label,
            'module'      => $module !== '' ? $module : null,
            'description' => $description !== '' ? $description : null,
        ];
        if ($isEdit) {
            $payload['id'] = $id;
        }

        try {
            if (! $permModel->save($payload)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please fix the validation errors.',
                    'errors'  => $permModel->errors(),
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $isEdit ? 'Permission updated.' : 'Permission created.',
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
        if (! $this->authz()->can('permissions.delete')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid permission.']);
        }

        $permModel = new PermissionModel();
        $perm = $permModel->find($id);
        if (! $perm) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Permission not found.']);
        }

        $assigned = db_connect()->table('role_permissions')->where('permission_id', $id)->countAllResults();
        if ($assigned > 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'This permission is assigned to roles and cannot be deleted.',
            ]);
        }

        try {
            $permModel->delete($id);
            return $this->response->setJSON(['success' => true, 'message' => 'Permission deleted.']);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
