<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaxFieldsToProformaInvoices extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('proforma_invoices')) {
            return;
        }

        $columns = [];

        if (! $this->db->fieldExists('invoice_type', 'proforma_invoices')) {
            $columns['invoice_type'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'proforma_date',
            ];
        }

        if (! $this->db->fieldExists('gst_percent', 'proforma_invoices')) {
            $columns['gst_percent'] = [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'after'      => 'currency',
            ];
        }

        if (! $this->db->fieldExists('gst_mode', 'proforma_invoices')) {
            $columns['gst_mode'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'gst_percent',
            ];
        }

        if (! $this->db->fieldExists('cgst_amount', 'proforma_invoices')) {
            $columns['cgst_amount'] = [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'gst_mode',
            ];
        }

        if (! $this->db->fieldExists('sgst_amount', 'proforma_invoices')) {
            $columns['sgst_amount'] = [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'cgst_amount',
            ];
        }

        if (! $this->db->fieldExists('igst_amount', 'proforma_invoices')) {
            $columns['igst_amount'] = [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'sgst_amount',
            ];
        }

        if (! $this->db->fieldExists('total_gst', 'proforma_invoices')) {
            $columns['total_gst'] = [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'igst_amount',
            ];
        }

        if (! $this->db->fieldExists('net_amount', 'proforma_invoices')) {
            $columns['net_amount'] = [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'total_gst',
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn('proforma_invoices', $columns);
        }
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}

