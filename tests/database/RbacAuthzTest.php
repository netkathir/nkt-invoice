<?php

use App\Libraries\Authz;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;

/**
 * @internal
 */
final class RbacAuthzTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $migrate = true;

    protected function setUp(): void
    {
        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('sqlite3 extension is not enabled; enable it or configure MySQL tests.');
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (extension_loaded('sqlite3')) {
            parent::tearDown();
        }
    }

    public function testSuperAdminCanAnything(): void
    {
        $session = Services::session();
        $session->set(['admin_id' => 1]);

        $authz = new Authz(db_connect(), $session);
        $this->assertTrue($authz->can('roles.view'));
        $this->assertTrue($authz->can('some.random.permission'));
    }

    public function testNonSuperAdminRequiresPermission(): void
    {
        $db = db_connect();

        // Create a non-super role
        $db->table('roles')->insert([
            'name'        => 'Staff',
            'description' => null,
            'is_super'    => 0,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $staffRoleId = (int) $db->insertID();

        // Create an admin and assign Staff role
        $db->table('admins')->insert([
            'name'       => 'Staff User',
            'email'      => 'staff@example.com',
            'username'   => 'staff',
            'password'   => password_hash('Test@1234', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $adminId = (int) $db->insertID();
        $db->table('admin_roles')->insert(['admin_id' => $adminId, 'role_id' => $staffRoleId]);

        // Create a permission but do NOT assign it yet
        $db->table('permissions')->insert([
            'key'        => 'clients.view',
            'label'      => 'View clients',
            'module'     => 'Masters',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $permId = (int) $db->insertID();

        $session = Services::session();
        $session->set(['admin_id' => $adminId]);
        $authz = new Authz($db, $session);

        $this->assertFalse($authz->can('clients.view'));

        // Assign permission to role -> should allow
        $db->table('role_permissions')->insert(['role_id' => $staffRoleId, 'permission_id' => $permId]);
        $this->assertTrue($authz->can('clients.view'));
    }
}
