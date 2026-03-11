<?php

namespace App\Controllers;

use App\Models\AdminModel;
use Throwable;

class AuthController extends BaseController
{
    private function adminExists(): bool
    {
        return (new AdminModel())->countAllResults() > 0;
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
        $adminModel->insert([
            'name'     => 'Admin',
            'email'    => 'admin@gmail.com',
            'username' => 'admin',
            'password' => password_hash('Admin@123', PASSWORD_DEFAULT),
        ]);
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

        return redirect()->to(base_url('dashboard'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }
}
