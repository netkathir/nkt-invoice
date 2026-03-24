<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Invoices</h5>
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <button class="btn btn-sm btn-outline-success" id="pfBtnExport" type="button">Export CSV</button>
        <button class="btn btn-sm btn-outline-primary" id="pfBtnPrint" type="button">Print</button>
        <a class="btn btn-sm btn-primary" href="<?= base_url('proforma/create') ?>">Add</a>
        <button class="btn btn-sm btn-outline-secondary" id="pfBtnEdit" type="button" disabled>Edit</button>
        <button class="btn btn-sm btn-outline-danger" id="pfBtnDelete" type="button" disabled>Delete</button>
        <button class="btn btn-sm btn-warning text-dark" id="pfBtnPdf" type="button" disabled>Generate PDF</button>
        <button class="btn btn-sm btn-outline-primary" id="pfBtnView" type="button" disabled>View</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtProformas" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th class="text-center" style="width: 44px;"></th>
                <th style="width: 70px;">S.No</th>
                <th>Invoice No</th>
                <th>Invoice Type</th>
                <th>Date of Issue</th>
                <th>Due Date</th>
                <th>Customer Name</th>
                <th>Company Name</th>
                <th>Net Amount</th>
            </tr>
            <tr>
                <th class="text-center sorting_disabled">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="pfBtnClear">Clear</button>
                </th>
                <th class="sorting_disabled"></th>
                <th class="sorting_disabled"><input class="form-control form-control-sm pf-col-filter" data-col="2" placeholder="Search"></th>
                <th class="sorting_disabled"><input class="form-control form-control-sm pf-col-filter" data-col="3" placeholder="Search"></th>
                <th class="sorting_disabled">
                    <div class="input-group input-group-sm bms-date-wrap">
                        <input type="text" class="form-control pf-col-filter pf-col-filter-dmy" data-col="4" autocomplete="off">
                        <button class="btn btn-outline-secondary bms-date-btn" type="button" aria-label="Pick date">
                            <span aria-hidden="true">📅</span>
                        </button>
                        <input type="date" class="bms-native-date" id="pf_issue_native" tabindex="-1" aria-hidden="true">
                    </div>
                </th>
                <th class="sorting_disabled">
                    <div class="input-group input-group-sm bms-date-wrap">
                        <input type="text" class="form-control pf-col-filter pf-col-filter-dmy" data-col="5" autocomplete="off">
                        <button class="btn btn-outline-secondary bms-date-btn" type="button" aria-label="Pick date">
                            <span aria-hidden="true">📅</span>
                        </button>
                        <input type="date" class="bms-native-date" id="pf_due_native" tabindex="-1" aria-hidden="true">
                    </div>
                </th>
                <th class="sorting_disabled"><input class="form-control form-control-sm pf-col-filter" data-col="6" placeholder="Search"></th>
                <th class="sorting_disabled"><input class="form-control form-control-sm pf-col-filter" data-col="7" placeholder="Search"></th>
                <th class="sorting_disabled"><input class="form-control form-control-sm pf-col-filter" data-col="8" placeholder="Search"></th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initProformas && window.BMS.initProformas();
    });
</script>
<?= $this->endSection() ?>
