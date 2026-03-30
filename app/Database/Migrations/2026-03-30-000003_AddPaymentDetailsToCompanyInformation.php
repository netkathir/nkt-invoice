<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentDetailsToCompanyInformation extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('company_information')) {
            return;
        }

        $columns = [];

        if (! $this->db->fieldExists('current_account_details', 'company_information')) {
            $columns['current_account_details'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'phone_number',
            ];
        }

        if (! $this->db->fieldExists('paypal_account', 'company_information')) {
            $columns['paypal_account'] = [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
                'after' => 'current_account_details',
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn('company_information', $columns);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('company_information')) {
            return;
        }

        if ($this->db->fieldExists('paypal_account', 'company_information')) {
            $this->forge->dropColumn('company_information', 'paypal_account');
        }

        if ($this->db->fieldExists('current_account_details', 'company_information')) {
            $this->forge->dropColumn('company_information', 'current_account_details');
        }
    }
}
