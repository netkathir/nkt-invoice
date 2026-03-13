<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

class PasswordResetService
{
    public const TOKEN_TTL_SECONDS = 1800; // 30 minutes
    public const RATE_WINDOW_SECONDS = 3600; // 1 hour
    public const RATE_LIMIT_PER_EMAIL = 10;
    public const RATE_LIMIT_PER_IP = 10;

    private BaseConnection $db;

    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
    }

    public static function validatePasswordStrength(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }
        if (! preg_match('/[a-z]/', $password)) {
            return 'Password must include at least one lowercase letter.';
        }
        if (! preg_match('/[A-Z]/', $password)) {
            return 'Password must include at least one uppercase letter.';
        }
        if (! preg_match('/[0-9]/', $password)) {
            return 'Password must include at least one number.';
        }
        if (! preg_match('/[^a-zA-Z0-9]/', $password)) {
            return 'Password must include at least one special character.';
        }

        return null;
    }

    public function requestReset(string $email, string $requestIp, ?string $userAgent = null): array
    {
        $email = trim(strtolower($email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'A valid email address is required.'];
        }

        $admin = $this->db->table('admins')
            ->select('id,email,status')
            ->where('email', $email)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! $admin) {
            return ['success' => false, 'error' => 'Email address not found.'];
        }

        $adminId = (int) ($admin['id'] ?? 0);
        if ($adminId <= 0) {
            return ['success' => false, 'error' => 'Email address not found.'];
        }

        if ($this->isSuperAdmin($adminId)) {
            return ['success' => false, 'error' => 'Password reset is disabled for Super Admin accounts.'];
        }

        if (! $this->db->tableExists('password_reset_tokens')) {
            return ['success' => false, 'error' => 'Password reset is not available yet. Please contact the administrator.'];
        }

        if ($msg = $this->rateLimitMessage($adminId, $requestIp)) {
            return ['success' => false, 'error' => $msg];
        }

        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_TTL_SECONDS);

        try {
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);

            $this->db->table('password_reset_tokens')->insert([
                'admin_id'   => $adminId,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'used_at'    => null,
                'request_ip' => trim($requestIp) !== '' ? $requestIp : null,
                'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
                'created_at' => $now,
            ]);
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Unable to generate a reset link. Please try again later.'];
        }

        return [
            'success'   => true,
            'admin'     => $admin,
            'token'     => $rawToken,
            'expiresAt' => $expiresAt,
        ];
    }

    public function validateToken(string $rawToken): array
    {
        $rawToken = trim($rawToken);
        if ($rawToken === '') {
            return ['success' => false, 'error' => 'Invalid or expired reset link.'];
        }

        if (! $this->db->tableExists('password_reset_tokens')) {
            return ['success' => false, 'error' => 'Invalid or expired reset link.'];
        }

        $tokenHash = hash('sha256', $rawToken);
        $now = date('Y-m-d H:i:s');

        $row = $this->db->table('password_reset_tokens pr')
            ->select('pr.id, pr.admin_id, pr.expires_at, pr.used_at')
            ->where('pr.token_hash', $tokenHash)
            ->where('pr.used_at', null)
            ->where('pr.expires_at >=', $now)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! $row) {
            return ['success' => false, 'error' => 'Invalid or expired reset link.'];
        }

        $adminId = (int) ($row['admin_id'] ?? 0);
        if ($adminId <= 0 || $this->isSuperAdmin($adminId)) {
            return ['success' => false, 'error' => 'Password reset is disabled for Super Admin accounts.'];
        }

        return ['success' => true, 'row' => $row];
    }

    public function resetPassword(string $rawToken, string $newPassword): array
    {
        $valid = $this->validateToken($rawToken);
        if (! ($valid['success'] ?? false)) {
            return $valid;
        }

        if ($msg = self::validatePasswordStrength($newPassword)) {
            return ['success' => false, 'error' => $msg];
        }

        $row = (array) ($valid['row'] ?? []);
        $adminId = (int) ($row['admin_id'] ?? 0);
        if ($adminId <= 0) {
            return ['success' => false, 'error' => 'Invalid or expired reset link.'];
        }

        $now = date('Y-m-d H:i:s');
        $tokenHash = hash('sha256', $rawToken);

        $this->db->transStart();

        $check = $this->db->table('password_reset_tokens')
            ->select('id, admin_id')
            ->where('token_hash', $tokenHash)
            ->where('used_at', null)
            ->where('expires_at >=', $now)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! $check) {
            $this->db->transComplete();
            return ['success' => false, 'error' => 'Invalid or expired reset link.'];
        }

        $adminRow = $this->db->table('admins')
            ->select('password')
            ->where('id', $adminId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! $adminRow || ! isset($adminRow['password'])) {
            $this->db->transComplete();
            return ['success' => false, 'error' => 'Unable to reset password. Please try again.'];
        }

        if (password_verify($newPassword, (string) $adminRow['password'])) {
            $this->db->transComplete();
            return ['success' => false, 'error' => 'New password must be different from your current password.'];
        }

        // Invalidate all outstanding tokens for this admin.
        $this->db->table('password_reset_tokens')
            ->where('admin_id', $adminId)
            ->where('used_at', null)
            ->set(['used_at' => $now])
            ->update();

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        if ($hash === false) {
            $this->db->transComplete();
            return ['success' => false, 'error' => 'Unable to reset password. Please try again.'];
        }

        $this->db->table('admins')
            ->where('id', $adminId)
            ->set(['password' => $hash, 'updated_at' => $now])
            ->update();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['success' => false, 'error' => 'Unable to reset password. Please try again.'];
        }

        return ['success' => true];
    }

    private function isSuperAdmin(int $adminId): bool
    {
        if ($adminId <= 0) {
            return true;
        }

        if (! $this->db->tableExists('roles') || ! $this->db->tableExists('admin_roles')) {
            // Be safe if RBAC isn't ready.
            return true;
        }

        $row = $this->db->table('admin_roles ar')
            ->select('ar.admin_id')
            ->join('roles r', 'r.id = ar.role_id', 'inner')
            ->where('ar.admin_id', $adminId)
            ->where('r.is_super', 1)
            ->limit(1)
            ->get()
            ->getRowArray();

        return (bool) $row;
    }

    private function rateLimitMessage(int $adminId, string $requestIp): ?string
    {
        $cutoff = date('Y-m-d H:i:s', time() - self::RATE_WINDOW_SECONDS);

        $byEmail = (int) $this->db->table('password_reset_tokens')
            ->where('admin_id', $adminId)
            ->where('created_at >=', $cutoff)
            ->countAllResults();

        if ($byEmail >= self::RATE_LIMIT_PER_EMAIL) {
            return 'Too many password reset requests for this email. Please try again later.';
        }

        $ip = trim($requestIp);
        if ($ip !== '') {
            $byIp = (int) $this->db->table('password_reset_tokens')
                ->where('request_ip', $ip)
                ->where('created_at >=', $cutoff)
                ->countAllResults();

            if ($byIp >= self::RATE_LIMIT_PER_IP) {
                return 'Too many password reset requests. Please try again later.';
            }
        }

        return null;
    }
}
