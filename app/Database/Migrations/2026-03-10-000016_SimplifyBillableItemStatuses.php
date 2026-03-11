<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SimplifyBillableItemStatuses extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('billable_items')) {
            return;
        }

        // Convert legacy statuses to simplified ones.
        $this->db->query("UPDATE `billable_items` SET `status` = 'Pending' WHERE `status` IN ('Pending Proforma','Pending')");
        $this->db->query("UPDATE `billable_items` SET `status` = 'Billed' WHERE `status` IN ('Proforma Created','Billed')");

        // Any unknown/empty value -> Pending
        $this->db->query("UPDATE `billable_items` SET `status` = 'Pending' WHERE `status` IS NULL OR `status` = ''");

        // Ensure default is Pending
        try {
            $this->db->query("ALTER TABLE `billable_items` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'Pending'");
        } catch (\Throwable $e) {
            // ignore if hosting doesn't allow modify, app logic still sets default
        }
    }

    public function down(): void
    {
        // no-op
    }
}

