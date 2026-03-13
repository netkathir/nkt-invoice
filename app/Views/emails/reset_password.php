<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your password</title>
</head>
<body>
<p>Hello,</p>

<p>We received a request to reset your password. Click the link below to set a new password:</p>

<p>
    <a href="<?= esc((string) ($resetLink ?? '')) ?>">Reset Password</a><br>
    <span style="font-size: 12px; color: #666;">This link expires in 30 minutes (expires at <?= esc((string) ($expiresAt ?? '')) ?>).</span>
</p>

<p>If you did not request this, you can ignore this email.</p>

<p>Thanks,<br>Billing Management System</p>
</body>
</html>

