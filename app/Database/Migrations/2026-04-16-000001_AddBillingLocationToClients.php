<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBillingLocationToClients extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('clients')) {
            return;
        }

        if (! $this->db->fieldExists('billing_city', 'clients')) {
            $this->forge->addColumn('clients', [
                'billing_city' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'billing_address',
                ],
            ]);
        }

        if (! $this->db->fieldExists('billing_state', 'clients')) {
            $this->forge->addColumn('clients', [
                'billing_state' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'billing_city',
                ],
            ]);
        }

        if (! $this->db->fieldExists('billing_country', 'clients')) {
            $this->forge->addColumn('clients', [
                'billing_country' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'billing_state',
                ],
            ]);
        }

        if (! $this->db->fieldExists('billing_postal_code', 'clients')) {
            $this->forge->addColumn('clients', [
                'billing_postal_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'billing_country',
                ],
            ]);
        }
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}
