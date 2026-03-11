<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProformaDefaultStatusDraft extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('proforma_invoices')) {
            return;
        }

        $this->db->query("ALTER TABLE `proforma_invoices` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'Draft'");
        $this->db->query("UPDATE `proforma_invoices` SET `status` = 'Draft' WHERE `status` IS NULL OR `status` = ''");
    }

    public function down(): void
    {
        // no-op
    }
}

