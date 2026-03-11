<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\RoleModel;
use Throwable;

class AuthController extends BaseController
{
    private function adminExists(): bool
    {
        return (new AdminModel())->countAllResults() > 0;
    }

    private function bootstrapSuperAdminIfNeeded(int $adminId): void
    {
        if ($adminId <= 0) {
            return;
        }

        try {
            $db = db_connect();

            if (
                ! $db->tableExists('admins') ||
                ! $db->tableExists('roles') ||
                ! $db->tableExists('admin_roles')
            ) {
                return;
            }

            // Ensure a Super Admin role exists.
            $roleModel = new RoleModel();
            $superRole = $roleModel->where('is_super', 1)->orderBy('id', 'ASC')->first();
            if (! $superRole) {
                $roleId = (int) $roleModel->insert([
                    'name'        => 'Super Admin',
                    'description' => 'Full access to the system',
                    'is_super'    => 1,
                ]);
                $superRole = ['id' => $roleId];
            }

            $superRoleId = (int) ($superRole['id'] ?? 0);
            if ($superRoleId <= 0) {
                return;
            }

            // If no Super Admin is assigned yet, or this is the first admin, grant Super Admin to this admin.
            $hasAnySuper = (bool) $db->table('admin_roles ar')
                ->select('ar.admin_id')
                ->join('roles r', 'r.id = ar.role_id', 'inner')
                ->where('r.is_super', 1)
                ->limit(1)
                ->get()
                ->getRowArray();

            $minAdminRow = $db->table('admins')->selectMin('id')->get()->getRowArray();
            $minAdminId = (int) ($minAdminRow['id'] ?? 0);

            if (! $hasAnySuper || ($minAdminId > 0 && $adminId === $minAdminId)) {
                $exists = (bool) $db->table('admin_roles')
                    ->select('admin_id')
                    ->where('admin_id', $adminId)
                    ->where('role_id', $superRoleId)
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                if (! $exists) {
                    $db->table('admin_roles')->insert(['admin_id' => $adminId, 'role_id' => $superRoleId]);
                }
            }
        } catch (Throwable $e) {
            // no-op (preserve existing behavior if RBAC isn't ready)
        }
    }

    private function ensureDefaultAdminExists(): void
    {
        if ($this->adminExists()) {
            return;
        }

        // Seed a single default admin (no registration UI).
        // Credentials:
        // Email: admin@gmail.com
        // Password: Admin@123
        $adminModel = new AdminModel();
        $adminId = (int) $adminModel->insert([
            'name'     => 'Admin',
            'email'    => 'admin@gmail.com',
            'username' => 'admin',
            'password' => password_hash('Admin@123', PASSWORD_DEFAULT),
        ]);

        $this->bootstrapSuperAdminIfNeeded($adminId);
    }

    public function login()
    {
        if (session()->get('admin_id')) {
            return redirect()->to(base_url('dashboard'));
        }

        $this->ensureDefaultAdminExists();

        return view('auth/login', [
            'title' => 'Admin Login',
        ]);
    }

    public function loginPost()
    {
        if (session()->get('admin_id')) {
            return redirect()->to(base_url('dashboard'));
        }

        $this->ensureDefaultAdminExists();

        $identifier = trim((string) $this->request->getPost('identifier'));
        $password = (string) $this->request->getPost('password');

        $errors = [];
        if ($identifier === '') {
            $errors['identifier'] = 'Username or Email is required.';
        }
        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if ($errors !== []) {
            return view('auth/login', [
                'title'  => 'Admin Login',
                'errors' => $errors,
                'old'    => ['identifier' => $identifier],
            ]);
        }

        $adminModel = new AdminModel();
        $admin = $adminModel
            ->groupStart()
                ->where('username', $identifier)
                ->orWhere('email', $identifier)
            ->groupEnd()
            ->first();

        if (! $admin || ! isset($admin['password']) || ! password_verify($password, (string) $admin['password'])) {
            return view('auth/login', [
                'title' => 'Admin Login',
                'error' => 'Invalid username or password',
                'old'   => ['identifier' => $identifier],
            ]);
        }

        session()->regenerate(true);
        session()->set([
            'admin_id'       => (int) $admin['id'],
            'admin_name'     => (string) $admin['name'],
            'admin_username' => (string) $admin['username'],
        ]);

        $this->bootstrapSuperAdminIfNeeded((int) $admin['id']);

        return redirect()->to(base_url('dashboard'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }
}
