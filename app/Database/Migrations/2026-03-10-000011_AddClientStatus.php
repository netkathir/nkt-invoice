<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClientStatus extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('clients')) {
            return;
        }

        if (! $this->db->fieldExists('status', 'clients')) {
            $this->forge->addColumn('clients', [
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => false,
                    'default'    => 'Active',
                    'after'      => 'phone',
                ],
            ]);
        }

        $this->db->query("UPDATE `clients` SET `status` = 'Active' WHERE `status` IS NULL OR `status` = ''");
    }

    public function down(): void
    {
        // no-op
    }
}

