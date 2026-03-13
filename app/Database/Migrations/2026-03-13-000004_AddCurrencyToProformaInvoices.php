<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCurrencyToProformaInvoices extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('proforma_invoices')) {
            return;
        }

        if ($this->db->fieldExists('currency', 'proforma_invoices')) {
            return;
        }

        $this->forge->addColumn('proforma_invoices', [
            'currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'billing_to',
            ],
        ]);
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}

