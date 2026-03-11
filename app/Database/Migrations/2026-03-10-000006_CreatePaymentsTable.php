<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Throwable;

class CreatePaymentsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'payment_date'     => ['type' => 'DATE'],
            'invoice_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'client_id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'payment_mode'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'amount'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'reference_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'remarks'          => ['type' => 'TEXT', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['client_id', 'payment_date']);
        $this->forge->addKey('invoice_id');
        $this->forge->createTable('payments', true, ['ENGINE' => 'InnoDB']);

        try {
            $this->db->query('ALTER TABLE `payments` ADD CONSTRAINT `fk_bms_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT');
        } catch (Throwable $e) {
            // ignore
        }
        try {
            $this->db->query('ALTER TABLE `payments` ADD CONSTRAINT `fk_bms_payments_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT');
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('payments', true);
    }
}
