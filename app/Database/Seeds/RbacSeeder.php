<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        if (! $db->tableExists('roles') || ! $db->tableExists('permissions')) {
            return;
        }

        $super = $db->table('roles')->select('id,is_super')->where('name', 'Super Admin')->get()->getRowArray();
        if (! $super) {
            $db->table('roles')->insert([
                'name'        => 'Super Admin',
                'description' => 'Full access to the system',
                'is_super'    => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $superRoleId = (int) $db->insertID();
        } else {
            $superRoleId = (int) ($super['id'] ?? 0);
            if ((int) ($super['is_super'] ?? 0) !== 1) {
                $db->table('roles')->where('id', $superRoleId)->update(['is_super' => 1, 'updated_at' => $now]);
            }
        }

        $baseline = [
            ['key' => 'roles.view',          'label' => 'View roles',               'module' => 'Access'],
            ['key' => 'roles.create',        'label' => 'Create roles',             'module' => 'Access'],
            ['key' => 'roles.edit',          'label' => 'Edit roles',               'module' => 'Access'],
            ['key' => 'roles.delete',        'label' => 'Delete roles',             'module' => 'Access'],
            ['key' => 'roles.assign_perms',  'label' => 'Assign permissions to roles', 'module' => 'Access'],
            ['key' => 'permissions.view',    'label' => 'View permissions',         'module' => 'Access'],
            ['key' => 'permissions.create',  'label' => 'Create permissions',       'module' => 'Access'],
            ['key' => 'permissions.edit',    'label' => 'Edit permissions',         'module' => 'Access'],
            ['key' => 'permissions.delete',  'label' => 'Delete permissions',       'module' => 'Access'],
            ['key' => 'admins.assign_roles', 'label' => 'Assign roles to admins',   'module' => 'Access'],
        ];

        $permissionIds = [];
        foreach ($baseline as $p) {
            $existing = $db->table('permissions')->select('id')->where('key', $p['key'])->get()->getRowArray();
            if ($existing) {
                $permissionIds[] = (int) $existing['id'];
                $db->table('permissions')->where('id', (int) $existing['id'])->update([
                    'label'      => $p['label'],
                    'module'     => $p['module'],
                    'updated_at' => $now,
                ]);
                continue;
            }

            $db->table('permissions')->insert([
                'key'         => $p['key'],
                'label'       => $p['label'],
                'module'      => $p['module'],
                'description' => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $permissionIds[] = (int) $db->insertID();
        }

        if ($db->tableExists('role_permissions')) {
            foreach ($permissionIds as $pid) {
                $row = $db->table('role_permissions')
                    ->select('role_id')
                    ->where('role_id', $superRoleId)
                    ->where('permission_id', $pid)
                    ->get()
                    ->getRowArray();
                if ($row) continue;
                $db->table('role_permissions')->insert(['role_id' => $superRoleId, 'permission_id' => $pid]);
            }
        }

        if ($db->tableExists('admins') && $db->tableExists('admin_roles')) {
            $admins = $db->table('admins')->select('id')->get()->getResultArray();
            foreach ($admins as $a) {
                $adminId = (int) ($a['id'] ?? 0);
                if ($adminId <= 0) continue;
                $row = $db->table('admin_roles')
                    ->select('admin_id')
                    ->where('admin_id', $adminId)
                    ->where('role_id', $superRoleId)
                    ->get()
                    ->getRowArray();
                if ($row) continue;
                $db->table('admin_roles')->insert(['admin_id' => $adminId, 'role_id' => $superRoleId]);
            }
        }
    }
}

