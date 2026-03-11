<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeClientNameNullable extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('clients')) {
            return;
        }

        // Make company name optional.
        $this->forge->modifyColumn('clients', [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
                'null'       => true,
            ],
        ]);
    }

    public function down(): void
    {
        // no-op
    }
}

