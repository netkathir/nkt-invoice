<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Throwable;

class CreateInvoicesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'invoice_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'invoice_date'   => ['type' => 'DATE'],
            'client_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'proforma_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'total_amount'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'balance_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Unpaid'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('invoice_number');
        $this->forge->addKey(['client_id', 'invoice_date']);
        $this->forge->addKey('proforma_id');
        $this->forge->createTable('invoices', true, ['ENGINE' => 'InnoDB']);

        try {
            $this->db->query('ALTER TABLE `invoices` ADD CONSTRAINT `fk_bms_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT');
        } catch (Throwable $e) {
            // ignore
        }
        try {
            $this->db->query('ALTER TABLE `invoices` ADD CONSTRAINT `fk_bms_invoices_proforma` FOREIGN KEY (`proforma_id`) REFERENCES `proforma_invoices`(`id`) ON UPDATE CASCADE ON DELETE SET NULL');
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('invoices', true);
    }
}
