<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGstNoToClients extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('clients')) {
            return;
        }

        if (! $this->db->fieldExists('gst_no', 'clients')) {
            $this->forge->addColumn('clients', [
                'gst_no' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'phone',
                ],
            ]);
        }
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}
