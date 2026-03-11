<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedRbacDefaults extends Migration
{
    public function up(): void
    {
        if (
            ! $this->db->tableExists('admins') ||
            ! $this->db->tableExists('roles') ||
            ! $this->db->tableExists('permissions') ||
            ! $this->db->tableExists('admin_roles') ||
            ! $this->db->tableExists('role_permissions')
        ) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        // 1) Ensure a Super Admin role exists.
        $superRole = $this->db->table('roles')
            ->select('id,is_super')
            ->where('name', 'Super Admin')
            ->get()
            ->getRowArray();

        if (! $superRole) {
            $this->db->table('roles')->insert([
                'name'        => 'Super Admin',
                'description' => 'Full access to the system',
                'is_super'    => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $superRoleId = (int) $this->db->insertID();
        } else {
            $superRoleId = (int) $superRole['id'];
            if ((int) ($superRole['is_super'] ?? 0) !== 1) {
                $this->db->table('roles')->where('id', $superRoleId)->update(['is_super' => 1, 'updated_at' => $now]);
            }
        }

        // 2) Seed baseline permissions for access management module.
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
            $existing = $this->db->table('permissions')->select('id')->where('key', $p['key'])->get()->getRowArray();
            if ($existing) {
                $permissionIds[] = (int) $existing['id'];
                // Keep label/module up to date (safe).
                $this->db->table('permissions')->where('id', (int) $existing['id'])->update([
                    'label'      => $p['label'],
                    'module'     => $p['module'],
                    'updated_at' => $now,
                ]);
                continue;
            }

            $this->db->table('permissions')->insert([
                'key'         => $p['key'],
                'label'       => $p['label'],
                'module'      => $p['module'],
                'description' => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $permissionIds[] = (int) $this->db->insertID();
        }

        // 3) Grant Super Admin role all seeded permissions (for UI completeness).
        foreach ($permissionIds as $pid) {
            $row = $this->db->table('role_permissions')
                ->select('role_id')
                ->where('role_id', $superRoleId)
                ->where('permission_id', $pid)
                ->get()
                ->getRowArray();
            if ($row) continue;

            $this->db->table('role_permissions')->insert([
                'role_id'       => $superRoleId,
                'permission_id' => $pid,
            ]);
        }

        // 4) Preserve existing behavior: assign Super Admin role to ALL existing admins.
        $admins = $this->db->table('admins')->select('id')->get()->getResultArray();
        foreach ($admins as $a) {
            $adminId = (int) ($a['id'] ?? 0);
            if ($adminId <= 0) continue;

            $row = $this->db->table('admin_roles')
                ->select('admin_id')
                ->where('admin_id', $adminId)
                ->where('role_id', $superRoleId)
                ->get()
                ->getRowArray();
            if ($row) continue;

            $this->db->table('admin_roles')->insert([
                'admin_id' => $adminId,
                'role_id'  => $superRoleId,
            ]);
        }
    }

    public function down(): void
    {
        // no-op
    }
}

