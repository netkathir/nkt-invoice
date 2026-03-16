<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Throwable;

class CascadeDeleteClients extends Migration
{
    /**
     * @return list<string>
     */
    private function fkNames(string $table, string $column, string $refTable): array
    {
        try {
            $sql = 'SELECT CONSTRAINT_NAME AS name
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = ?
                      AND COLUMN_NAME = ?
                      AND REFERENCED_TABLE_NAME = ?';

            $rows = $this->db->query($sql, [$table, $column, $refTable])->getResultArray();
            $names = [];
            foreach ($rows as $r) {
                $n = trim((string) ($r['name'] ?? ''));
                if ($n !== '') {
                    $names[] = $n;
                }
            }
            return array_values(array_unique($names));
        } catch (Throwable) {
            return [];
        }
    }

    private function dropFkIfExists(string $table, string $fkName): void
    {
        $fkName = trim($fkName);
        if ($fkName === '') return;
        try {
            $this->db->query('ALTER TABLE `' . $table . '` DROP FOREIGN KEY `' . $fkName . '`');
        } catch (Throwable) {
            // ignore (shared hosting / already dropped / different name)
        }
    }

    private function addFk(string $table, string $fkName, string $column, string $refTable, string $refColumn, string $onDelete): void
    {
        $this->db->query(
            'ALTER TABLE `' . $table . '` ADD CONSTRAINT `' . $fkName . '` FOREIGN KEY (`' . $column . '`) ' .
            'REFERENCES `' . $refTable . '`(`' . $refColumn . '`) ON UPDATE CASCADE ON DELETE ' . $onDelete
        );
    }

    public function up(): void
    {
        // Only applies if tables exist.
        if (! $this->db->tableExists('clients')) {
            return;
        }

        // IMPORTANT: This makes deleting a client delete all related data (invoices, items, payments).
        // Requested behavior: delete parent -> delete child.
        $changes = [
            // table, column, refTable, refColumn, fkName (preferred), onDelete
            ['proforma_invoices', 'client_id', 'clients', 'id', 'fk_proforma_invoices_client', 'CASCADE'],
            ['invoices', 'client_id', 'clients', 'id', 'fk_invoices_client', 'CASCADE'],
            ['billable_items', 'client_id', 'clients', 'id', 'fk_billable_items_client', 'CASCADE'],
            ['proforma_payments', 'client_id', 'clients', 'id', 'fk_proforma_payments_client', 'CASCADE'],
            ['payments', 'client_id', 'clients', 'id', 'fk_payments_client', 'CASCADE'],

            // Ensure invoice delete cascades to payments.
            ['payments', 'invoice_id', 'invoices', 'id', 'fk_payments_invoice', 'CASCADE'],

            // Ensure billable item delete cascades to proforma_items.
            ['proforma_items', 'billable_item_id', 'billable_items', 'id', 'fk_proforma_items_billable', 'CASCADE'],
        ];

        foreach ($changes as $c) {
            [$table, $column, $refTable, $refColumn, $preferredName, $onDelete] = $c;

            if (! $this->db->tableExists($table)) {
                continue;
            }

            // Drop any existing FK(s) matching this relationship (names differ between environments).
            $names = $this->fkNames($table, $column, $refTable);
            foreach ($names as $n) {
                $this->dropFkIfExists($table, $n);
            }

            // Add with preferred name (ignore if already exists).
            try {
                $this->addFk($table, $preferredName, $column, $refTable, $refColumn, $onDelete);
            } catch (Throwable) {
                // ignore if cannot add (already exists / permissions)
            }
        }
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}

