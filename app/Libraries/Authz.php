<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Session\Session;

class Authz
{
    private BaseConnection $db;
    private Session $session;

    /** @var array<int, bool> */
    private array $superCache = [];

    /** @var array<int, array<string, bool>> */
    private array $permCache = [];

    public function __construct(BaseConnection $db, Session $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    public function currentAdminId(): int
    {
        return (int) ($this->session->get('admin_id') ?? 0);
    }

    public function isSuperAdmin(?int $adminId = null): bool
    {
        $adminId = (int) ($adminId ?? $this->currentAdminId());
        if ($adminId <= 0) {
            return false;
        }

        if (array_key_exists($adminId, $this->superCache)) {
            return $this->superCache[$adminId];
        }

        try {
            $row = $this->db->table('admin_roles ar')
                ->select('r.id')
                ->join('roles r', 'r.id = ar.role_id', 'inner')
                ->where('ar.admin_id', $adminId)
                ->where('r.is_super', 1)
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (\Throwable $e) {
            // If RBAC tables are not ready yet, preserve existing behavior (full access).
            $this->superCache[$adminId] = true;
            return true;
        }

        $this->superCache[$adminId] = (bool) $row;
        return $this->superCache[$adminId];
    }

    public function can(string $permissionKey, ?int $adminId = null): bool
    {
        $permissionKey = trim($permissionKey);
        if ($permissionKey === '') {
            return false;
        }

        $adminId = (int) ($adminId ?? $this->currentAdminId());
        if ($adminId <= 0) {
            return false;
        }

        if ($this->isSuperAdmin($adminId)) {
            return true;
        }

        if (isset($this->permCache[$adminId][$permissionKey])) {
            return $this->permCache[$adminId][$permissionKey];
        }

        try {
            $row = $this->db->table('admin_roles ar')
                ->select('p.id')
                ->join('role_permissions rp', 'rp.role_id = ar.role_id', 'inner')
                ->join('permissions p', 'p.id = rp.permission_id', 'inner')
                ->where('ar.admin_id', $adminId)
                ->where('p.key', $permissionKey)
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (\Throwable $e) {
            $allowed = true;
            $this->permCache[$adminId][$permissionKey] = $allowed;
            return $allowed;
        }

        $allowed = (bool) $row;
        $this->permCache[$adminId][$permissionKey] = $allowed;
        return $allowed;
    }

    public function require(string $permissionKey, ?string $message = null)
    {
        if ($this->can($permissionKey)) {
            return null;
        }

        $this->session->setFlashdata('error', $message ?: 'You do not have permission to access this page.');
        return redirect()->to(base_url('dashboard'));
    }
}
