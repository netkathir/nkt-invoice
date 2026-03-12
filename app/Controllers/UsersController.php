<?php

namespace App\Controllers;

use App\Libraries\Authz;
use Throwable;

class UsersController extends BaseController
{
    private function authz(): Authz
    {
        return new Authz(db_connect(), service('session'));
    }

    private function requireSuperAdminJson()
    {
        if ($this->authz()->isSuperAdmin()) {
            return null;
        }

        return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
    }

    private function generateUsername(string $email): string
    {
        $email = trim(strtolower($email));
        $local = explode('@', $email)[0] ?? '';
        $base = preg_replace('/[^a-z0-9_]+/', '', $local);
        $base = trim((string) $base);
        if ($base === '') {
            $base = 'user';
        }

        $base = substr($base, 0, 40);
        $db = db_connect();
        $candidate = $base;
        $i = 1;
        while (true) {
            $exists = (bool) $db->table('admins')
                ->select('id')
                ->where('username', $candidate)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (! $exists) {
                return $candidate;
            }

            $suffix = (string) $i;
            $candidate = substr($base, 0, 50 - strlen($suffix)) . $suffix;
            $i++;
            if ($i > 9999) {
                return $candidate;
            }
        }
    }

    private function isLastSuperAdmin(int $adminId): bool
    {
        $db = db_connect();

        $isSuper = (bool) $db->table('admin_roles ar')
            ->select('ar.admin_id')
            ->join('roles r', 'r.id = ar.role_id', 'inner')
            ->where('ar.admin_id', $adminId)
            ->where('r.is_super', 1)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! $isSuper) {
            return false;
        }

        $otherSuperCount = $db->table('admin_roles ar')
            ->join('roles r', 'r.id = ar.role_id', 'inner')
            ->where('r.is_super', 1)
            ->where('ar.admin_id !=', $adminId)
            ->countAllResults();

        return $otherSuperCount === 0;
    }

    public function index()
    {
        if ($resp = $this->authz()->require('users.view')) {
            return $resp;
        }

        $roles = db_connect()
            ->table('roles')
            ->select('id,name,is_super')
            ->orderBy('is_super', 'DESC')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $isSuper = $this->authz()->isSuperAdmin();

        return view('access/users/list', [
            'title'     => 'Users',
            'active'    => 'users',
            'roles'     => $roles,
            'canCreate' => $isSuper && $this->authz()->can('users.create'),
            'canEdit'   => $isSuper && $this->authz()->can('users.edit'),
            'canDelete' => $isSuper && $this->authz()->can('users.delete'),
        ]);
    }

    public function list()
    {
        if (! $this->authz()->can('users.view')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $rows = db_connect()
            ->table('admins a')
            ->select('a.id, a.name, a.email, a.mobile, a.status')
            ->select("(SELECT r.name FROM admin_roles ar JOIN roles r ON r.id = ar.role_id WHERE ar.admin_id = a.id ORDER BY r.is_super DESC, r.name ASC LIMIT 1) AS role_name", false)
            ->select("(SELECT r.is_super FROM admin_roles ar JOIN roles r ON r.id = ar.role_id WHERE ar.admin_id = a.id ORDER BY r.is_super DESC, r.name ASC LIMIT 1) AS role_is_super", false)
            ->orderBy('a.id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }

    public function get(int $id)
    {
        if (! $this->authz()->can('users.view')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $admin = db_connect()
            ->table('admins')
            ->select('id, name, email, mobile, status')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (! $admin) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'User not found.']);
        }

        $roleRow = db_connect()
            ->table('admin_roles ar')
            ->select('ar.role_id')
            ->join('roles r', 'r.id = ar.role_id', 'inner')
            ->where('ar.admin_id', $id)
            ->orderBy('r.is_super', 'DESC')
            ->orderBy('r.name', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $admin['role_id'] = (int) ($roleRow['role_id'] ?? 0);

        return $this->response->setJSON(['success' => true, 'data' => $admin]);
    }

    public function save()
    {
        if ($resp = $this->requireSuperAdminJson()) {
            return $resp;
        }

        $id = (int) $this->request->getPost('id');
        $isEdit = $id > 0;

        $permission = $isEdit ? 'users.edit' : 'users.create';
        if (! $this->authz()->can($permission)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $mobile = trim((string) $this->request->getPost('mobile'));
        $status = (int) $this->request->getPost('status') === 1 ? 1 : 0;
        $roleId = (int) $this->request->getPost('role_id');

        $password = (string) $this->request->getPost('password');
        $confirm = (string) $this->request->getPost('confirm_password');

        $rules = [
            'name'   => 'required|min_length[2]|max_length[191]',
            'email'  => 'required|valid_email|max_length[191]|is_unique[admins.email,id,' . $id . ']',
            'mobile' => 'permit_empty|max_length[20]',
            'status' => 'required|in_list[0,1]',
            'role_id'=> 'required|is_not_unique[roles.id]',
        ];

        if (! $isEdit) {
            $rules['password'] = 'required|min_length[8]';
            $rules['confirm_password'] = 'required|matches[password]';
        } elseif ($password !== '' || $confirm !== '') {
            $rules['password'] = 'required|min_length[8]';
            $rules['confirm_password'] = 'required|matches[password]';
        }

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Please fix the validation errors.',
                'errors'  => service('validation')->getErrors(),
            ]);
        }

        if ($isEdit && $this->isLastSuperAdmin($id) && $status === 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'You cannot deactivate the last Super Admin.',
            ]);
        }

        $db = db_connect();
        $db->transStart();
        try {
            $payload = [
                'name'   => $name,
                'email'  => $email,
                'mobile' => $mobile !== '' ? $mobile : null,
                'status' => $status,
            ];

            if (! $isEdit) {
                $payload['username'] = $this->generateUsername($email);
            }

            if ($password !== '') {
                $payload['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($isEdit) {
                $db->table('admins')->where('id', $id)->update($payload);
            } else {
                $db->table('admins')->insert($payload);
                $id = (int) $db->insertID();
            }

            $db->table('admin_roles')->where('admin_id', $id)->delete();
            $db->table('admin_roles')->insert(['admin_id' => $id, 'role_id' => $roleId]);

            $db->transComplete();
        } catch (Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $isEdit ? 'User updated.' : 'User created.',
        ]);
    }

    public function delete()
    {
        if ($resp = $this->requireSuperAdminJson()) {
            return $resp;
        }

        if (! $this->authz()->can('users.delete')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid user.']);
        }

        $currentId = $this->authz()->currentAdminId();
        if ($currentId > 0 && $id === $currentId) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'You cannot delete your own account.']);
        }

        if ($this->isLastSuperAdmin($id)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'You cannot delete the last Super Admin.']);
        }

        try {
            db_connect()->table('admins')->where('id', $id)->delete();
            return $this->response->setJSON(['success' => true, 'message' => 'User deleted.']);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

