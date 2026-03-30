<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExportTaxReferenceToCompanyInformation extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('company_information')) {
            return;
        }

        if (! $this->db->fieldExists('export_tax_reference', 'company_information')) {
            $this->forge->addColumn('company_information', [
                'export_tax_reference' => [
                    'type' => 'VARCHAR',
                    'constraint' => 191,
                    'null' => true,
                    'after' => 'gstin_number',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('company_information')) {
            return;
        }

        if ($this->db->fieldExists('export_tax_reference', 'company_information')) {
            $this->forge->dropColumn('company_information', 'export_tax_reference');
        }
    }
}
