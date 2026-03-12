<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedUsersPermissions extends Migration
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
            ['key' => 'users.view',   'label' => 'View users',   'module' => 'Access'],
            ['key' => 'users.create', 'label' => 'Create users', 'module' => 'Access'],
            ['key' => 'users.edit',   'label' => 'Edit users',   'module' => 'Access'],
            ['key' => 'users.delete', 'label' => 'Delete users', 'module' => 'Access'],
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

        $superRole = $this->db->table('roles')->select('id')->where('name', 'Super Admin')->get()->getRowArray();
        $superRoleId = (int) ($superRole['id'] ?? 0);
        if ($superRoleId <= 0) {
            return;
        }

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
    }

    public function down(): void
    {
        // no-op
    }
}

