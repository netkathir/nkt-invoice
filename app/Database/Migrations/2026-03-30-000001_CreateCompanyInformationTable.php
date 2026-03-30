<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyInformationTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('company_information')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'company_name' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'logo_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'address_line1' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'address_line2' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'city' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'state' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'pincode' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'gstin_number' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
            ],
            'email_id' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ],
            'website' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('company_information', true);
    }

    public function down()
    {
        $this->forge->dropTable('company_information', true);
    }
}
