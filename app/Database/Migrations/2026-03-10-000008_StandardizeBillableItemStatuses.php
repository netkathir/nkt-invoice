<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class StandardizeBillableItemStatuses extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('billable_items')) {
            return;
        }

        // Normalize previous statuses to latest Phase-1 set.
        $this->db->query("UPDATE `billable_items` SET `status` = 'Pending' WHERE `status` IN ('Pending Proforma')");
        $this->db->query("UPDATE `billable_items` SET `status` = 'Billed' WHERE `status` IN ('Proforma Created')");

        // Ensure default aligns (best effort; may be ignored on some MySQL versions/settings)
        $this->db->query("ALTER TABLE `billable_items` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        // no-op
    }
}

