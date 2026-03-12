<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMobileAndStatusToAdmins extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('admins')) {
            return;
        }

        if (! $this->db->fieldExists('mobile', 'admins')) {
            $this->forge->addColumn('admins', [
                'mobile' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('status', 'admins')) {
            $this->forge->addColumn('admins', [
                'status' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 1,
                ],
            ]);
        }
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}

