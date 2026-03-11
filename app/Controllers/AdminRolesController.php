<?php

namespace App\Controllers;

use App\Libraries\Authz;
use App\Models\AdminModel;
use App\Models\RoleModel;
use Throwable;

class AdminRolesController extends BaseController
{
    private function authz(): Authz
    {
        return new Authz(db_connect(), service('session'));
    }

    public function index()
    {
        if ($resp = $this->authz()->require('admins.assign_roles')) {
            return $resp;
        }

        $admins = db_connect()
            ->table('admins a')
            ->select('a.id, a.name, a.email, a.username, a.created_at')
            ->orderBy('a.id', 'DESC')
            ->get()
            ->getResultArray();

        $rolesByAdmin = [];
        $rows = db_connect()
            ->table('admin_roles ar')
            ->select('ar.admin_id, r.name, r.is_super')
            ->join('roles r', 'r.id = ar.role_id', 'inner')
            ->orderBy('r.is_super', 'DESC')
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();
        foreach ($rows as $r) {
            $aid = (int) ($r['admin_id'] ?? 0);
            if ($aid <= 0) continue;
            $rolesByAdmin[$aid][] = $r;
        }

        return view('access/admin_roles/list', [
            'title'        => 'Admin Role Assignment',
            'active'       => 'admin_roles',
            'admins'       => $admins,
            'rolesByAdmin' => $rolesByAdmin,
        ]);
    }

    public function edit(int $adminId)
    {
        if ($resp = $this->authz()->require('admins.assign_roles')) {
            return $resp;
        }

        $admin = (new AdminModel())->find($adminId);
        if (! $admin) {
            return redirect()->to(base_url('admin-roles'))->with('error', 'Admin not found.');
        }

        $roles = (new RoleModel())->orderBy('is_super', 'DESC')->orderBy('name', 'ASC')->findAll();

        $selected = [];
        $rows = db_connect()->table('admin_roles')->select('role_id')->where('admin_id', $adminId)->get()->getResultArray();
        foreach ($rows as $r) {
            $selected[(int) ($r['role_id'] ?? 0)] = true;
        }

        return view('access/admin_roles/edit', [
            'title'    => 'Assign Roles',
            'active'   => 'admin_roles',
            'admin'    => $admin,
            'roles'    => $roles,
            'selected' => $selected,
        ]);
    }

    public function update(int $adminId)
    {
        if (! $this->authz()->can('admins.assign_roles')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $admin = (new AdminModel())->find($adminId);
        if (! $admin) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Admin not found.']);
        }

        $roleIds = (array) $this->request->getPost('role_ids');
        $roleIds = array_values(array_unique(array_filter(array_map('intval', $roleIds))));
        if ($roleIds === []) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Select at least one role.']);
        }

        // Safety: prevent removing Super Admin from the last remaining super admin.
        $superRoleIds = array_map(
            static fn ($r) => (int) ($r['id'] ?? 0),
            db_connect()->table('roles')->select('id')->where('is_super', 1)->get()->getResultArray()
        );
        $superRoleIds = array_values(array_filter($superRoleIds));

        if ($superRoleIds !== []) {
            $keepsSuper = count(array_intersect($roleIds, $superRoleIds)) > 0;
            if (! $keepsSuper) {
                $otherSuperCount = db_connect()
                    ->table('admin_roles ar')
                    ->join('roles r', 'r.id = ar.role_id', 'inner')
                    ->where('r.is_super', 1)
                    ->where('ar.admin_id !=', $adminId)
                    ->countAllResults();
                if ($otherSuperCount === 0) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'success' => false,
                        'message' => 'You must keep at least one Super Admin in the system.',
                    ]);
                }
            }
        }

        $db = db_connect();
        $db->transStart();
        try {
            $db->table('admin_roles')->where('admin_id', $adminId)->delete();
            $batch = [];
            foreach ($roleIds as $rid) {
                $batch[] = ['admin_id' => $adminId, 'role_id' => $rid];
            }
            $db->table('admin_roles')->insertBatch($batch);
            $db->transComplete();
        } catch (Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Roles updated.']);
    }
}

