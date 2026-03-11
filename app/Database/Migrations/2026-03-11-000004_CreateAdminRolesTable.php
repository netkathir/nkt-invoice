<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminRolesTable extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('admin_roles')) {
            return;
        }

        if (! $this->db->tableExists('admins') || ! $this->db->tableExists('roles')) {
            return;
        }

        $this->forge->addField([
            'admin_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey(['admin_id', 'role_id'], true);
        $this->forge->addKey('role_id');
        $this->forge->addForeignKey('admin_id', 'admins', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admin_roles', true);
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}

