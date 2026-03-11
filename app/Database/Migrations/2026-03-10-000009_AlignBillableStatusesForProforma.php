<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlignBillableStatusesForProforma extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('billable_items')) {
            return;
        }

        // Convert Phase-1 "Pending/Billed" back to Proforma workflow statuses.
        $this->db->query("UPDATE `billable_items` SET `status` = 'Pending Proforma' WHERE `status` = 'Pending'");
        $this->db->query("UPDATE `billable_items` SET `status` = 'Proforma Created' WHERE `status` = 'Billed'");

        $this->db->query("ALTER TABLE `billable_items` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'Pending Proforma'");
    }

    public function down(): void
    {
        // no-op
    }
}

