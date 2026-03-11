<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Throwable;

class CreateBillableItemsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'entry_date'    => ['type' => 'DATE'],
            'client_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'description'   => ['type' => 'TEXT'],
            'quantity'      => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 1],
            'unit_price'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'amount'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'billing_month' => ['type' => 'CHAR', 'constraint' => 7, 'null' => true],
            'proforma_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'invoice_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Pending'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['client_id', 'entry_date']);
        $this->forge->addKey(['status', 'client_id']);
        $this->forge->addKey('proforma_id');
        $this->forge->addKey('invoice_id');
        $this->forge->createTable('billable_items', true, ['ENGINE' => 'InnoDB']);

        try {
            $this->db->query('ALTER TABLE `billable_items` ADD CONSTRAINT `fk_bms_billable_items_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT');
        } catch (Throwable $e) {
            // ignore
        }
        try {
            $this->db->query('ALTER TABLE `billable_items` ADD CONSTRAINT `fk_bms_billable_items_proforma` FOREIGN KEY (`proforma_id`) REFERENCES `proforma_invoices`(`id`) ON UPDATE CASCADE ON DELETE SET NULL');
        } catch (Throwable $e) {
            // ignore
        }
        try {
            $this->db->query('ALTER TABLE `billable_items` ADD CONSTRAINT `fk_bms_billable_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON UPDATE CASCADE ON DELETE SET NULL');
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('billable_items', true);
    }
}
