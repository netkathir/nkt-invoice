<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProformaPaymentsTable extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('proforma_payments')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'proforma_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'payment_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'payment_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'reference_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['proforma_id', 'payment_date']);
        $this->forge->addKey(['client_id', 'payment_date']);

        $this->forge->addForeignKey('proforma_id', 'proforma_invoices', 'id', 'CASCADE', 'CASCADE', 'fk_proforma_payments_proforma');
        $this->forge->addForeignKey('client_id', 'clients', 'id', 'CASCADE', 'RESTRICT', 'fk_proforma_payments_client');

        $this->forge->createTable('proforma_payments', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}

