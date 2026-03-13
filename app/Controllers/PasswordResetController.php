<?php

namespace App\Controllers;

use App\Libraries\PasswordResetService;
use Config\Services;

class PasswordResetController extends BaseController
{
    private function service(): PasswordResetService
    {
        return new PasswordResetService(db_connect());
    }

    public function request()
    {
        if (session()->get('admin_id')) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('auth/forgot_password', [
            'title' => 'Forgot Password',
        ]);
    }

    public function requestPost()
    {
        if (session()->get('admin_id')) {
            return redirect()->to(base_url('dashboard'));
        }

        $email = trim((string) $this->request->getPost('email'));

        $result = $this->service()->requestReset(
            $email,
            (string) $this->request->getIPAddress(),
            $this->request->getUserAgent()?->getAgentString()
        );

        if (! ($result['success'] ?? false)) {
            return view('auth/forgot_password', [
                'title' => 'Forgot Password',
                'error' => (string) ($result['error'] ?? 'Unable to process your request.'),
                'old'   => ['email' => $email],
            ]);
        }

        $token = (string) ($result['token'] ?? '');
        $expiresAt = (string) ($result['expiresAt'] ?? '');
        $admin = (array) ($result['admin'] ?? []);

        $to = (string) ($admin['email'] ?? '');
        if ($to === '' || $token === '') {
            return view('auth/forgot_password', [
                'title' => 'Forgot Password',
                'error' => 'Unable to process your request. Please try again.',
                'old'   => ['email' => $email],
            ]);
        }

        $resetLink = base_url('reset-password/' . $token);
        $body = view('emails/reset_password', [
            'resetLink' => $resetLink,
            'expiresAt' => $expiresAt,
        ]);

        $emailSvc = Services::email();
        $emailCfg = config('Email');

        $fromEmail = trim((string) ($emailCfg->fromEmail ?? ''));
        $fromName = trim((string) ($emailCfg->fromName ?? ''));
        if ($fromEmail === '') {
            $host = (string) (parse_url(base_url(), PHP_URL_HOST) ?: '');
            $host = trim($host);
            if ($host === '' || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
                $host = 'example.com';
            }
            $fromEmail = 'no-reply@' . $host;
        }
        if ($fromName === '') {
            $fromName = 'Billing Management System';
        }

        $emailSvc->setMailType('html');
        $emailSvc->setFrom($fromEmail, $fromName);
        $emailSvc->setTo($to);
        $emailSvc->setSubject('Reset your password');
        $emailSvc->setMessage($body);

        if (! $emailSvc->send()) {
            log_message('error', 'Password reset email failed: {debug}', [
                'debug' => $emailSvc->printDebugger(['headers', 'subject']),
            ]);

            // Safety: invalidate token if email failed.
            db_connect()->table('password_reset_tokens')
                ->where('token_hash', hash('sha256', $token))
                ->set(['used_at' => date('Y-m-d H:i:s')])
                ->update();

            return view('auth/forgot_password', [
                'title' => 'Forgot Password',
                'error' => 'Unable to send reset email. Please contact the administrator.',
                'old'   => ['email' => $email],
            ]);
        }

        session()->setFlashdata('success', 'Reset link sent. Please check your email.');
        return redirect()->to(base_url('admin/login'));
    }

    public function reset(string $token)
    {
        if (session()->get('admin_id')) {
            return redirect()->to(base_url('dashboard'));
        }

        $valid = $this->service()->validateToken($token);
        if (! ($valid['success'] ?? false)) {
            return view('auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => (string) ($valid['error'] ?? 'Invalid or expired reset link.'),
            ]);
        }

        return view('auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    public function resetPost(string $token)
    {
        if (session()->get('admin_id')) {
            return redirect()->to(base_url('dashboard'));
        }

        $password = (string) $this->request->getPost('password');
        $confirm = (string) $this->request->getPost('confirm_password');

        $errors = [];
        if ($password === '') {
            $errors['password'] = 'New Password is required.';
        }
        if ($confirm === '') {
            $errors['confirm_password'] = 'Confirm Password is required.';
        }
        if ($password !== '' && $confirm !== '' && $password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        if ($errors === []) {
            if ($msg = PasswordResetService::validatePasswordStrength($password)) {
                $errors['password'] = $msg;
            }
        }

        if ($errors !== []) {
            return view('auth/reset_password', [
                'title'  => 'Reset Password',
                'token'  => $token,
                'errors' => $errors,
            ]);
        }

        $result = $this->service()->resetPassword($token, $password);
        if (! ($result['success'] ?? false)) {
            return view('auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => (string) ($result['error'] ?? 'Unable to reset password.'),
            ]);
        }

        session()->setFlashdata('success', 'Password reset successful. You can now login with your new password.');
        return redirect()->to(base_url('admin/login'));
    }
}

