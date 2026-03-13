<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Throwable;

class ExpandBillableBillingMonthLength extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('billable_items')) {
            return;
        }

        if (! $this->db->fieldExists('billing_month', 'billable_items')) {
            return;
        }

        // SQLite doesn't support MODIFY COLUMN in the same way; skip for tests.
        if (strtolower((string) $this->db->DBDriver) === 'sqlite3') {
            return;
        }

        try {
            // Old schema used CHAR(7) which truncates "Mar 2026" to "Mar 202".
            $this->forge->modifyColumn('billable_items', [
                'billing_month' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 8,
                    'null'       => true,
                ],
            ]);
        } catch (Throwable $e) {
            // ignore (shared hosting safety)
        }

        try {
            // Repair already-truncated values like "Mar 202" -> "Mar 2026" using entry_date year.
            $this->db->query(
                "UPDATE `billable_items`
                 SET `billing_month` = CONCAT(SUBSTRING(`billing_month`, 1, 4), YEAR(`entry_date`))
                 WHERE `billing_month` REGEXP '^[A-Za-z]{3} [0-9]{3}$'"
            );
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        // no-op (forward compatible)
    }
}

