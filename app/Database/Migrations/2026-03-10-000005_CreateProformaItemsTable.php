<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Throwable;

class CreateProformaItemsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'proforma_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'billable_item_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'amount'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('proforma_id');
        $this->forge->addKey('billable_item_id');
        $this->forge->createTable('proforma_items', true, ['ENGINE' => 'InnoDB']);

        try {
            $this->db->query('ALTER TABLE `proforma_items` ADD CONSTRAINT `fk_bms_proforma_items_proforma` FOREIGN KEY (`proforma_id`) REFERENCES `proforma_invoices`(`id`) ON UPDATE CASCADE ON DELETE CASCADE');
        } catch (Throwable $e) {
            // ignore
        }
        try {
            $this->db->query('ALTER TABLE `proforma_items` ADD CONSTRAINT `fk_bms_proforma_items_billable` FOREIGN KEY (`billable_item_id`) REFERENCES `billable_items`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT');
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('proforma_items', true);
    }
}
