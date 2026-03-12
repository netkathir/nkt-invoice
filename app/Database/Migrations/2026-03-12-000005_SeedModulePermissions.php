<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedModulePermissions extends Migration
{
    public function up(): void
    {
        if (
            ! $this->db->tableExists('roles') ||
            ! $this->db->tableExists('permissions') ||
            ! $this->db->tableExists('role_permissions')
        ) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $baseline = [
            // SYSTEM ADMIN
            ['key' => 'users.view',   'label' => 'Users',            'module' => 'SYSTEM ADMIN'],
            ['key' => 'users.create', 'label' => 'Users',            'module' => 'SYSTEM ADMIN'],
            ['key' => 'users.edit',   'label' => 'Users',            'module' => 'SYSTEM ADMIN'],
            ['key' => 'users.delete', 'label' => 'Users',            'module' => 'SYSTEM ADMIN'],

            ['key' => 'roles.view',   'label' => 'Roles',            'module' => 'SYSTEM ADMIN'],
            ['key' => 'roles.create', 'label' => 'Roles',            'module' => 'SYSTEM ADMIN'],
            ['key' => 'roles.edit',   'label' => 'Roles',            'module' => 'SYSTEM ADMIN'],
            ['key' => 'roles.delete', 'label' => 'Roles',            'module' => 'SYSTEM ADMIN'],
            ['key' => 'roles.assign_perms', 'label' => 'Role Permissions', 'module' => 'SYSTEM ADMIN'],

            ['key' => 'permissions.view',   'label' => 'Permissions', 'module' => 'SYSTEM ADMIN'],
            ['key' => 'permissions.create', 'label' => 'Permissions', 'module' => 'SYSTEM ADMIN'],
            ['key' => 'permissions.edit',   'label' => 'Permissions', 'module' => 'SYSTEM ADMIN'],
            ['key' => 'permissions.delete', 'label' => 'Permissions', 'module' => 'SYSTEM ADMIN'],

            ['key' => 'role_permissions.view',   'label' => 'Role Permissions', 'module' => 'SYSTEM ADMIN'],
            ['key' => 'role_permissions.edit',   'label' => 'Role Permissions', 'module' => 'SYSTEM ADMIN'],
            ['key' => 'role_permissions.delete', 'label' => 'Role Permissions', 'module' => 'SYSTEM ADMIN'],

            // MASTERS
            ['key' => 'client_masters.view',   'label' => 'Client Masters', 'module' => 'MASTERS'],
            ['key' => 'client_masters.create', 'label' => 'Client Masters', 'module' => 'MASTERS'],
            ['key' => 'client_masters.edit',   'label' => 'Client Masters', 'module' => 'MASTERS'],
            ['key' => 'client_masters.delete', 'label' => 'Client Masters', 'module' => 'MASTERS'],

            // BILLABLE ITEMS
            ['key' => 'billable_items.view',   'label' => 'Billable Items', 'module' => 'BILLABLE ITEMS'],
            ['key' => 'billable_items.create', 'label' => 'Billable Items', 'module' => 'BILLABLE ITEMS'],
            ['key' => 'billable_items.edit',   'label' => 'Billable Items', 'module' => 'BILLABLE ITEMS'],
            ['key' => 'billable_items.delete', 'label' => 'Billable Items', 'module' => 'BILLABLE ITEMS'],
        ];

        $permissionIds = [];
        foreach ($baseline as $p) {
            $existing = $this->db->table('permissions')->select('id')->where('key', $p['key'])->get()->getRowArray();
            if ($existing) {
                $permissionIds[] = (int) $existing['id'];
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

        // Ensure Super Admin role has them all (for safety).
        $superRole = $this->db->table('roles')->select('id')->where('name', 'Super Admin')->get()->getRowArray();
        $superRoleId = (int) ($superRole['id'] ?? 0);
        if ($superRoleId <= 0) {
            return;
        }

        foreach (array_values(array_unique(array_filter($permissionIds))) as $pid) {
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
    }

    public function down(): void
    {
        // no-op
    }
}
