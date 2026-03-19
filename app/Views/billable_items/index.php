<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Billable Items</h5>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" id="btnGenerateProforma" type="button" disabled>Generate Proforma</button>
        <button class="btn btn-primary" id="btnAddBillable" type="button">Add Billable Item</button>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Client</label>
                <select class="form-select" id="filterClient">
                    <option value="">All Clients</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= esc((string) $c['id']) ?>"><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="filterStatus">
                    <option value="">All</option>
                    <option value="Pending">Pending</option>
                    <option value="Billed">Billed</option>
                </select>
            </div>
            <div class="col-12 col-md-5 text-md-end">
                <div class="small text-muted">
                    Tip: Click a cell under Description/Quantity/Unit Price to edit (pending items only).
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtBillableItems" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th><input class="form-check-input" type="checkbox" id="chkAll"></th>
                <th>Entry No</th>
                <th>Date</th>
                <th>Client</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Proforma No</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="billableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Billable Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="billableForm" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Entry Date</label>
                            <input type="date" class="form-control" name="entry_date" id="bi_entry_date" value="<?= esc(date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Client</label>
                            <select class="form-select" name="client_id" id="bi_client_id" required>
                                <option value="">Select Client</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= esc((string) $c['id']) ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="bi_description" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <select class="form-select" name="currency" id="bi_currency">
                                <optgroup label="Common">
                                    <option value="INR">INR – Indian Rupee</option>
                                    <option value="USD">USD – US Dollar</option>
                                    <option value="EUR">EUR – Euro</option>
                                    <option value="GBP">GBP – British Pound</option>
                                    <option value="AED">AED – UAE Dirham</option>
                                    <option value="SGD">SGD – Singapore Dollar</option>
                                    <option value="AUD">AUD – Australian Dollar</option>
                                    <option value="CAD">CAD – Canadian Dollar</option>
                                    <option value="JPY">JPY – Japanese Yen</option>
                                </optgroup>
                                <optgroup label="All Currencies">
                                    <option value="AFN">AFN – Afghan Afghani</option>
                                    <option value="ALL">ALL – Albanian Lek</option>
                                    <option value="DZD">DZD – Algerian Dinar</option>
                                    <option value="AOA">AOA – Angolan Kwanza</option>
                                    <option value="ARS">ARS – Argentine Peso</option>
                                    <option value="AMD">AMD – Armenian Dram</option>
                                    <option value="AWG">AWG – Aruban Florin</option>
                                    <option value="AZN">AZN – Azerbaijani Manat</option>
                                    <option value="BSD">BSD – Bahamian Dollar</option>
                                    <option value="BHD">BHD – Bahraini Dinar</option>
                                    <option value="BDT">BDT – Bangladeshi Taka</option>
                                    <option value="BBD">BBD – Barbadian Dollar</option>
                                    <option value="BYN">BYN – Belarusian Ruble</option>
                                    <option value="BZD">BZD – Belize Dollar</option>
                                    <option value="XOF">XOF – West African CFA Franc</option>
                                    <option value="BTN">BTN – Bhutanese Ngultrum</option>
                                    <option value="BOB">BOB – Bolivian Boliviano</option>
                                    <option value="BAM">BAM – Bosnia-Herzegovina Mark</option>
                                    <option value="BWP">BWP – Botswanan Pula</option>
                                    <option value="BRL">BRL – Brazilian Real</option>
                                    <option value="BND">BND – Brunei Dollar</option>
                                    <option value="BGN">BGN – Bulgarian Lev</option>
                                    <option value="BIF">BIF – Burundian Franc</option>
                                    <option value="CVE">CVE – Cape Verdean Escudo</option>
                                    <option value="KHR">KHR – Cambodian Riel</option>
                                    <option value="XAF">XAF – Central African CFA Franc</option>
                                    <option value="CLP">CLP – Chilean Peso</option>
                                    <option value="CNY">CNY – Chinese Yuan</option>
                                    <option value="COP">COP – Colombian Peso</option>
                                    <option value="KMF">KMF – Comorian Franc</option>
                                    <option value="CDF">CDF – Congolese Franc</option>
                                    <option value="CRC">CRC – Costa Rican Colón</option>
                                    <option value="HRK">HRK – Croatian Kuna</option>
                                    <option value="CUP">CUP – Cuban Peso</option>
                                    <option value="CZK">CZK – Czech Koruna</option>
                                    <option value="DKK">DKK – Danish Krone</option>
                                    <option value="DJF">DJF – Djiboutian Franc</option>
                                    <option value="DOP">DOP – Dominican Peso</option>
                                    <option value="EGP">EGP – Egyptian Pound</option>
                                    <option value="ERN">ERN – Eritrean Nakfa</option>
                                    <option value="ETB">ETB – Ethiopian Birr</option>
                                    <option value="FJD">FJD – Fijian Dollar</option>
                                    <option value="GMD">GMD – Gambian Dalasi</option>
                                    <option value="GEL">GEL – Georgian Lari</option>
                                    <option value="GHS">GHS – Ghanaian Cedi</option>
                                    <option value="GTQ">GTQ – Guatemalan Quetzal</option>
                                    <option value="GNF">GNF – Guinean Franc</option>
                                    <option value="GYD">GYD – Guyanaese Dollar</option>
                                    <option value="HTG">HTG – Haitian Gourde</option>
                                    <option value="HNL">HNL – Honduran Lempira</option>
                                    <option value="HKD">HKD – Hong Kong Dollar</option>
                                    <option value="HUF">HUF – Hungarian Forint</option>
                                    <option value="ISK">ISK – Icelandic Króna</option>
                                    <option value="IDR">IDR – Indonesian Rupiah</option>
                                    <option value="IRR">IRR – Iranian Rial</option>
                                    <option value="IQD">IQD – Iraqi Dinar</option>
                                    <option value="ILS">ILS – Israeli Shekel</option>
                                    <option value="JMD">JMD – Jamaican Dollar</option>
                                    <option value="JOD">JOD – Jordanian Dinar</option>
                                    <option value="KZT">KZT – Kazakhstani Tenge</option>
                                    <option value="KES">KES – Kenyan Shilling</option>
                                    <option value="KWD">KWD – Kuwaiti Dinar</option>
                                    <option value="KGS">KGS – Kyrgystani Som</option>
                                    <option value="LAK">LAK – Laotian Kip</option>
                                    <option value="LBP">LBP – Lebanese Pound</option>
                                    <option value="LSL">LSL – Lesotho Loti</option>
                                    <option value="LRD">LRD – Liberian Dollar</option>
                                    <option value="LYD">LYD – Libyan Dinar</option>
                                    <option value="MOP">MOP – Macanese Pataca</option>
                                    <option value="MKD">MKD – Macedonian Denar</option>
                                    <option value="MGA">MGA – Malagasy Ariary</option>
                                    <option value="MWK">MWK – Malawian Kwacha</option>
                                    <option value="MYR">MYR – Malaysian Ringgit</option>
                                    <option value="MVR">MVR – Maldivian Rufiyaa</option>
                                    <option value="MRU">MRU – Mauritanian Ouguiya</option>
                                    <option value="MUR">MUR – Mauritian Rupee</option>
                                    <option value="MXN">MXN – Mexican Peso</option>
                                    <option value="MDL">MDL – Moldovan Leu</option>
                                    <option value="MNT">MNT – Mongolian Tugrik</option>
                                    <option value="MAD">MAD – Moroccan Dirham</option>
                                    <option value="MZN">MZN – Mozambican Metical</option>
                                    <option value="MMK">MMK – Myanmar Kyat</option>
                                    <option value="NAD">NAD – Namibian Dollar</option>
                                    <option value="NPR">NPR – Nepalese Rupee</option>
                                    <option value="NZD">NZD – New Zealand Dollar</option>
                                    <option value="NIO">NIO – Nicaraguan Córdoba</option>
                                    <option value="NGN">NGN – Nigerian Naira</option>
                                    <option value="NOK">NOK – Norwegian Krone</option>
                                    <option value="OMR">OMR – Omani Rial</option>
                                    <option value="PKR">PKR – Pakistani Rupee</option>
                                    <option value="PAB">PAB – Panamanian Balboa</option>
                                    <option value="PGK">PGK – Papua New Guinean Kina</option>
                                    <option value="PYG">PYG – Paraguayan Guarani</option>
                                    <option value="PEN">PEN – Peruvian Sol</option>
                                    <option value="PHP">PHP – Philippine Peso</option>
                                    <option value="PLN">PLN – Polish Zloty</option>
                                    <option value="QAR">QAR – Qatari Rial</option>
                                    <option value="RON">RON – Romanian Leu</option>
                                    <option value="RUB">RUB – Russian Ruble</option>
                                    <option value="RWF">RWF – Rwandan Franc</option>
                                    <option value="SAR">SAR – Saudi Riyal</option>
                                    <option value="RSD">RSD – Serbian Dinar</option>
                                    <option value="SLL">SLL – Sierra Leonean Leone</option>
                                    <option value="SOS">SOS – Somali Shilling</option>
                                    <option value="ZAR">ZAR – South African Rand</option>
                                    <option value="KRW">KRW – South Korean Won</option>
                                    <option value="SSP">SSP – South Sudanese Pound</option>
                                    <option value="LKR">LKR – Sri Lankan Rupee</option>
                                    <option value="SDG">SDG – Sudanese Pound</option>
                                    <option value="SRD">SRD – Surinamese Dollar</option>
                                    <option value="SZL">SZL – Swazi Lilangeni</option>
                                    <option value="SEK">SEK – Swedish Krona</option>
                                    <option value="CHF">CHF – Swiss Franc</option>
                                    <option value="TWD">TWD – Taiwan Dollar</option>
                                    <option value="TJS">TJS – Tajikistani Somoni</option>
                                    <option value="TZS">TZS – Tanzanian Shilling</option>
                                    <option value="THB">THB – Thai Baht</option>
                                    <option value="TOP">TOP – Tongan Paʻanga</option>
                                    <option value="TTD">TTD – Trinidad & Tobago Dollar</option>
                                    <option value="TND">TND – Tunisian Dinar</option>
                                    <option value="TRY">TRY – Turkish Lira</option>
                                    <option value="TMT">TMT – Turkmenistani Manat</option>
                                    <option value="UGX">UGX – Ugandan Shilling</option>
                                    <option value="UAH">UAH – Ukrainian Hryvnia</option>
                                    <option value="UYU">UYU – Uruguayan Peso</option>
                                    <option value="UZS">UZS – Uzbekistani Som</option>
                                    <option value="VUV">VUV – Vanuatu Vatu</option>
                                    <option value="VES">VES – Venezuelan Bolívar</option>
                                    <option value="VND">VND – Vietnamese Dong</option>
                                    <option value="YER">YER – Yemeni Rial</option>
                                    <option value="ZMW">ZMW – Zambian Kwacha</option>
                                    <option value="ZWL">ZWL – Zimbabwean Dollar</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="0.01" class="form-control" name="quantity" id="bi_quantity" value="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit Price</label>
                            <input type="number" step="0.01" class="form-control" name="unit_price" id="bi_unit_price" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount</label>
                            <input class="form-control" id="bi_amount_preview" value="0.00" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Billing Month (Optional)</label>
                            <input class="form-control" name="billing_month" id="bi_billing_month" placeholder="Mar 2026">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveBillable">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        window.BMS = window.BMS || {};
        BMS.initBillableItems && BMS.initBillableItems();
    });
</script>
<?= $this->endSection() ?>
