<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Create Invoice</h5>
    <a class="btn btn-light" href="<?= base_url('proforma') ?>">Back</a>
</div>

<div class="card bms-invoice-card">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="flex-shrink-0">
                <img src="<?= base_url('favicon_netk.png') ?>" alt="Company Logo" class="bms-invoice-logo">
            </div>
            <div class="flex-grow-1">
                <div class="h5 mb-1">Invoice</div>
                <div class="text-muted small">Invoice</div>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="row g-3 align-items-center">
	                    <div class="col-12 col-md-4">
	                        <label class="form-label mb-0 text-md-end fw-semibold">Invoice No <span class="text-danger">*</span></label>
	                    </div>
	                    <div class="col-12 col-md-8">
	                        <input type="text" class="form-control" id="pf_invoice_no" value="" placeholder="Enter invoice no" required>
	                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Customer Name <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-12 col-md-8">
                        <select class="form-select" id="pf_client_id" required>
                            <option value="">Select</option>
                            <?php foreach ($clients as $c): ?>
                                <?php
                                    $customer = $c['contact_person'] ?: ($c['name'] ?: ($c['email'] ?: ($c['phone'] ?: ('Client #' . $c['id']))));
                                    $company = $c['name'] ?: $customer;
                                    $gst = $c['gst_no'] ?? ($c['gst'] ?? '');
                                    $addr1 = $c['address'] ?? '';
                                    $addr2 = $c['billing_address'] ?? '';
                                    $city = $c['city'] ?? '';
                                    $state = $c['state'] ?? '';
                                    $pincode = $c['postal_code'] ?? ($c['pincode'] ?? '');
                                ?>
                                <option
                                    value="<?= esc((string) $c['id']) ?>"
                                    data-company="<?= esc((string) $company) ?>"
                                    data-customer="<?= esc((string) $customer) ?>"
                                    data-gst="<?= esc((string) $gst) ?>"
                                    data-addr1="<?= esc((string) $addr1) ?>"
                                    data-addr2="<?= esc((string) $addr2) ?>"
                                    data-city="<?= esc((string) $city) ?>"
                                    data-state="<?= esc((string) $state) ?>"
                                    data-pincode="<?= esc((string) $pincode) ?>"
                                ><?= esc($customer) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="pf_from" value="">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">GST NO</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_gst" value="">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Address Line1</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_addr1" value="">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">State</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_state" value="">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Currency <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-12 col-md-8">
                        <select class="form-select" id="pf_currency" required>
                            <option value="">Select</option>
                            <option value="INR" selected>INR</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-12 col-md-8">
                        <select class="form-select" id="pf_invoice_type" required>
                            <option value="GST Invoice" selected>GST Invoice</option>
                            <option value="Export Invoice">Export Invoice</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Company Name</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_company" value="">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Date Of Issue <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="input-group bms-date-wrap">
                            <input type="text" class="form-control" id="pf_date" placeholder="DD/MM/YYYY" autocomplete="off" required>
                            <button class="btn btn-outline-secondary bms-date-btn" type="button" aria-label="Pick date">
                                <span aria-hidden="true">📅</span>
                            </button>
                            <input type="date" class="bms-native-date" id="pf_date_native" value="<?= esc(date('Y-m-d')) ?>" tabindex="-1" aria-hidden="true">
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Address Line2</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_addr2" value="">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">City</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_city" value="">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">PinCode</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_pincode" value="">
                    </div>
                </div>
            </div>
        </div>

	        <div class="mt-4 bms-items-box">
	            <div class="fw-semibold mb-2">Items <span class="text-danger">*</span></div>
	            <div class="table-responsive">
	                <table id="pfItemsTable" class="table table-bordered align-middle nowrap w-100 mb-0">
	                    <thead class="table-light">
	                    <tr>
	                        <th style="width:200px;">Item</th>
	                        <th>Item Description</th>
	                        <th class="text-end" style="width:120px;">Quantity</th>
	                        <th style="width:110px;">UOM</th>
	                        <th class="text-end" style="width:140px;">Price</th>
	                        <th class="text-end" style="width:140px;">Value</th>
	                        <th class="text-center" style="width:90px;">Actions</th>
	                    </tr>
	                    </thead>
	                    <tbody></tbody>
	                </table>
	            </div>
	        </div>

        <div class="row g-3 justify-content-end mt-4">
            <div class="col-12 col-md-6 col-lg-5 col-xl-4">
                <div class="row g-2 align-items-center">
                    <div class="col-4 text-end fw-semibold">Total</div>
                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="text" class="form-control" id="pf_total_input" value="0.00" readonly>
                        </div>
                        <span id="pf_total" class="d-none">0.00</span>
                    </div>
                </div>

                <div id="pf_gst_box" class="mt-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-4 text-end fw-semibold">GST %</div>
                        <div class="col-8">
                            <input type="number" min="0" step="0.01" class="form-control" id="pf_gst_percent" value="0">
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-1">
                        <div class="col-4"></div>
                        <div class="col-8 d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pf_gst_mode" id="pf_mode_cgst" value="CGST_SGST" checked>
                                <label class="form-check-label" for="pf_mode_cgst">CGST &amp; SGST</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pf_gst_mode" id="pf_mode_igst" value="IGST">
                                <label class="form-check-label" for="pf_mode_igst">IGST</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-4 text-end fw-semibold">CGST</div>
                        <div class="col-8">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" class="form-control" id="pf_cgst_amt" value="0.00" readonly></div>
                                <div class="col-6"><input type="text" class="form-control" id="pf_cgst_val" value="0.00" readonly></div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-4 text-end fw-semibold">SGST</div>
                        <div class="col-8">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" class="form-control" id="pf_sgst_amt" value="0.00" readonly></div>
                                <div class="col-6"><input type="text" class="form-control" id="pf_sgst_val" value="0.00" readonly></div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-4 text-end fw-semibold">IGST</div>
                        <div class="col-8">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" class="form-control" id="pf_igst_amt" value="0.00" readonly></div>
                                <div class="col-6"><input type="text" class="form-control" id="pf_igst_val" value="0.00" readonly></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-4 text-end fw-semibold">Total GST</div>
                        <div class="col-8">
                            <input type="text" class="form-control" id="pf_total_gst" value="0.00" readonly>
                        </div>
                    </div>
                </div>

                <div class="row g-2 align-items-center mt-2">
                    <div class="col-4 text-end fw-semibold">Net Amount</div>
                    <div class="col-8">
                        <input type="text" class="form-control" id="pf_net_amount" value="0.00" readonly>
                    </div>
                </div>

                <div class="row g-2 align-items-center mt-2">
                    <div class="col-4 text-end fw-semibold">Due Date <span class="text-danger">*</span></div>
                    <div class="col-8">
                        <div class="input-group bms-date-wrap">
                            <input type="text" class="form-control" id="pf_due" placeholder="DD/MM/YYYY" autocomplete="off" required>
                            <button class="btn btn-outline-secondary bms-date-btn" type="button" aria-label="Pick date">
                                <span aria-hidden="true">📅</span>
                            </button>
                            <input type="date" class="bms-native-date" id="pf_due_native" tabindex="-1" aria-hidden="true">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-primary" id="btnSaveProforma" type="button" disabled>Submit</button>
            </div>
        </div>
    </div>
</div>

<?= view('billable_items/view_modal') ?>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initProformaCreate && window.BMS.initProformaCreate();
    });
</script>
<?= $this->endSection() ?>
