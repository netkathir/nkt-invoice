<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedDefaultAdmin extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('admins')) {
            return;
        }

        $row = $this->db->table('admins')->select('id')->limit(1)->get()->getRowArray();
        if ($row) {
            return;
        }

        // Default admin credentials:
        // Email: admin@gmail.com
        // Password: Admin@123
        $this->db->table('admins')->insert([
            'name'       => 'Admin',
            'email'      => 'admin@gmail.com',
            'username'   => 'admin',
            'password'   => password_hash('Admin@123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down(): void
    {
        // no-op
    }
}

