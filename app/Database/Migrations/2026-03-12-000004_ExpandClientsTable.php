<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExpandClientsTable extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('clients')) {
            return;
        }

        if (! $this->db->fieldExists('client_code', 'clients')) {
            $this->forge->addColumn('clients', [
                'client_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'id',
                ],
            ]);
        }

        if (! $this->db->fieldExists('address', 'clients')) {
            $this->forge->addColumn('clients', [
                'address' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after'=> 'phone',
                ],
            ]);
        }

        if (! $this->db->fieldExists('billing_address', 'clients')) {
            $this->forge->addColumn('clients', [
                'billing_address' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after'=> 'address',
                ],
            ]);
        }

        if (! $this->db->fieldExists('city', 'clients')) {
            $this->forge->addColumn('clients', [
                'city' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'billing_address',
                ],
            ]);
        }

        if (! $this->db->fieldExists('state', 'clients')) {
            $this->forge->addColumn('clients', [
                'state' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'city',
                ],
            ]);
        }

        if (! $this->db->fieldExists('country', 'clients')) {
            $this->forge->addColumn('clients', [
                'country' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'state',
                ],
            ]);
        }

        if (! $this->db->fieldExists('postal_code', 'clients')) {
            $this->forge->addColumn('clients', [
                'postal_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'country',
                ],
            ]);
        }
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}

