<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Billing Management System') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('favicon_netk.png?v=' . ((@filemtime(FCPATH . 'favicon_netk.png')) ?: 0)) ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('favicon_netk.png?v=' . ((@filemtime(FCPATH . 'favicon_netk.png')) ?: 0)) ?>">
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . ((@filemtime(FCPATH . 'favicon.ico')) ?: 0)) ?>">
    <link rel="apple-touch-icon" href="<?= base_url('favicon_netk.png?v=' . ((@filemtime(FCPATH . 'favicon_netk.png')) ?: 0)) ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . ((@filemtime(FCPATH . 'assets/css/app.css')) ?: 0)) ?>">
</head>
<body>
<div class="app-canvas p-3 p-md-4">
    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
