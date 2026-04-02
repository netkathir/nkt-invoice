<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?= view('proforma/_form', [
    'mode' => 'edit',
    'clients' => $clients,
    'proforma' => $proforma,
    'items' => $items,
]) ?>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initProformaEdit && window.BMS.initProformaEdit();
    });
</script>
<?= $this->endSection() ?>
