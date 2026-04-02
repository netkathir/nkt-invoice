<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?= view('proforma/_form', [
    'mode' => 'view',
    'proforma' => $proforma,
    'items' => $items,
]) ?>
<?= $this->endSection() ?>
