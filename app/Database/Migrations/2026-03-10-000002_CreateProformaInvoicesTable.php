<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Throwable;

class CreateProformaInvoicesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'proforma_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'client_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'proforma_date'   => ['type' => 'DATE'],
            'billing_from'    => ['type' => 'DATE', 'null' => true],
            'billing_to'      => ['type' => 'DATE', 'null' => true],
            'total_amount'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Draft'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('proforma_number');
        $this->forge->addKey(['client_id', 'proforma_date']);
        $this->forge->createTable('proforma_invoices', true, ['ENGINE' => 'InnoDB']);

        try {
            $this->db->query('ALTER TABLE `proforma_invoices` ADD CONSTRAINT `fk_bms_proforma_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT');
        } catch (Throwable $e) {
            // ignore (already exists / constraint name conflict from older installs)
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('proforma_invoices', true);
    }
}
