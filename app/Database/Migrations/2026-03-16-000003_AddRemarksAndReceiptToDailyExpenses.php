<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRemarksAndReceiptToDailyExpenses extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('daily_expenses')) {
            return;
        }

        $columns = [];

        if (! $this->db->fieldExists('remarks', 'daily_expenses')) {
            $columns['remarks'] = [
                'type' => 'TEXT',
                'null' => true,
                'after'=> 'description',
            ];
        }

        if (! $this->db->fieldExists('receipt_path', 'daily_expenses')) {
            $columns['receipt_path'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'paid_to',
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn('daily_expenses', $columns);
        }
    }

    public function down(): void
    {
        // no-op (shared hosting safety)
    }
}

