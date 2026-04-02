<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?= view('proforma/_form', [
    'mode' => 'create',
    'clients' => $clients,
    'nextInvoiceNo' => $nextInvoiceNo,
]) ?>

<?= view('billable_items/view_modal') ?>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initProformaCreate && window.BMS.initProformaCreate();
    });
</script>
<?= $this->endSection() ?>
