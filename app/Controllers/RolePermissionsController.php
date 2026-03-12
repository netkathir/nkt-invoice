<?php

namespace App\Controllers;

use App\Libraries\Authz;

class RolePermissionsController extends BaseController
{
    private function authz(): Authz
    {
        return new Authz(db_connect(), service('session'));
    }

    public function index()
    {
        if ($resp = $this->authz()->require('roles.assign_perms')) {
            return $resp;
        }

        return view('access/role_permissions/list', [
            'title'  => 'Role Permissions',
            'active' => 'role_permissions',
        ]);
    }

    public function list()
    {
        if (! $this->authz()->can('roles.assign_perms')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $rows = db_connect()
            ->table('roles r')
            ->select('r.id, r.name, r.description, r.is_super, r.created_at')
            ->orderBy('r.is_super', 'DESC')
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }
}

