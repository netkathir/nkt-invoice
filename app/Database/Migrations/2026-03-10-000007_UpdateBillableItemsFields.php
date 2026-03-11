<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Throwable;

class UpdateBillableItemsFields extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('billable_items')) {
            return;
        }

        // entry_no (unique entry number)
        if (! $this->db->fieldExists('entry_no', 'billable_items')) {
            $this->forge->addColumn('billable_items', [
                'entry_no' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'id',
                ],
            ]);
        }

        // Ensure columns exist (future-safe for deployments created from older schema)
        if (! $this->db->fieldExists('proforma_id', 'billable_items')) {
            $this->forge->addColumn('billable_items', [
                'proforma_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'billing_month',
                ],
            ]);
        }

        if (! $this->db->fieldExists('invoice_id', 'billable_items')) {
            $this->forge->addColumn('billable_items', [
                'invoice_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'proforma_id',
                ],
            ]);
        }

        // Backfill entry_no for existing rows
        $this->db->query("UPDATE `billable_items` SET `entry_no` = CONCAT('BI-', LPAD(`id`, 5, '0')) WHERE `entry_no` IS NULL OR `entry_no` = ''");

        // Add unique index (idempotent)
        $hasIndex = $this->db
            ->query("SHOW INDEX FROM `billable_items` WHERE Key_name = 'billable_items_entry_no_unique'")
            ->getRowArray() !== null;

        if (! $hasIndex) {
            try {
                $this->db->query('ALTER TABLE `billable_items` ADD UNIQUE KEY `billable_items_entry_no_unique` (`entry_no`)');
            } catch (Throwable $e) {
                // ignore (e.g., duplicate data from manual edits)
            }
        }
    }

    public function down(): void
    {
        // Keep columns for forward compatibility; no-op.
    }
}

