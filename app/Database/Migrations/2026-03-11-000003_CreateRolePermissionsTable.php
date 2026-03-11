<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolePermissionsTable extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('role_permissions')) {
            return;
        }

        if (! $this->db->tableExists('roles') || ! $this->db->tableExists('permissions')) {
            return;
        }

        $this->forge->addField([
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'permission_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey(['role_id', 'permission_id'], true);
        $this->forge->addKey('permission_id');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_permissions', true);
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}

