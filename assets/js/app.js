(function () {
    window.BMS = window.BMS || {};
    const SIDEBAR_KEY = 'bms_sidebar_closed';

    function base(path) {
        return (window.APP_BASE_URL || '/') + String(path || '').replace(/^\/+/, '');
    }

    function notify(msg, type) {
        type = type || 'info';
        const cls = type === 'success' ? 'alert-success' : (type === 'danger' ? 'alert-danger' : 'alert-info');
        const $box = $('<div class="alert ' + cls + ' alert-dismissible fade show bms-alert mb-3" role="alert"></div>');
        $box.text(msg || '');
        $box.append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');

        const $modalBody = $('.modal.show .modal-body').first();
        if ($modalBody.length) {
            $modalBody.find('.bms-alert').remove();
            $modalBody.prepend($box);
        } else {
            $('.bms-alert').remove();
            $('main.container-fluid').prepend($box);
        }
        setTimeout(function () { $box.alert('close'); }, 4500);
    }

    function postJson(url, data) {
        return $.ajax({
            url: base(url),
            method: 'POST',
            data: data,
            dataType: 'json'
        });
    }

    function getJson(url, data) {
        return $.ajax({
            url: base(url),
            method: 'GET',
            data: data || {},
            dataType: 'json'
        });
    }

    function dtDefaults() {
        return {
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            // Move "entries per page" (length) control to bottom on all tables
            dom:
                "<'row g-2 align-items-center mb-2'<'col-12 col-md-6'i><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row g-2 align-items-center mt-2'<'col-12 col-md-6'l><'col-12 col-md-6 text-end'p>>",
        };
    }

    function formatUiDate(value, type) {
        const raw = String(value || '').trim();
        if (type === 'sort' || type === 'type') return raw;
        if (!raw) return '-';

        const datePart = raw.slice(0, 10);
        const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(datePart);
        if (!m) return raw;

        const yyyy = m[1];
        const mm = parseInt(m[2], 10);
        const dd = m[3];
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const mon = months[mm - 1] || '';
        if (!mon) return raw;
        return dd + ' ' + mon + ' ' + yyyy;
    }

    // Date helpers (ISO <-> DD/MM/YYYY)
    function isoToDmy(iso) {
        const raw = String(iso || '').trim().slice(0, 10);
        const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(raw);
        if (!m) return '';
        return m[3] + '/' + m[2] + '/' + m[1];
    }

    function isoToDmyText(iso) {
        const raw = String(iso || '').trim().slice(0, 10);
        const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(raw);
        if (!m) return '';
        const yyyy = m[1];
        const mm = parseInt(m[2], 10);
        const dd = m[3];
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const mon = months[mm - 1];
        if (!mon) return '';
        return dd + ' ' + mon + ' ' + yyyy;
    }

    function dmyToIso(dmy) {
        const raw = String(dmy || '').trim();
        if (!raw) return '';
        const m = /^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/.exec(raw);
        if (!m) return '';
        const dd = String(m[1]).padStart(2, '0');
        const mm = String(m[2]).padStart(2, '0');
        const yyyy = m[3];
        const d = parseInt(dd, 10), mo = parseInt(mm, 10);
        if (mo < 1 || mo > 12 || d < 1 || d > 31) return '';
        return yyyy + '-' + mm + '-' + dd;
    }

    function textToIso(dmyText) {
        const raw = String(dmyText || '').trim();
        if (!raw) return '';
        const m = /^(\d{1,2})\s+([A-Za-z]{3})\s+(\d{4})$/.exec(raw);
        if (!m) return dmyToIso(raw);
        const dd = String(m[1]).padStart(2, '0');
        const mon = m[2].toLowerCase();
        const yyyy = m[3];
        const map = { jan:1, feb:2, mar:3, apr:4, may:5, jun:6, jul:7, aug:8, sep:9, oct:10, nov:11, dec:12 };
        const mo = map[mon] || 0;
        const d = parseInt(dd, 10);
        if (mo < 1 || mo > 12 || d < 1 || d > 31) return '';
        const mm = String(mo).padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd;
    }

    function addDaysToIso(iso, days) {
        const raw = String(iso || '').trim().slice(0, 10);
        const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(raw);
        if (!m) return '';
        const year = parseInt(m[1], 10);
        const month = parseInt(m[2], 10) - 1;
        const day = parseInt(m[3], 10);
        const delta = parseInt(days, 10);
        if (!isFinite(year) || !isFinite(month) || !isFinite(day) || !isFinite(delta)) return '';
        const dt = new Date(year, month, day);
        dt.setDate(dt.getDate() + delta);
        const yyyy = dt.getFullYear();
        const mm = String(dt.getMonth() + 1).padStart(2, '0');
        const dd = String(dt.getDate()).padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd;
    }

    function initPremiumDatePickers(container) {
        if (typeof flatpickr === 'undefined') return;
        const parent = container || document;
        
        // Month Picker
        $(parent).find('.bms-month-picker').each(function() {
            if (this._flatpickr) return;
            flatpickr(this, {
                dateFormat: "M Y",
                allowInput: true,
                appendTo: document.body,
                disableMobile: "true",
                onReady: function(selectedDates, dateStr, instance) {
                    const $input = $(instance.input);
                    $input.on('change', function() {
                        if (!$input.val()) instance.clear();
                    });
                }
            });
        });

        // Date Picker
        const $inputs = $(parent).find('input[type="date"], .bms-date-picker, #filterStartDate, #filterEndDate, #pfFilterStartDate, #pfFilterEndDate, #payFilterStartDate, #payFilterEndDate, #prFilterStartDate, #prFilterEndDate, #payDate, #bi_entry_date');
        
        $inputs.each(function() {
            if (this._flatpickr) return;
            flatpickr(this, {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                allowInput: true,
                appendTo: document.body,
                disableMobile: "true"
            });
        });
    }
    BMS.initPremiumDatePickers = initPremiumDatePickers;

    function formatUiDateDmy(value, type) {
        const raw = String(value || '').trim();
        if (!raw) return type === 'display' ? '-' : '';
        if (type === 'display') return isoToDmy(raw) || raw;
        return raw;
    }

    function readDateFieldIso(selector, parseUi) {
        const $el = $(selector);
        if (!$el.length) return '';
        const raw = String($el.val() || '').trim();
        if (!raw) return '';
        if (String($el.attr('type') || '').toLowerCase() === 'date') {
            return raw.slice(0, 10);
        }
        const toIso = typeof parseUi === 'function' ? parseUi : dmyToIso;
        return toIso(raw);
    }

    function setDateFieldValue(selector, iso, formatUi) {
        const $el = $(selector);
        if (!$el.length) return;
        const value = String(iso || '').trim().slice(0, 10);
        if (String($el.attr('type') || '').toLowerCase() === 'date') {
            $el.val(value);
            return;
        }
        const toUi = typeof formatUi === 'function' ? formatUi : isoToDmy;
        $el.val(value ? toUi(value) : '');
    }

    // Attach native calendar to a text input (DD/MM/YYYY) using a hidden <input type="date">
    function attachNativeCalendar(textEl, nativeEl, onIsoChange, formatUi, parseUi) {
        const $txt = $(textEl);
        if (!$txt.length) return;
        const toUi = typeof formatUi === 'function' ? formatUi : isoToDmy;
        const toIso = typeof parseUi === 'function' ? parseUi : dmyToIso;
        let $nat = $(nativeEl);
        const $btn = $txt.closest('.bms-date-wrap').find('.bms-date-btn');

        if (!$nat.length && typeof nativeEl === 'string' && nativeEl.charAt(0) === '#') {
            const nativeId = nativeEl.slice(1);
            const $created = $('<input>', {
                type: 'date',
                id: nativeId,
                class: 'bms-native-date',
                'aria-label': 'Pick date'
            }).css({
                position: 'fixed',
                top: '-100px',
                left: '-100px',
                width: '1px',
                minWidth: '1px',
                maxWidth: '1px',
                height: '1px',
                minHeight: '1px',
                opacity: 0,
                pointerEvents: 'none',
                zIndex: -1
            });

            $('body').append($created);
            $nat = $created;
        }

        if (!$nat.length) return;
        const isDetachedPicker = !$nat.closest('.bms-date-wrap').length;

        function placeNativeNearTrigger() {
            const anchor = ($btn.length ? $btn.get(0) : $txt.get(0));
            if (!anchor) return;
            const rect = anchor.getBoundingClientRect();
            if (isDetachedPicker) {
                $nat.css({
                    top: Math.max(rect.top, 0) + 'px',
                    left: Math.max(rect.left, 0) + 'px',
                    width: Math.max(rect.width, 44) + 'px',
                    minWidth: Math.max(rect.width, 44) + 'px',
                    maxWidth: Math.max(rect.width, 44) + 'px',
                    height: Math.max(rect.height, 36) + 'px',
                    minHeight: Math.max(rect.height, 36) + 'px',
                    zIndex: 1055
                });
                return;
            }

            $nat.css({
                top: Math.max(rect.top + Math.min(rect.height / 2, 12), 0) + 'px',
                left: Math.max(rect.left + rect.width - 2, 0) + 'px',
                zIndex: 1055
            });
        }

        function hideNative() {
            const hiddenState = {
                top: '-100px',
                left: '-100px',
                zIndex: -1
            };

            if (isDetachedPicker) {
                hiddenState.width = '1px';
                hiddenState.minWidth = '1px';
                hiddenState.maxWidth = '1px';
                hiddenState.height = '1px';
                hiddenState.minHeight = '1px';
            }

            $nat.css(hiddenState);
        }

        function currentIsoValue() {
            return String($nat.val() || '').trim().slice(0, 10);
        }

        function syncNativeFromText() {
            const iso = toIso($txt.val()) || currentIsoValue();
            if (iso) {
                $nat.val(iso);
            }
            return iso;
        }

        function openPicker() {
            const el = $nat.get(0);
            if (!el) return;
            syncNativeFromText();
            placeNativeNearTrigger();

            const launchPicker = function () {
                try {
                    if (typeof el.showPicker === 'function') {
                        el.showPicker();
                    } else {
                        el.focus();
                        el.click();
                    }
                } catch (e) {
                    try { el.focus(); } catch (_e) {}
                }
            };

            if (isDetachedPicker && typeof window.requestAnimationFrame === 'function') {
                window.requestAnimationFrame(function () {
                    window.requestAnimationFrame(launchPicker);
                });
                return;
            }

            launchPicker();
        }

        function applyIso(iso) {
            const v = String(iso || '').trim().slice(0, 10);
            $nat.val(v);
            $txt.val(v ? toUi(v) : '');
            onIsoChange && onIsoChange(v);
            hideNative();
        }

        if ($btn.length) {
            $btn.off('click.bmsDate').on('click.bmsDate', function (e) {
                e.preventDefault();
                openPicker();
            });
        }

        $nat.off('pointerdown.bmsDate mousedown.bmsDate focus.bmsDate').on('pointerdown.bmsDate mousedown.bmsDate focus.bmsDate', function () {
            syncNativeFromText();
        });

        $nat.off('change.bmsDate').on('change.bmsDate', function () {
            applyIso($nat.val());
        });

        $nat.off('blur.bmsDate').on('blur.bmsDate', function () {
            window.setTimeout(hideNative, 0);
        });

        $txt.off('input.bmsDate blur.bmsDate').on('input.bmsDate blur.bmsDate', function () {
            const iso = toIso($txt.val());
            if (iso) {
                $nat.val(iso);
                onIsoChange && onIsoChange(iso);
            } else {
                onIsoChange && onIsoChange('');
            }
        });

        const initialIso = syncNativeFromText();
        if (initialIso) {
            $txt.val(toUi(initialIso));
        }
        hideNative();
    }

    function iconSvg(name) {
        const n = String(name || '');
        if (n === 'view') {
            return '' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>';
        }
        if (n === 'edit') {
            return '' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                '<path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>';
        }
        if (n === 'delete') {
            return '' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                '<path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>';
        }
        if (n === 'print') {
            return '' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                '<path d="M7 9V4h10v5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M6 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M7 14h10v6H7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M17 12h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>';
        }
        if (n === 'billed') {
            return '' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                '<path d="M5 12l4 4 10-10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>';
        }
        return '';
    }

    function actionBtn(action, styleClass, title) {
        const a = String(action || '').trim();
        const iconName = a === 'del' ? 'delete' : a;
        const t = String(title || a || '').trim() || 'Action';
        return '' +
            '<button class="btn btn-sm ' + String(styleClass || '') + ' bms-action-btn btn-' + a + '" type="button" title="' + t + '" aria-label="' + t + '">' +
            iconSvg(iconName) +
            '</button>';
    }

    function actionLink(action, styleClass, title, href) {
        const a = String(action || '').trim();
        const iconName = a === 'del' ? 'delete' : a;
        const t = String(title || a || '').trim() || 'Action';
        return '' +
            '<a class="btn btn-sm ' + String(styleClass || '') + ' bms-action-btn btn-' + a + '" href="' + String(href || '#') + '" title="' + t + '" aria-label="' + t + '">' +
            iconSvg(iconName) +
            '</a>';
    }

    function actionGroup(innerHtml) {
        const html = String(innerHtml || '');
        if (!html) return '';
        return '<div class="bms-actions" role="group" aria-label="Actions">' + html + '</div>';
    }

    function escapeHtml(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatMoneyValue(v) {
        const n = parseFloat(String(v || '0').replace(/,/g, '')) || 0;
        try {
            return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } catch (e) {
            return n.toFixed(2);
        }
    }

    const INDIA_STATES = [
        'Andaman and Nicobar Islands',
        'Andhra Pradesh',
        'Arunachal Pradesh',
        'Assam',
        'Bihar',
        'Chandigarh',
        'Chhattisgarh',
        'Dadra and Nagar Haveli and Daman and Diu',
        'Delhi',
        'Goa',
        'Gujarat',
        'Haryana',
        'Himachal Pradesh',
        'Jammu and Kashmir',
        'Jharkhand',
        'Karnataka',
        'Kerala',
        'Ladakh',
        'Lakshadweep',
        'Madhya Pradesh',
        'Maharashtra',
        'Manipur',
        'Meghalaya',
        'Mizoram',
        'Nagaland',
        'Odisha',
        'Puducherry',
        'Punjab',
        'Rajasthan',
        'Sikkim',
        'Tamil Nadu',
        'Telangana',
        'Tripura',
        'Uttar Pradesh',
        'Uttarakhand',
        'West Bengal'
    ];

    const COUNTRY_OPTIONS = [
        'Afghanistan',
        'Albania',
        'Algeria',
        'Andorra',
        'Angola',
        'Antigua and Barbuda',
        'Argentina',
        'Armenia',
        'Australia',
        'Austria',
        'Azerbaijan',
        'Bahamas',
        'Bahrain',
        'Bangladesh',
        'Barbados',
        'Belarus',
        'Belgium',
        'Belize',
        'Benin',
        'Bhutan',
        'Bolivia',
        'Bosnia and Herzegovina',
        'Botswana',
        'Brazil',
        'Brunei',
        'Bulgaria',
        'Burkina Faso',
        'Burundi',
        'Cabo Verde',
        'Cambodia',
        'Cameroon',
        'Canada',
        'Central African Republic',
        'Chad',
        'Chile',
        'China',
        'Colombia',
        'Comoros',
        'Congo',
        'Costa Rica',
        'Croatia',
        'Cuba',
        'Cyprus',
        'Czech Republic',
        'Democratic Republic of the Congo',
        'Denmark',
        'Djibouti',
        'Dominica',
        'Dominican Republic',
        'Ecuador',
        'Egypt',
        'El Salvador',
        'Equatorial Guinea',
        'Eritrea',
        'Estonia',
        'Eswatini',
        'Ethiopia',
        'Fiji',
        'Finland',
        'France',
        'Gabon',
        'Gambia',
        'Georgia',
        'Germany',
        'Ghana',
        'Greece',
        'Grenada',
        'Guatemala',
        'Guinea',
        'Guinea-Bissau',
        'Guyana',
        'Haiti',
        'Honduras',
        'Hungary',
        'Iceland',
        'India',
        'Indonesia',
        'Iran',
        'Iraq',
        'Ireland',
        'Israel',
        'Italy',
        'Jamaica',
        'Japan',
        'Jordan',
        'Kazakhstan',
        'Kenya',
        'Kiribati',
        'Kuwait',
        'Kyrgyzstan',
        'Laos',
        'Latvia',
        'Lebanon',
        'Lesotho',
        'Liberia',
        'Libya',
        'Liechtenstein',
        'Lithuania',
        'Luxembourg',
        'Madagascar',
        'Malawi',
        'Malaysia',
        'Maldives',
        'Mali',
        'Malta',
        'Marshall Islands',
        'Mauritania',
        'Mauritius',
        'Mexico',
        'Micronesia',
        'Moldova',
        'Monaco',
        'Mongolia',
        'Montenegro',
        'Morocco',
        'Mozambique',
        'Myanmar',
        'Namibia',
        'Nauru',
        'Nepal',
        'Netherlands',
        'New Zealand',
        'Nicaragua',
        'Niger',
        'Nigeria',
        'North Korea',
        'North Macedonia',
        'Norway',
        'Oman',
        'Pakistan',
        'Palau',
        'Palestine',
        'Panama',
        'Papua New Guinea',
        'Paraguay',
        'Peru',
        'Philippines',
        'Poland',
        'Portugal',
        'Qatar',
        'Romania',
        'Russia',
        'Rwanda',
        'Saint Kitts and Nevis',
        'Saint Lucia',
        'Saint Vincent and the Grenadines',
        'Samoa',
        'San Marino',
        'Sao Tome and Principe',
        'Saudi Arabia',
        'Senegal',
        'Serbia',
        'Seychelles',
        'Sierra Leone',
        'Singapore',
        'Slovakia',
        'Slovenia',
        'Solomon Islands',
        'Somalia',
        'South Africa',
        'South Korea',
        'South Sudan',
        'Spain',
        'Sri Lanka',
        'Sudan',
        'Suriname',
        'Sweden',
        'Switzerland',
        'Syria',
        'Taiwan',
        'Tajikistan',
        'Tanzania',
        'Thailand',
        'Timor-Leste',
        'Togo',
        'Tonga',
        'Trinidad and Tobago',
        'Tunisia',
        'Turkey',
        'Turkmenistan',
        'Tuvalu',
        'Uganda',
        'Ukraine',
        'United Arab Emirates',
        'United Kingdom',
        'United States',
        'Uruguay',
        'Uzbekistan',
        'Vanuatu',
        'Vatican City',
        'Venezuela',
        'Vietnam',
        'Yemen',
        'Zambia',
        'Zimbabwe'
    ];

    function populateSelectOptions($select, values, placeholder, currentValue) {
        const current = String(currentValue || '').trim();
        const list = Array.isArray(values) ? values : [];

        $select.empty();
        $select.append($('<option></option>').val('').text(String(placeholder || 'Select')));

        list.forEach(function (value) {
            $select.append($('<option></option>').val(value).text(value));
        });

        if (current && list.indexOf(current) === -1) {
            $select.append($('<option></option>').val(current).text(current));
        }

        $select.val(current);
    }

    function isIndiaCountry(country) {
        const value = String(country || '').trim().toLowerCase();
        return value === '' || value === 'india' || value === 'in';
    }

    function resolveInvoiceTypeByCountry(country) {
        return isIndiaCountry(country) ? 'GST Invoice' : 'Export Invoice';
    }

    function descriptionPlainText(value) {
        const raw = String(value || '');
        if (!raw) return '';

        // If it looks like HTML with list items, extract li text.
        if (raw.indexOf('<') !== -1 && /<li[\s>]/i.test(raw)) {
            try {
                const tmp = document.createElement('div');
                tmp.innerHTML = raw;
                const lis = tmp.querySelectorAll('li');
                if (lis && lis.length) {
                    const parts = [];
                    lis.forEach(function (li) {
                        const t = String(li.innerText || li.textContent || '').trim();
                        if (t) parts.push(t);
                    });
                    return parts.join(' ');
                }
            } catch (e) {}
        }

        return raw
            .replace(/<[^>]*>/g, ' ')
            .replace(/\u00A0/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function renderTruncatedDescription(value, type, maxChars) {
        const full = descriptionPlainText(value);
        if (type === 'sort' || type === 'type' || type === 'filter') return full;
        if (!full) return '-';

        const limit = parseInt(maxChars || 20, 10) || 20;
        const shown = full.length > limit ? (full.slice(0, limit) + '...') : full;
        const safe = function (s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };

        // Use native browser tooltip via `title` (works without extra JS init).
        return '<span title="' + safe(full) + '">' + safe(shown) + '</span>';
    }

    function renderTruncatedDescriptionBullets(value, type, maxChars) {
        const raw = String(value || '');
        const limit = parseInt(maxChars || 30, 10) || 30;

        // For DataTables search/sort, return full plain text.
        if (type === 'sort' || type === 'type' || type === 'filter') {
            return descriptionPlainText(raw);
        }

        const safe = function (s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };

        // Split into bullet lines.
        let lines = [];
        if (raw.indexOf('<') !== -1 && /<li[\s>]/i.test(raw)) {
            try {
                const tmp = document.createElement('div');
                tmp.innerHTML = raw;
                const lis = tmp.querySelectorAll('li');
                if (lis && lis.length) {
                    lis.forEach(function (li) {
                        const t = String(li.innerText || li.textContent || '').trim();
                        if (t) lines.push(t);
                    });
                }
            } catch (e) {}
        }
        if (!lines.length) {
            lines = raw.split(/\r?\n/).map(function (l) { return l.trim(); }).filter(function (l) { return l !== ''; });
        }
        if (!lines.length) return '-';

        const full = lines.join('\n');
        const hasMore = lines.length > 2;
        const shownLines = lines.slice(0, 2).map(function (l, idx) {
            let out = l;
            if (out.length > limit) {
                out = out.slice(0, limit) + '...';
            }
            // If there are more than 2 bullets, add ellipsis only at the 2nd bullet point.
            if (hasMore && idx === 1 && !out.endsWith('...')) {
                out = out + '...';
            }
            return out;
        });

        const lisHtml = shownLines.map(function (l) {
            return '<li>' + safe(l) + '</li>';
        }).join('');

        return '<ul class="mb-0" title="' + safe(full) + '">' + lisHtml + '</ul>';
    }

    function renderBulletText(value, type) {
        const raw = String(value || '');
        if (type === 'sort' || type === 'type') return raw;

        let lines = [];
        if (raw.indexOf('<') !== -1 && /<li[\s>]/i.test(raw)) {
            try {
                const tmp = document.createElement('div');
                tmp.innerHTML = raw;
                const lis = tmp.querySelectorAll('li');
                if (lis && lis.length) {
                    lis.forEach(function (li) {
                        const t = String(li.innerText || li.textContent || '').trim();
                        if (t) lines.push(t);
                    });
                }
            } catch (e) {}
        }

        if (!lines.length) {
            lines = raw.split(/\r?\n/).map(function (l) { return l.trim(); }).filter(function (l) { return l !== ''; });
        }
        if (!lines.length) return '-';
        const safe = function (s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };
        return '<ul class="mb-0">' + lines.map(function (l) { return '<li>' + safe(l) + '</li>'; }).join('') + '</ul>';
    }

    function billableStatusBadge(status) {
        const v = String(status || '').trim();
        const cls = v === 'Billed' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle';
        const label = v === 'Billed' ? 'Billed' : 'Pending';
        return '<span class="badge rounded-pill ' + cls + '">' + label + '</span>';
    }

    function statusSelectHtml(tableName, recordId, currentValue, options) {
        const current = (currentValue === null || currentValue === undefined || currentValue === '') ? '' : String(currentValue);
        const opts = (options || []).map(function (v) {
            const selected = String(v) === current ? 'selected' : '';
            return '<option value="' + String(v).replace(/"/g, '&quot;') + '" ' + selected + '>' + v + '</option>';
        }).join('');

        return '' +
            '<select class="form-select form-select-sm bms-status-select" ' +
            'data-table="' + tableName + '" data-id="' + recordId + '" data-old="' + current.replace(/"/g, '&quot;') + '">' +
            opts +
            '</select>';
    }

    BMS.initDashboard = function (opts) {
        opts = opts || {};
        const $month = $('#dashMonth');
        const $monthPicker = $('#dashMonthPicker');
        const $monthTrigger = $('#dashMonthTrigger');
        const $monthTriggerLabel = $('#dashMonthTriggerLabel');
        const $monthPanel = $('#dashMonthPanel');
        const $monthYear = $('#dashMonthYear');
        const $monthPrevYear = $('#dashMonthPrevYear');
        const $monthNextYear = $('#dashMonthNextYear');
        const $monthToday = $('#dashMonthToday');
        const $monthClose = $('#dashMonthClose');
        const $dashHero = $month.closest('.dash-hero');
        if (!$month.length) return;

        let currentMonth = String($month.val() || opts.defaultMonth || '').trim();
        if (!currentMonth) {
            const now = new Date();
            currentMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
            $month.val(currentMonth);
        }
        let pickerYear = parseInt(String(currentMonth).slice(0, 4), 10) || new Date().getFullYear();
        const monthLabelsShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        function fmtMoney(v) {
            const n = parseFloat(v || 0) || 0;
            try {
                return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } catch (e) {
                return n.toFixed(2);
            }
        }

        function formatMonthLabel(ym) {
            const raw = String(ym || '').trim();
            const m = /^(\d{4})-(\d{2})$/.exec(raw);
            if (!m) return raw || 'Current Month';
            const dt = new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, 1);
            try {
                return dt.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
            } catch (e) {
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                return (months[dt.getMonth()] || raw) + ' ' + dt.getFullYear();
            }
        }

        function syncDashboardMonthLabels() {
            const label = formatMonthLabel(currentMonth);
            $('#dashMonthLabel').text(label);
            $('#dashWindowTitle').text(label);
            $monthTriggerLabel.text(label);
        }

        function parseMonthValue(ym) {
            const raw = String(ym || '').trim();
            const m = /^(\d{4})-(\d{2})$/.exec(raw);
            if (!m) return null;
            return {
                year: parseInt(m[1], 10),
                month: parseInt(m[2], 10)
            };
        }

        function renderDashboardMonthPicker() {
            if (!$monthPanel.length) return;
            $monthYear.text(String(pickerYear));
            const currentParts = parseMonthValue(currentMonth);
            const now = new Date();
            const realYear = now.getFullYear();
            const realMonth = now.getMonth() + 1;

            $monthPanel.find('.dash-month-chip').each(function (idx) {
                const monthNum = idx + 1;
                const isSelected = !!currentParts && currentParts.year === pickerYear && currentParts.month === monthNum;
                const isCurrent = pickerYear === realYear && realMonth === monthNum;
                $(this)
                    .toggleClass('is-selected', isSelected)
                    .toggleClass('is-current', isCurrent)
                    .attr('aria-pressed', isSelected ? 'true' : 'false')
                    .text(monthLabelsShort[idx]);
            });
        }

        function openDashboardMonthPicker() {
            const currentParts = parseMonthValue(currentMonth);
            pickerYear = currentParts ? currentParts.year : pickerYear;
            renderDashboardMonthPicker();
            $monthPanel.removeClass('d-none');
            $monthTrigger.attr('aria-expanded', 'true');
            $dashHero.addClass('is-month-open');
        }

        function closeDashboardMonthPicker() {
            $monthPanel.addClass('d-none');
            $monthTrigger.attr('aria-expanded', 'false');
            $dashHero.removeClass('is-month-open');
        }

        function updateDashboardMonth(nextMonth) {
            currentMonth = String(nextMonth || '').trim();
            if (!currentMonth) return;
            $month.val(currentMonth);
            syncDashboardMonthLabels();
            renderDashboardMonthPicker();
            loadMetrics();
            summaryTable.ajax.reload();
        }

        function setMetric(id, value, isMoney) {
            const $el = $(id);
            if (!$el.length) return;
            $el.text(isMoney ? fmtMoney(value) : String(value || 0));
        }

        function loadMetrics() {
            getJson('dashboard/metrics', { month: currentMonth })
                .done(function (res) {
                    const d = (res && res.data) ? res.data : {};
                    const totalItems = parseInt(d.total_items || 0, 10) || 0;
                    const pendingItems = parseInt(d.pending_items || 0, 10) || 0;
                    const billedItems = parseInt(d.billed_items || 0, 10) || 0;
                    const pendingAmount = parseFloat(d.pending_amount || 0) || 0;
                    const billedAmount = parseFloat(d.billed_amount || 0) || 0;
                    setMetric('#mTotalItems', d.total_items, false);
                    setMetric('#mPendingItems', d.pending_items, false);
                    setMetric('#mBilledItems', d.billed_items, false);
                    setMetric('#mPendingAmount', d.pending_amount, true);
                    setMetric('#mBilledAmount', d.billed_amount, true);

                    const label = formatMonthLabel(currentMonth);
                    let insight = 'Tracking billing activity for ' + label + '.';
                    if (totalItems > 0) {
                        insight = billedItems + ' of ' + totalItems + ' items are already billed for ' + label + ', with ' + fmtMoney(pendingAmount) + ' still pending and ' + fmtMoney(billedAmount) + ' already billed.';
                    } else if (pendingItems > 0 || billedItems > 0) {
                        insight = 'Billing movement is available for ' + label + ', including ' + pendingItems + ' pending items and ' + billedItems + ' billed items.';
                    }
                    $('#dashInsight').text(insight);
                })
                .fail(function (xhr) {
                    notify((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to load dashboard metrics.', 'danger');
                });
        }

        const summaryTable = $('#dtClientBillingSummary').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: {
                url: base('dashboard/client-billing-summary'),
                data: function (d) { d.month = currentMonth; },
                dataSrc: 'data'
            },
            order: [[0, 'asc']],
            columns: [
                { data: 'client_name', render: function (d) { return d || '-'; } },
                { data: 'pending_items', className: 'text-center', render: function (d) { return parseInt(d || 0, 10) || 0; } },
                { data: 'pending_amount', className: 'text-end', render: function (d) { return fmtMoney(d); } },
                { data: 'billed_items', className: 'text-center', render: function (d) { return parseInt(d || 0, 10) || 0; } },
                { data: 'billed_amount', className: 'text-end', render: function (d) { return fmtMoney(d); } },
                { data: 'total_amount', className: 'text-end', render: function (d) { return fmtMoney(d); } },
                { data: null, orderable: false, className: 'text-center', render: function (row) {
                    if (!row || !row.client_id) return '';
                    const href = base('billable-items?client_id=' + row.client_id + '&month=' + encodeURIComponent(currentMonth) + '&status=all');
                    return '<div class="bms-actions justify-content-center"><a class="btn btn-sm bms-action-btn btn-view" href="' + href + '" title="View Client Billing" aria-label="View Client Billing"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M1 12C2.73 7.61 7 4.5 12 4.5s9.27 3.11 11 7.5c-1.73 4.39-6 7.5-11 7.5S2.73 16.39 1 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg></a></div>';
                }},
            ],
        }));

        summaryTable.on('xhr.dt', function (e, settings, json) {
            if (json && json.success === false && json.message) notify(json.message, 'danger');
        });

        $monthTrigger.on('click', function (e) {
            e.preventDefault();
            if ($monthPanel.hasClass('d-none')) {
                openDashboardMonthPicker();
            } else {
                closeDashboardMonthPicker();
            }
        });

        $monthPrevYear.on('click', function () {
            pickerYear -= 1;
            renderDashboardMonthPicker();
        });

        $monthNextYear.on('click', function () {
            pickerYear += 1;
            renderDashboardMonthPicker();
        });

        $monthPanel.on('click', '.dash-month-chip', function () {
            const month = String($(this).data('month') || '').padStart(2, '0');
            if (!month) return;
            updateDashboardMonth(String(pickerYear) + '-' + month);
            closeDashboardMonthPicker();
        });

        $monthToday.on('click', function () {
            const now = new Date();
            pickerYear = now.getFullYear();
            updateDashboardMonth(now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0'));
            closeDashboardMonthPicker();
        });

        $monthClose.on('click', function () {
            closeDashboardMonthPicker();
        });

        $(document).off('mousedown.dashMonth').on('mousedown.dashMonth', function (e) {
            if (!$monthPicker.length) return;
            if ($(e.target).closest('#dashMonthPicker').length) return;
            closeDashboardMonthPicker();
        });

        syncDashboardMonthLabels();
        renderDashboardMonthPicker();
        loadMetrics();
    };

    BMS.initClientMaster = function () {
        const $modal = new bootstrap.Modal(document.getElementById('clientModal'));
        const $saveBtn = $('#btnSaveClient');
        const $form = $('#clientForm');
        const $state = $('#client_state');
        const $country = $('#client_country');
        let lastManualBilling = '';
        let lastManualBillingLines = { line1: '', line2: '' };

        function normalizeLine(v) {
            return String(v || '').replace(/\s+/g, ' ').trim();
        }

        function joinLines(line1, line2) {
            const a = normalizeLine(line1);
            const b = normalizeLine(line2);
            if (a && b) return a + "\n" + b;
            return a || b || '';
        }

        function splitLines(value) {
            const raw = String(value || '').trim();
            if (!raw) return { line1: '', line2: '' };

            const parts = raw.split(/\r?\n/).map(function (p) { return normalizeLine(p); }).filter(Boolean);
            if (parts.length >= 2) {
                return { line1: parts[0] || '', line2: parts.slice(1).join(' ') || '' };
            }

            // Fallback: try to split on first comma.
            const idx = raw.indexOf(',');
            if (idx > -1) {
                const p1 = normalizeLine(raw.slice(0, idx));
                const p2 = normalizeLine(raw.slice(idx + 1));
                return { line1: p1, line2: p2 };
            }

            return { line1: normalizeLine(raw), line2: '' };
        }

        function syncHiddenAddress() {
            $('#client_address').val(joinLines($('#client_address_line1').val(), $('#client_address_line2').val()));
        }

        function syncHiddenBilling() {
            $('#client_billing_address').val(joinLines($('#client_billing_line1').val(), $('#client_billing_line2').val()));
        }

        function setFormMode(mode) {
            const isView = mode === 'view';
            $form.find('input,select,textarea').prop('disabled', isView);
            $saveBtn.toggle(!isView);
            syncSameAsState();
        }

        function clearErrors() {
            $('#clientForm .is-invalid').removeClass('is-invalid');
            $('#clientForm [data-err]').text('');
        }

        function setLocationValues(stateValue, countryValue) {
            populateSelectOptions($state, INDIA_STATES, 'Select State', stateValue);
            populateSelectOptions($country, COUNTRY_OPTIONS, 'Select Country', countryValue);
        }

        function setSameAs(on) {
            const $bill1 = $('#client_billing_line1');
            const $bill2 = $('#client_billing_line2');
            const $addr1 = $('#client_address_line1');
            const $addr2 = $('#client_address_line2');
            if (on) {
                if (! $bill1.prop('disabled')) {
                    lastManualBillingLines = {
                        line1: String($bill1.val() || ''),
                        line2: String($bill2.val() || ''),
                    };
                }
                $bill1.val($addr1.val() || '');
                $bill2.val($addr2.val() || '');
                $bill1.prop('disabled', true);
                $bill2.prop('disabled', true);
                syncHiddenBilling();
            } else {
                $bill1.prop('disabled', false);
                $bill2.prop('disabled', false);
                const current = joinLines($bill1.val(), $bill2.val());
                const addr = joinLines($addr1.val(), $addr2.val());
                if (String(current || '') === String(addr || '')) {
                    $bill1.val(lastManualBillingLines.line1 || '');
                    $bill2.val(lastManualBillingLines.line2 || '');
                    syncHiddenBilling();
                }
            }
        }

        function syncSameAsState() {
            const isView = $form.find('input,select,textarea').first().prop('disabled');
            const $chk = $('#client_same_as_address');
            const $bill1 = $('#client_billing_line1');
            const $bill2 = $('#client_billing_line2');
            $chk.prop('disabled', isView);

            if ($chk.is(':checked')) {
                $bill1.prop('disabled', true);
                $bill2.prop('disabled', true);
                $bill1.val($('#client_address_line1').val() || '');
                $bill2.val($('#client_address_line2').val() || '');
                syncHiddenBilling();
            }
        }

        const table = $('#dtClients').DataTable($.extend(true, {}, dtDefaults(), {
            responsive: false,
            scrollX: false,
            autoWidth: false,
            ajax: { url: base('masters/client-master/list'), dataSrc: 'data' },
            order: [[0, 'asc']],
            columns: [
                { data: 'name', width: '18%', render: function (d, type, row) {
                    const v = String(d || '').trim();
                    if (type === 'sort' || type === 'type') return v || String(row.contact_person || '').trim();
                    return v || '-';
                }},
                { data: 'email', width: '23%', render: function (d) { return (String(d || '').trim() || '-'); } },
                { data: 'address', width: '29%', orderable: false, render: function (d) {
                    const raw = String(d || '').replace(/\s+/g, ' ').trim();
                    if (!raw) return '-';
                    const short = raw.length > 60 ? (raw.slice(0, 60) + '…') : raw;
                    const esc = function (s) {
                        return String(s)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/\"/g, '&quot;')
                            .replace(/'/g, '&#39;');
                    };
                    return '<span title="' + esc(raw) + '">' + esc(short) + '</span>';
                }},
                { data: 'city', width: '10%', render: function (d) { return (String(d || '').trim() || '-'); } },
                { data: 'country', width: '10%', className: 'text-nowrap', render: function (d) { return (String(d || '').trim() || '-'); } },
                { data: null, width: '10%', className: 'text-nowrap', orderable: false, render: function (row) {
                    return actionGroup(
                        actionBtn('view', 'btn-outline-dark', 'View') +
                        actionBtn('edit', 'btn-outline-primary', 'Edit') +
                        actionBtn('del', 'btn-outline-danger', 'Delete')
                    );
                }},
            ],
        }));

        $('#btnAddClient').on('click', function () {
            clearErrors();
            $('#clientModalTitle').text('Add Client');
            $('#clientForm')[0].reset();
            $('#clientForm').removeClass('was-validated');
            $('#client_id').val('');
            $('#client_gst_no').val('');
            lastManualBilling = '';
            $('#client_same_as_address').prop('checked', false);
            $('#client_billing_line1,#client_billing_line2').prop('disabled', false);
            $('#client_address_line1,#client_address_line2,#client_billing_line1,#client_billing_line2').val('');
            $('#client_address,#client_billing_address').val('');
            setLocationValues('', '');
            setFormMode('edit');
            $modal.show();
        });

        $('#dtClients tbody').on('click', 'button.btn-view', function () {
            clearErrors();
            const row = table.row($(this).closest('tr')).data();
            $('#clientModalTitle').text('View Client');
            $('#clientForm').removeClass('was-validated');
            $('#client_id').val(row.id);
            $('#client_name').val(row.name);
            $('#client_contact_person').val(row.contact_person || '');
            $('#client_email').val(row.email || '');
            $('#client_phone').val(row.phone || '');
            $('#client_gst_no').val(row.gst_no || '');
            (function () {
                const a = splitLines(row.address || '');
                $('#client_address_line1').val(a.line1);
                $('#client_address_line2').val(a.line2);
                syncHiddenAddress();
            })();
            (function () {
                const b = splitLines(row.billing_address || '');
                $('#client_billing_line1').val(b.line1);
                $('#client_billing_line2').val(b.line2);
                syncHiddenBilling();
            })();
            $('#client_city').val(row.city || '');
            setLocationValues(row.state || '', row.country || '');
            $('#client_postal_code').val(row.postal_code || '');
            lastManualBillingLines = splitLines(row.billing_address || '');
            $('#client_same_as_address').prop('checked', false);
            setFormMode('view');
            $modal.show();
        });

        $('#dtClients tbody').on('click', 'button.btn-edit', function () {
            clearErrors();
            const row = table.row($(this).closest('tr')).data();
            $('#clientModalTitle').text('Edit Client');
            $('#clientForm').removeClass('was-validated');
            $('#client_id').val(row.id);
            $('#client_name').val(row.name);
            $('#client_contact_person').val(row.contact_person || '');
            $('#client_email').val(row.email || '');
            $('#client_phone').val(row.phone || '');
            $('#client_gst_no').val(row.gst_no || '');
            (function () {
                const a = splitLines(row.address || '');
                $('#client_address_line1').val(a.line1);
                $('#client_address_line2').val(a.line2);
                syncHiddenAddress();
            })();
            (function () {
                const b = splitLines(row.billing_address || '');
                $('#client_billing_line1').val(b.line1);
                $('#client_billing_line2').val(b.line2);
                syncHiddenBilling();
            })();
            $('#client_city').val(row.city || '');
            setLocationValues(row.state || '', row.country || '');
            $('#client_postal_code').val(row.postal_code || '');
            lastManualBillingLines = splitLines(row.billing_address || '');
            $('#client_same_as_address').prop('checked', false);
            setFormMode('edit');
            $modal.show();
        });

        setLocationValues('', '');

        $('#client_same_as_address').on('change', function () {
            setSameAs($(this).is(':checked'));
        });

        $('#client_address_line1,#client_address_line2').on('input', function () {
            syncHiddenAddress();
            if ($('#client_same_as_address').is(':checked')) {
                $('#client_billing_line1').val($('#client_address_line1').val() || '');
                $('#client_billing_line2').val($('#client_address_line2').val() || '');
                syncHiddenBilling();
            }
        });

        $('#client_billing_line1,#client_billing_line2').on('input', function () {
            syncHiddenBilling();
        });

        $('#dtClients tbody').on('click', 'button.btn-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!confirm('Delete this client?')) return;
            postJson('masters/client-master/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#btnSaveClient').on('click', function () {
            clearErrors();
            const form = document.getElementById('clientForm');
            form.classList.add('was-validated');
            if (!form.checkValidity()) {
                notify('Please fill the required fields.', 'danger');
                return;
            }
            postJson('masters/client-master/save', $('#clientForm').serialize())
                .done(function (res) {
                    notify(res.message || 'Saved.', 'success');
                    $modal.hide();
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    notify(res.message || 'Save failed.', 'danger');
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (k) {
                            const $field = $('#clientForm [name="' + k + '"]');
                            $field.addClass('is-invalid');
                            $('#clientForm [data-err="' + k + '"]').text(res.errors[k]);
                        });
                    }
                });
        });
    };

    // Backward compatible alias (older views)
    BMS.initClients = BMS.initClientMaster;

    BMS.initRoles = function (opts) {
        opts = opts || {};
        const $modal = new bootstrap.Modal(document.getElementById('roleModal'));
        const $saveBtn = $('#btnSaveRole');
        const $form = $('#roleForm');

        function setFormMode(mode) {
            const isView = mode === 'view';
            $form.find('input,select,textarea').prop('disabled', isView);
            // Keep modal close button usable.
            $('#roleModal .btn-close').prop('disabled', false);
            $saveBtn.toggle(!isView);
        }

        function clearErrors() {
            $('#roleForm .is-invalid').removeClass('is-invalid');
            $('#roleForm [data-err]').text('');
        }

        function openRole(row, mode) {
            clearErrors();
            row = row || {};
            $('#roleModalTitle').text(mode === 'view' ? 'View Role' : 'Edit Role');
            $('#roleForm').removeClass('was-validated');
            $('#role_id').val(row.id || '');
            $('#role_name').val(row.name || '');
            $('#role_description').val(row.description || '');
            const $isSuper = $('#role_is_super');
            if ($isSuper.length) $isSuper.prop('checked', parseInt(row.is_super || 0, 10) === 1);
            setFormMode(mode);
            $modal.show();
        }

        const table = $('#dtRoles').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('roles/list'), dataSrc: 'data' },
            order: [[1, 'asc']],
            columns: [
                { data: null, orderable: false, render: function (d, t, r, meta) {
                    return (meta.row + meta.settings._iDisplayStart + 1);
                }},
                { data: 'name', render: function (d) { return d || '-'; } },
                { data: 'description', render: function (d) { return d || '-'; } },
                { data: null, orderable: false, className: 'text-start', render: function (row) {
                    let html = actionBtn('view', 'btn-outline-dark', 'View');
                    if (opts.canEdit) html += actionBtn('edit', 'btn-outline-primary', 'Edit');
                    if (opts.canDelete) html += actionBtn('del', 'btn-outline-danger', 'Delete');
                    return actionGroup(html);
                }},
            ],
        }));

        if (opts.canCreate) {
            $('#btnAddRole').on('click', function () {
                clearErrors();
                $('#roleModalTitle').text('Add Role');
                $('#roleForm')[0].reset();
                $('#roleForm').removeClass('was-validated');
                $('#role_id').val('');
                setFormMode('edit');
                $modal.show();
            });
        }

        $('#dtRoles tbody').on('click', 'button.btn-view', function () {
            const row = table.row($(this).closest('tr')).data();
            openRole(row, 'view');
        });

        $('#dtRoles tbody').on('click', 'button.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            openRole(row, 'edit');
        });

        $('#dtRoles tbody').on('click', 'button.btn-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            if (!confirm('Delete this role?')) return;
            postJson('roles/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#btnSaveRole').on('click', function () {
            clearErrors();
            const form = document.getElementById('roleForm');
            form.classList.add('was-validated');
            if (!form.checkValidity()) {
                notify('Please fill the required fields.', 'danger');
                return;
            }

            postJson('roles/save', $('#roleForm').serialize())
                .done(function (res) {
                    notify(res.message || 'Saved.', 'success');
                    $modal.hide();
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    notify(res.message || 'Save failed.', 'danger');
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (k) {
                            const $field = $('#roleForm [name="' + k + '"]');
                            $field.addClass('is-invalid');
                            $('#roleForm [data-err="' + k + '"]').text(res.errors[k]);
                        });
                    }
                });
        });
    };

    BMS.initRolePermissions = function (opts) {
        opts = opts || {};
        if (opts.locked) return;

        const $form = $('#rolePermsForm');
        const $matrix = $form.find('.perm-level');

        function idsFrom($el) {
            const raw = String($el.data('ids') || '').trim();
            if (!raw) return [];
            return raw.split(',')
                .map(function (x) { return parseInt(String(x).trim(), 10); })
                .filter(function (n) { return n > 0; });
        }

        function setHidden(ids, on) {
            ids.forEach(function (id) {
                $form.find('.perm-id[value="' + id + '"]').prop('checked', !!on);
            });
        }

        function anySelected(ids) {
            for (let i = 0; i < ids.length; i++) {
                if ($form.find('.perm-id[value="' + ids[i] + '"]').is(':checked')) return true;
            }
            return false;
        }

        function normalizeRow($row) {
            const $r = $row.find('.perm-level[data-level="read"]').first();
            const $w = $row.find('.perm-level[data-level="write"]').first();
            const $d = $row.find('.perm-level[data-level="delete"]').first();

            const readIds = idsFrom($r);
            const writeIds = idsFrom($w);
            const deleteIds = idsFrom($d);

            const hasDelete = anySelected(deleteIds);
            const hasWrite = anySelected(writeIds);
            const hasRead = anySelected(readIds);

            if (hasDelete) {
                setHidden(readIds, true);
                setHidden(writeIds, true);
                setHidden(deleteIds, true);
            } else if (hasWrite) {
                setHidden(readIds, true);
                setHidden(writeIds, true);
            } else if (!hasRead) {
                setHidden(writeIds, false);
                setHidden(deleteIds, false);
            }
        }

        function syncRow($row) {
            const $r = $row.find('.perm-level[data-level="read"]').first();
            const $w = $row.find('.perm-level[data-level="write"]').first();
            const $d = $row.find('.perm-level[data-level="delete"]').first();

            const readIds = idsFrom($r);
            const writeIds = idsFrom($w);
            const deleteIds = idsFrom($d);

            const hasDelete = anySelected(deleteIds);
            const hasWrite = anySelected(writeIds) || hasDelete;
            const hasRead = anySelected(readIds) || hasWrite;

            if ($r.length) $r.prop('checked', hasRead);
            if ($w.length) $w.prop('checked', hasWrite);
            if ($d.length) $d.prop('checked', hasDelete);
        }

        if ($matrix.length) {
            $form.find('tr[data-page]').each(function () {
                const $row = $(this);
                normalizeRow($row);
                syncRow($row);
            });

            $form.on('change', '.perm-level', function () {
                const $cb = $(this);
                const level = String($cb.data('level') || '').trim();
                const $row = $cb.closest('tr[data-page]');
                if (! $row.length) return;

                const readIds = idsFrom($row.find('.perm-level[data-level="read"]').first());
                const writeIds = idsFrom($row.find('.perm-level[data-level="write"]').first());
                const deleteIds = idsFrom($row.find('.perm-level[data-level="delete"]').first());

                const checked = $cb.is(':checked');

                if (level === 'read') {
                    setHidden(readIds, checked);
                    if (!checked) {
                        setHidden(writeIds, false);
                        setHidden(deleteIds, false);
                    }
                } else if (level === 'write') {
                    if (checked) {
                        setHidden(readIds, true);
                        setHidden(writeIds, true);
                    } else {
                        setHidden(writeIds, false);
                        setHidden(deleteIds, false);
                    }
                } else if (level === 'delete') {
                    if (checked) {
                        setHidden(readIds, true);
                        setHidden(writeIds, true);
                        setHidden(deleteIds, true);
                    } else {
                        setHidden(deleteIds, false);
                    }
                }

                syncRow($row);
            });
        }

        $('#btnSaveRolePerms').on('click', function () {
            const roleId = parseInt(opts.roleId || 0, 10);
            if (!roleId) return;
            postJson('roles/' + roleId + '/permissions', $('#rolePermsForm').serialize())
                .done(function (res) {
                    notify(res.message || 'Saved.', 'success');
                    window.setTimeout(function () {
                        window.location.href = base('role-permissions');
                    }, 500);
                })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.', 'danger'); });
        });
    };

    BMS.initRolePermissionsList = function () {
        const table = $('#dtRolePermissions').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('role-permissions/list'), dataSrc: 'data' },
            order: [[1, 'asc']],
            columns: [
                { data: null, orderable: false, className: 'text-center', render: function (d, t, r, meta) {
                    return (meta.row + meta.settings._iDisplayStart + 1);
                }},
                { data: 'name', className: 'text-center', render: function (d) { return d || '-'; } },
                { data: 'description', className: 'text-center', render: function (d) { return d || '-'; } },
                { data: 'permissions_count', className: 'text-center', render: function (d) {
                    const n = parseInt(d || 0, 10) || 0;
                    return '<span class="badge text-bg-secondary">' + n + '</span>';
                }},
                { data: null, orderable: false, className: 'text-center', render: function (row) {
                    const roleId = parseInt((row && row.id) || 0, 10);
                    if (!roleId) return '';
                    let html = actionLink('view', 'btn-outline-dark', 'View Permissions', base('roles/' + roleId + '/permissions?mode=view'));
                    if (!(parseInt((row && row.is_super) || 0, 10) === 1 || String((row && row.name) || '').trim().toLowerCase() === 'super admin')) {
                        html += actionLink('edit', 'btn-outline-primary', 'Edit Permissions', base('roles/' + roleId + '/permissions'));
                    }
                    return actionGroup(html);
                }},
            ],
        }));

        table.on('xhr.dt', function (e, settings, json) {
            if (json && json.success === false && json.message) {
                notify(json.message, 'danger');
            }
        });
    };

    BMS.initPermissions = function (opts) {
        opts = opts || {};
        const $modal = new bootstrap.Modal(document.getElementById('permModal'));
        const $saveBtn = $('#btnSavePerm');
        const $form = $('#permForm');

        function setFormMode(mode) {
            const isView = mode === 'view';
            $form.find('input,select,textarea').prop('disabled', isView);
            $('#permModal .btn-close').prop('disabled', false);
            $saveBtn.toggle(!isView);
        }

        function clearErrors() {
            $('#permForm .is-invalid').removeClass('is-invalid');
            $('#permForm [data-err]').text('');
        }

        function openPermission(row, mode) {
            clearErrors();
            row = row || {};
            $('#permModalTitle').text(mode === 'view' ? 'View Permission' : 'Edit Permission');
            $('#permForm').removeClass('was-validated');
            $('#perm_id').val(row.id || '');
            $('#perm_key').val(row.key || '');
            $('#perm_label').val(row.label || '');
            $('#perm_module').val(row.module || '');
            $('#perm_description').val(row.description || '');
            setFormMode(mode);
            $modal.show();
        }

        const table = $('#dtPermissions').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('permissions/list'), dataSrc: 'data' },
            order: [[1, 'asc']],
            columns: [
                { data: null, orderable: false, render: function (d, t, r, meta) {
                    return (meta.row + meta.settings._iDisplayStart + 1);
                }},
                { data: null, render: function (row) {
                    const key = String((row && row.key) || '').trim();
                    const labelName = String((row && row.label) || '').trim();
                    if (labelName) return labelName;

                    const moduleName = String((row && row.module) || '').trim();
                    if (moduleName) return moduleName;

                    // Fallback if module is missing (shouldn't happen with the API filter).
                    if (!key) return '-';
                    const cleaned = key.replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim();
                    if (!cleaned) return key;
                    return cleaned.replace(/\b\w/g, function (m) { return m.toUpperCase(); });
                }},
                { data: null, orderable: false, className: 'text-start', render: function () {
                    let html = actionBtn('view', 'btn-outline-dark', 'View');
                    if (opts.canEdit) html += actionBtn('edit', 'btn-outline-primary', 'Edit');
                    if (opts.canDelete) html += actionBtn('del', 'btn-outline-danger', 'Delete');
                    return actionGroup(html);
                }},
            ],
        }));

        if (opts.canCreate) {
            $('#btnAddPermission').on('click', function () {
                clearErrors();
                $('#permModalTitle').text('Add Permission');
                $('#permForm')[0].reset();
                $('#permForm').removeClass('was-validated');
                $('#perm_id').val('');
                setFormMode('edit');
                $modal.show();
            });
        }

        $('#dtPermissions tbody').on('click', 'button.btn-view', function () {
            const row = table.row($(this).closest('tr')).data();
            openPermission(row, 'view');
        });

        $('#dtPermissions tbody').on('click', 'button.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            openPermission(row, 'edit');
        });

        $('#dtPermissions tbody').on('click', 'button.btn-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            if (!confirm('Delete this permission?')) return;
            postJson('permissions/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#btnSavePerm').on('click', function () {
            clearErrors();
            const form = document.getElementById('permForm');
            form.classList.add('was-validated');
            if (!form.checkValidity()) {
                notify('Please fill the required fields.', 'danger');
                return;
            }

            postJson('permissions/save', $('#permForm').serialize())
                .done(function (res) {
                    notify(res.message || 'Saved.', 'success');
                    $modal.hide();
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    notify(res.message || 'Save failed.', 'danger');
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (k) {
                            const $field = $('#permForm [name="' + k + '"]');
                            $field.addClass('is-invalid');
                            $('#permForm [data-err="' + k + '"]').text(res.errors[k]);
                        });
                    }
                });
        });
    };

    BMS.initUsers = function (opts) {
        opts = opts || {};
        const $modal = new bootstrap.Modal(document.getElementById('userModal'));
        const $saveBtn = $('#btnSaveUser');
        const $form = $('#userForm');

        function syncPasswordToggles() {
            $('#userForm .toggle-password').each(function () {
                const $btn = $(this);
                const sel = String($btn.data('target') || '').trim();
                const $inp = sel ? $(sel) : $();
                const disabled = !($inp.length) || $inp.prop('disabled');
                $btn.prop('disabled', disabled);

                if ($inp.length) {
                    const isText = $inp.attr('type') === 'text';
                    $btn.attr('aria-pressed', isText ? 'true' : 'false');
                    $btn.attr('aria-label', isText ? 'Hide password' : 'Show password');
                    $btn.find('.pw-icon-eye').toggleClass('d-none', isText);
                    $btn.find('.pw-icon-eye-off').toggleClass('d-none', !isText);
                }
            });
        }

        function setFormMode(mode) {
            const isView = mode === 'view';
            $form.find('input,select,textarea').prop('disabled', isView);
            $('#userModal .btn-close').prop('disabled', false);
            $saveBtn.toggle(!isView);

            const isCreate = mode === 'create';
            const requirePw = isCreate;
            $('.user-pw-required').toggle(requirePw);
            $('.user-pw-hint').toggle(!isCreate && !isView);
            $('#user_password').prop('required', requirePw);
            $('#user_confirm_password').prop('required', requirePw);

            if (isCreate || isView) {
                $('#user_password').val('');
                $('#user_confirm_password').val('');
            }

            // Always reset visibility back to password type on mode change.
            $('#user_password').attr('type', 'password');
            $('#user_confirm_password').attr('type', 'password');
            syncPasswordToggles();
        }

        function clearErrors() {
            $('#userForm .is-invalid').removeClass('is-invalid');
            $('#userForm [data-err]').text('');
        }

        function fillForm(data) {
            data = data || {};
            $('#user_id').val(data.id || '');
            $('#user_name').val(data.name || '');
            $('#user_email').val(data.email || '');
            $('#user_mobile').val(data.mobile || '');
            $('#user_status').val(String((data.status === 0 || data.status === '0') ? 0 : 1));
            $('#user_role_id').val(String(data.role_id || ''));
            $('#user_password').val('');
            $('#user_confirm_password').val('');
            $('#user_password').attr('type', 'password');
            $('#user_confirm_password').attr('type', 'password');
            syncPasswordToggles();
        }

        $('#user_mobile').on('input', function () {
            const digitsOnly = String($(this).val() || '').replace(/\D/g, '').slice(0, 10);
            $(this).val(digitsOnly);
        });

        function roleBadge(roleName, isSuper) {
            const name = String(roleName || '').trim();
            if (!name) {
                return '<span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle">No Role</span>';
            }
            const sup = parseInt(isSuper || 0, 10) === 1 || name.toLowerCase() === 'super admin';
            if (sup) {
                return '<span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle">' + name + '</span>';
            }
            return '<span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle">' + name + '</span>';
        }

        function statusBadge(status) {
            const v = parseInt(status || 0, 10) === 1;
            return v
                ? '<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">Active</span>'
                : '<span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>';
        }

        const table = $('#dtUsers').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('users/list'), dataSrc: 'data' },
            order: [[1, 'asc']],
            columns: [
                { data: null, orderable: false, render: function (d, t, r, meta) {
                    return (meta.row + meta.settings._iDisplayStart + 1);
                }},
                { data: 'name', render: function (d) { return d || '-'; } },
                { data: 'email', render: function (d) { return d || '-'; } },
                { data: 'mobile', orderable: false, render: function (d) {
                    const v = String(d || '').trim();
                    return v ? v : 'N/A';
                }},
                { data: null, orderable: false, render: function (row) {
                    return roleBadge(row && row.role_name, row && row.role_is_super);
                }},
                { data: 'status', orderable: false, render: function (d) {
                    return statusBadge(d);
                }},
                { data: null, orderable: false, className: 'text-start', render: function (row) {
                    let html = actionBtn('view', 'btn-outline-dark', 'View');
                    if (opts.canEdit) html += actionBtn('edit', 'btn-outline-primary', 'Edit');
                    if (opts.canDelete) html += actionBtn('del', 'btn-outline-danger', 'Delete');
                    return actionGroup(html);
                }},
            ],
            columnDefs: [
                { targets: [1, 2], orderable: true },
            ],
        }));

        table.on('xhr.dt', function (e, settings, json) {
            if (json && json.success === false && json.message) {
                notify(json.message, 'danger');
            }
        });

        if (opts.canCreate) {
            $('#btnAddUser').on('click', function () {
                clearErrors();
                $('#userModalTitle').text('Add New User');
                $('#userForm')[0].reset();
                $('#userForm').removeClass('was-validated');
                fillForm({ id: '' });
                setFormMode('create');
                $modal.show();
            });
        }

        $('#userForm').on('click', '.toggle-password', function () {
            const $btn = $(this);
            const sel = String($btn.data('target') || '').trim();
            if (!sel) return;
            const $inp = $(sel);
            if (!$inp.length || $inp.prop('disabled')) return;

            const isText = $inp.attr('type') === 'text';
            $inp.attr('type', isText ? 'password' : 'text');
            syncPasswordToggles();
            try { $inp.trigger('focus'); } catch (e) {}
        });

        function loadAndOpen(id, mode) {
            clearErrors();
            getJson('users/' + id)
                .done(function (res) {
                    const data = (res && res.data) ? res.data : {};
                    fillForm(data);
                    $('#userModalTitle').text(mode === 'view' ? 'View User' : 'Edit User');
                    $('#userForm').removeClass('was-validated');
                    setFormMode(mode);
                    $modal.show();
                })
                .fail(function (xhr) {
                    notify((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to load user.', 'danger');
                });
        }

        $('#dtUsers tbody').on('click', 'button.btn-view', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            loadAndOpen(row.id, 'view');
        });

        $('#dtUsers tbody').on('click', 'button.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            loadAndOpen(row.id, 'edit');
        });

        $('#dtUsers tbody').on('click', 'button.btn-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!row || !row.id) return;
            if (!confirm('Delete this user?')) return;
            postJson('users/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#btnSaveUser').on('click', function () {
            clearErrors();
            const form = document.getElementById('userForm');
            form.classList.add('was-validated');
            if (!form.checkValidity()) {
                notify('Please fill the required fields.', 'danger');
                return;
            }

            postJson('users/save', $('#userForm').serialize())
                .done(function (res) {
                    if (res && res.success === false) {
                        notify(res.message || 'Save failed.', 'danger');
                        return;
                    }
                    notify((res && res.message) || 'Saved.', 'success');
                    $modal.hide();
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    notify(res.message || 'Save failed.', 'danger');
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (k) {
                            const $field = $('#userForm [name="' + k + '"]');
                            $field.addClass('is-invalid');
                            $('#userForm [data-err="' + k + '"]').text(res.errors[k]);
                        });
                    }
                });
        });
    };

    BMS.initAdminRolesEdit = function (opts) {
        opts = opts || {};
        const adminId = parseInt(opts.adminId || 0, 10);
        if (!adminId) return;

        $('#btnSaveAdminRoles').on('click', function () {
            postJson('admin-roles/' + adminId, $('#adminRolesForm').serialize())
                .done(function (res) { notify(res.message || 'Saved.', 'success'); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.', 'danger'); });
        });
    };

    BMS.initBillableItems = function () {
        const $modal = new bootstrap.Modal(document.getElementById('billableModal'));
        const viewModalEl = document.getElementById('billableViewModal');
        const $viewModal = viewModalEl ? new bootstrap.Modal(viewModalEl) : null;
        const hasTiny = typeof window.tinymce !== 'undefined' && document.getElementById('bi_description');
        const editorId = 'bi_description';
        let pendingDescriptionHtml = null;
        let isEnforcingBullets = false;
        let showDescError = false;
        const urlParams = new URLSearchParams(window.location.search);
        const urlEditId = parseInt(urlParams.get('edit') || '0', 10) || 0;
        const urlClientId = parseInt(urlParams.get('client_id') || '0', 10) || 0;
        const urlMonth = String(urlParams.get('month') || '').trim();
        const urlStatusRaw = String(urlParams.get('status') || '').trim().toLowerCase();
        const urlForceAll = urlStatusRaw === 'all';
        const urlForceBilled = urlStatusRaw === 'billed';
        let urlEditOpened = false;

        function getTinyEditor() {
            if (!hasTiny) return null;
            try {
                const ed = window.tinymce.get(editorId);
                if (ed) return ed;
                const active = window.tinymce.activeEditor;
                if (active && active.id === editorId) return active;
                return null;
            } catch (e) {
                return null;
            }
        }

        function escapeHtml(s) {
            return String(s || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function textFromHtml(html) {
            const tmp = document.createElement('div');
            tmp.innerHTML = String(html || '');
            return String(tmp.innerText || tmp.textContent || '')
                .replace(/\u00A0/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function bulletHtmlFromText(text) {
            const lines = String(text || '')
                .replace(/\u00A0/g, ' ')
                .split(/\r?\n/)
                .map(function (l) { return l.trim(); })
                .filter(function (l) { return l !== ''; });

            if (lines.length === 0) {
                return '<ul><li><br></li></ul>';
            }

            return '<ul>' + lines.map(function (l) { return '<li>' + escapeHtml(l) + '</li>'; }).join('') + '</ul>';
        }

        function enforceBulletOnly(editor) {
            if (!editor || isEnforcingBullets) return;
            isEnforcingBullets = true;
            try {
                const body = (typeof editor.getBody === 'function') ? editor.getBody() : null;
                const rawText = body ? (body.innerText || body.textContent || '') : (editor.getContent({ format: 'text' }) || '');
                let text = String(rawText).replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
                const html = (editor.getContent({ format: 'html' }) || '').trim();

                if (!text && html) {
                    text = textFromHtml(html);
                }

                if (!text) {
                    editor.setContent('<ul><li><br></li></ul>');
                    const li = editor.getBody().querySelector('li');
                    if (li) {
                        editor.selection.select(li, true);
                        editor.selection.collapse(false);
                    }
                    return;
                }

                setDescriptionValidity(true);

                // Force unordered list root
                if (!/^<ul[\s>]/i.test(html)) {
                    editor.setContent(bulletHtmlFromText(text));
                }
            } finally {
                isEnforcingBullets = false;
            }
        }

        function setDescriptionValidity(valid, msg) {
            const ed = getTinyEditor();
            const $fb = $('#bi_description_feedback');
            if ($fb.length) {
                if (msg) $fb.text(msg);
                $fb.toggle(!valid && showDescError);
            }

            if (ed && typeof ed.getContainer === 'function') {
                $(ed.getContainer()).toggleClass('is-invalid', !valid && showDescError);
            } else {
                $('#bi_description').toggleClass('is-invalid', !valid && showDescError);
            }
        }

        function getDescriptionText() {
            const ed = getTinyEditor();
            if (ed) {
                const body = (typeof ed.getBody === 'function') ? ed.getBody() : null;
                const raw = body ? (body.innerText || body.textContent || '') : (ed.getContent({ format: 'text' }) || '');
                const t = String(raw).replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
                if (t) return t;
                return textFromHtml(ed.getContent({ format: 'html' }) || '');
            }
            return String($('#bi_description').val() || '')
                .replace(/<[^>]*>/g, ' ')
                .replace(/\u00A0/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function getDescriptionHtml() {
            const ed = getTinyEditor();
            if (ed) {
                return (ed.getContent() || '').trim();
            }
            return String($('#bi_description').val() || '').trim();
        }

        function setDescriptionHtml(html) {
            const ed = getTinyEditor();
            if (ed) {
                ed.setContent(html || '');
            } else {
                $('#bi_description').val(html || '');
                pendingDescriptionHtml = html || '';
            }
        }

        if (hasTiny) {
            // Initialize TinyMCE once
            if (!getTinyEditor()) {
                // Avoid native HTML5 required validation on a textarea hidden by TinyMCE.
                $('#bi_description').prop('required', false);

                window.tinymce.init({
                    selector: '#' + editorId,
                    height: 150,
                    menubar: false,
                    branding: false,
                    statusbar: false,
                    plugins: 'lists',
                    toolbar: false,
                    contextmenu: false,
                    paste_as_text: true,
                    forced_root_block: false,
                    valid_elements: 'ul,li,br',
                    content_style:
                        'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;font-size:14px;}' +
                        'ul{margin:0;padding-left:1.2rem;}' +
                        'li{margin:0;}',
                    setup: function (editor) {
                        editor.on('init', function () {
                            if (pendingDescriptionHtml !== null) {
                                editor.setContent(pendingDescriptionHtml);
                                pendingDescriptionHtml = null;
                            }
                            enforceBulletOnly(editor);
                        });

                        // Prevent exiting bullet list (no paragraphs)
                        editor.on('keydown', function (e) {
                            if (e.key === 'Enter') {
                                const ul = editor.dom.getParent(editor.selection.getNode(), 'ul');
                                if (!ul) {
                                    e.preventDefault();
                                    const body = (typeof editor.getBody === 'function') ? editor.getBody() : null;
                                    const raw = body ? (body.innerText || body.textContent || '') : (editor.getContent({ format: 'text' }) || '');
                                    editor.setContent(bulletHtmlFromText(raw));
                                    return;
                                }

                                const li = editor.dom.getParent(editor.selection.getNode(), 'li');
                                if (li) {
                                    const liText = String(li.textContent || '').replace(/\u00A0/g, ' ').trim();
                                    // If empty list item, don't allow TinyMCE to end the list into a paragraph.
                                    if (!liText) {
                                        e.preventDefault();
                                        // create another empty bullet if user expects a new line
                                        editor.execCommand('mceInsertContent', false, '<li></li>');
                                        return;
                                    }
                                }
                            }
                        });

                        editor.on('paste', function () {
                            setTimeout(function () { enforceBulletOnly(editor); }, 0);
                        });

                        editor.on('keyup input change NodeChange SetContent', function () {
                            const ok = getDescriptionText() !== '';
                            setDescriptionValidity(ok);
                            enforceBulletOnly(editor);
                        });
                    },
                });
            }

            // Repaint editor when modal opens (prevents layout glitches in hidden modals)
            const billableModalEl = document.getElementById('billableModal');
            if (billableModalEl) billableModalEl.addEventListener('shown.bs.modal', function () {
                const ed = getTinyEditor();
                if (ed && typeof ed.execCommand === 'function') {
                    try { ed.execCommand('mceRepaint'); } catch (e) {}
                }
                if (ed && pendingDescriptionHtml !== null) {
                    ed.setContent(pendingDescriptionHtml);
                    pendingDescriptionHtml = null;
                }
            });
        }

        function clearBillableErrors() {
            const $form = $('#billableForm');
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('[data-err]').each(function () {
                const $el = $(this);
                const def = $el.data('default');
                if (def !== undefined) {
                    $el.text(def);
                }
            });
            setDescriptionValidity(true);
        }

        // Capture default invalid-feedback texts once (so we can restore them after server errors).
        $('#billableForm [data-err]').each(function () {
            const $el = $(this);
            if ($el.data('default') === undefined) {
                $el.data('default', $el.text());
            }
        });

        // (Rich text validation handled via TinyMCE helpers above)

        function fillClients($select, clients, includeAllOption, labelFn) {
            $select.empty();
            if (includeAllOption) {
                $select.append('<option value=\"\">All Clients</option>');
            } else {
                $select.append('<option value=\"\">Select Client</option>');
            }
            clients.forEach(function (c) {
                const custom = typeof labelFn === 'function' ? String(labelFn(c) || '').trim() : '';
                const label = custom || c.name || c.contact_person || c.email || c.phone || ('Client #' + c.id);
                $select.append('<option value=\"' + c.id + '\">' + label + '</option>');
            });
        }

        function loadClients() {
            return getJson('masters/client-master/list').then(function (res) {
                const clients = (res && res.data) ? res.data : [];
                fillClients($('#filterClient'), clients, true);
                fillClients($('#bi_client_id'), clients, false, function (c) {
                    const company = String((c && c.name) || '').trim();
                    const person = String((c && c.contact_person) || '').trim();
                    if (company && person) return company + ' - ' + person;
                    return company || person || '';
                });
                return clients;
            });
        }

        function updateAmountPreview() {
            const qty = parseFloat($('#bi_quantity').val()) || 0;
            const price = parseFloat($('#bi_unit_price').val()) || 0;
            $('#bi_amount_preview').val((qty * price).toFixed(2));
        }

        function sanitizeDecimalInput(value) {
            let clean = String(value || '').replace(/[^0-9.]/g, '');
            const firstDot = clean.indexOf('.');
            if (firstDot !== -1) {
                clean = clean.slice(0, firstDot + 1) + clean.slice(firstDot + 1).replace(/\./g, '');
            }
            return clean;
        }

        $('#bi_quantity,#bi_unit_price').on('input', updateAmountPreview);
        $('#bi_unit_price').on('input', function () {
            const clean = sanitizeDecimalInput($(this).val());
            if ($(this).val() !== clean) {
                $(this).val(clean);
            }
            updateAmountPreview();
        });

        // Default to showing Pending items.
        // Must be set before DataTable's initial AJAX call.
        BMS.initPremiumDatePickers();

        if (urlForceAll) {
            $('#filterStatus').val('');
        } else if (urlForceBilled) {
            $('#filterStatus').val('Billed');
        } else if (!$('#filterStatus').val()) {
            $('#filterStatus').val('Pending');
        }

        const table = $('#dtBillableItems').DataTable($.extend(true, {}, dtDefaults(), {
            responsive: false,
            scrollX: false,
            autoWidth: false,
            ajax: {
                url: base('billable-items/list'),
                dataSrc: 'data',
                data: function (d) {
                    d.client_id = $('#filterClient').val() || (urlClientId > 0 ? urlClientId : '');
                    d.status = $('#filterStatus').val();
                    d.month = $('#filterMonth').val() || urlMonth;
                    d.start_date = $('#filterStartDate').val();
                    d.end_date = $('#filterEndDate').val();
                }
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'entry_no', width: '11%', className: 'text-nowrap bms-billable-entry', render: function (d, t, row) {
                    if (t === 'sort' || t === 'type') return parseInt(row.id || '0', 10) || 0;
                    return d || ('BI-' + String(row.id).padStart(5, '0'));
                } },
                { data: 'entry_date', width: '11%', className: 'text-nowrap bms-billable-date', render: formatUiDate },
                { data: 'client_name', width: '13%', className: 'text-nowrap bms-billable-client' },
                { data: 'description', width: '18%', orderable: false, className: 'text-nowrap bms-billable-desc', render: function (d, t) {
                    return renderTruncatedDescription(d, t, 18);
                } },
                { data: 'billing_month', width: '12%', className: 'text-nowrap bms-billable-month', render: function (d) { return (String(d || '').trim() || '-'); } },
                { data: 'status', width: '10%', orderable: false, className: 'text-nowrap bms-billable-status', render: function (d, t, row) {
                    return billableStatusBadge(d);
                }},
                { data: 'amount', width: '10%', className: 'text-start text-nowrap bms-billable-amount' },
                { data: null, width: '15%', orderable: false, className: 'text-nowrap bms-billable-actions', render: function (row) {
                    const isPending = row.status === 'Pending';
                    let actionHtml =
                        actionBtn('view', 'btn-outline-dark', 'View') +
                        actionBtn('edit', 'btn-outline-primary', 'Edit') +
                        actionBtn('del', 'btn-outline-danger', 'Delete');

                    if (isPending) {
                        actionHtml += actionBtn('billed', '', 'Mark as Billed');
                    }

                    return '' +
                        '<div class="d-flex align-items-center gap-1 flex-nowrap">' +
                            actionGroup(actionHtml) +
                        '</div>';
                }},
            ],
        }));

        $('#filterClient,#filterStatus,#filterStartDate,#filterEndDate,#filterMonth').on('change', function () {
            table.ajax.reload();
        });

        $('.column-search').on('keyup change clear', function () {
            const index = $(this).data('index');
            table.column(index).search(this.value).draw();
        });

        $('#btnResetFilters').on('click', function () {
            $('#filterClient').val('');
            $('#filterStatus').val('');
            $('#filterMonth').val('');
            if ($('#filterMonth')[0]._flatpickr) $('#filterMonth')[0]._flatpickr.clear();
            $('#filterStartDate').val('');
            if ($('#filterStartDate')[0]._flatpickr) $('#filterStartDate')[0]._flatpickr.clear();
            $('#filterEndDate').val('');
            if ($('#filterEndDate')[0]._flatpickr) $('#filterEndDate')[0]._flatpickr.clear();
            table.ajax.reload();
        });

        $('#btnExportBillable').on('click', function () {
            const params = new URLSearchParams();
            const clientId = String($('#filterClient').val() || '');
            const status = String($('#filterStatus').val() || '');
            const start = $('#filterStartDate').val();
            const end = $('#filterEndDate').val();
            const month = $('#filterMonth').val() || urlMonth;
            if (clientId) params.set('client_id', clientId);
            if (status) params.set('status', status);
            if (start) params.set('start_date', start);
            if (end) params.set('end_date', end);
            if (month) params.set('month', month);
            const qs = params.toString();
            window.location.href = base('billable-items/download' + (qs ? '?' + qs : ''));
        });

        function openAdd() {
            showDescError = false;
            $('#billableModalTitle').text('Add Billable Item');
            $('#billableForm')[0].reset();
            $('#billableForm').removeClass('was-validated');
            $('#bi_id').val('');
            $('#bi_quantity').val('1');
            $('#bi_unit_price').val('0');
            $('#bi_currency').val('INR');
            (function () {
                const d = new Date();
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const v = (months[d.getMonth()] || 'Jan') + ' ' + d.getFullYear();
                $('#bi_billing_month').val(v);
            })();
            $('#bi_status').val('Pending');
            setDescriptionHtml('<ul><li><br></li></ul>');
            setDescriptionValidity(true);
            clearBillableErrors();
            updateAmountPreview();
            $modal.show();
        }

        function openEdit(row) {
            showDescError = false;
            $('#billableModalTitle').text('Edit Billable Item');
            $('#billableForm').removeClass('was-validated');
            $('#bi_id').val(row.id);
            $('#bi_client_id').val(row.client_id || '');
            setDescriptionHtml(bulletHtmlFromText(row.description || ''));
            setDescriptionValidity(true);
            $('#bi_quantity').val(row.quantity || '0');
            $('#bi_unit_price').val(row.unit_price || '0');
            $('#bi_currency').val(row.currency || 'INR');
            $('#bi_billing_month').val(row.billing_month || '');
            $('#bi_status').val(row.status || 'Pending');
            clearBillableErrors();
            updateAmountPreview();
            $modal.show();
        }

        function openView(row) {
            if (! $viewModal) return;
            row = row || {};

            const entryNo = row.entry_no || ('BI-' + String(row.id || '').padStart(5, '0'));
            $('#bi_view_entry_no').text(entryNo || '-');
            $('#bi_view_date').text(formatUiDate(row.entry_date, 'display'));
            $('#bi_view_client').text(String(row.client_name || '').trim() || '-');
            $('#bi_view_month').text(String(row.billing_month || '').trim() || '-');
            $('#bi_view_amount').text(String(row.amount || '').trim() || '-');
            $('#bi_view_status').text(String(row.status || '').trim() || '-');

            const desc = String(row.description || '').trim();
            if (!desc) {
                $('#bi_view_description').html('<div class="text-muted">-</div>');
            } else {
                const lines = desc.split(/\r?\n/)
                    .map(function (l) { return String(l || '').trim(); })
                    .filter(function (l) { return l !== ''; });
                const html = '<ul class="mb-0">' + lines.map(function (l) { return '<li>' + escapeHtml(l) + '</li>'; }).join('') + '</ul>';
                $('#bi_view_description').html(html);
            }

            $viewModal.show();
        }

        $('#btnAddBillable').on('click', openAdd);

        $('#btnSaveBillable').on('click', function () {
            clearBillableErrors();
            showDescError = true;
            const form = document.getElementById('billableForm');
            const edNow = getTinyEditor();
            if (edNow && typeof edNow.save === 'function') {
                try { edNow.save(); } catch (e) {}
            } else if (hasTiny && window.tinymce) {
                try { window.tinymce.triggerSave(); } catch (e) {}
            }
            const descOk = getDescriptionText() !== '';
            setDescriptionValidity(descOk);
            form.classList.add('was-validated');
            if (!form.checkValidity() || !descOk) {
                notify('Please fill the required fields.', 'danger');
                return;
            }

            const payloadArr = $('#billableForm').serializeArray();
            const ed = getTinyEditor();
            if (ed) enforceBulletOnly(ed);
            const descHtml = getDescriptionHtml() || '<ul><li><br></li></ul>';
            for (let i = payloadArr.length - 1; i >= 0; i--) {
                if (payloadArr[i] && payloadArr[i].name === 'description') {
                    payloadArr.splice(i, 1);
                }
            }
            payloadArr.push({ name: 'description', value: descHtml });

            postJson('billable-items/save', payloadArr)
                .done(function (res) {
                    notify(res.message || 'Saved.', 'success');
                    $('#billableForm').removeClass('was-validated');
                    showDescError = false;
                    setDescriptionValidity(true);
                    $modal.hide();
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    const msg = res.message || 'Save failed.';
                    if (res.errors && typeof res.errors === 'object') {
                        const firstKey = Object.keys(res.errors)[0];
                        const firstErr = firstKey ? res.errors[firstKey] : null;
                        notify(firstErr ? (msg + ' ' + firstErr) : msg, 'danger');
                    } else {
                        notify(msg, 'danger');
                    }
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (k) {
                            if (k === 'description') {
                                setDescriptionValidity(false, res.errors[k]);
                                return;
                            }
                            const $field = $('#billableForm [name="' + k + '"]');
                            if ($field.length) $field.addClass('is-invalid');
                            const $err = $('#billableForm [data-err="' + k + '"]');
                            if ($err.length) $err.text(res.errors[k]);
                        });
                    }
                });
        });

        $('#dtBillableItems tbody').on('click', 'button.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            openEdit(row);
        });

        $('#dtBillableItems tbody').on('click', 'button.btn-view', function () {
            const row = table.row($(this).closest('tr')).data();
            openView(row);
        });

        // Row click -> View
        $('#dtBillableItems tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('button,a,input,select,textarea,label').length) return;
            let $tr = $(this);
            if ($tr.hasClass('child')) {
                $tr = $tr.prev('.parent');
            }
            const row = table.row($tr).data();
            if (!row) return;
            openView(row);
        });

        $('#dtBillableItems tbody').on('click', 'button.btn-del', function () {
            const row = table.row($(this).closest('tr')).data();
            if (!confirm('Delete this billable item?')) return;
            postJson('billable-items/delete', { id: row.id })
                .done(function (res) { notify(res.message || 'Deleted.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger'); });
        });

        $('#dtBillableItems tbody').on('click', 'button.btn-billed', function () {
            const row = table.row($(this).closest('tr')).data();
            if (row.status !== 'Pending') return;
            postJson('billable-items/mark-billed', { id: row.id })
                .done(function (res) { notify(res.message || 'Updated.', 'success'); table.ajax.reload(null, false); })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.', 'danger'); });
        });

        loadClients().always(function () {
            // ensure default client filter exists
            if (!$('#filterClient').val()) $('#filterClient').val('');
            if (urlClientId > 0) {
                $('#filterClient').val(String(urlClientId));
                table.ajax.reload();
            }
            updateAmountPreview();
        });

        // If navigated from Dashboard "Edit", auto-open modal for that record.
        table.on('xhr', function () {
            if (!urlEditId || urlEditOpened) return;
            const rows = table.rows().data().toArray();
            const found = rows.find(function (r) { return parseInt(r.id, 10) === urlEditId; });
            if (found) {
                urlEditOpened = true;
                openEdit(found);
                try {
                    const u = new URL(window.location.href);
                    u.searchParams.delete('edit');
                    window.history.replaceState({}, document.title, u.toString());
                } catch (e) {}
            }
        });
    };

    BMS.initProformas = function () {
        const $tableEl = $('#dtProformas');
        if (! $tableEl.length) return;
        BMS.initPremiumDatePickers();
        const $statTotal = $('#pfStatTotal');
        const $statVisible = $('#pfStatVisible');
        const $statExport = $('#pfStatExport');
        const $statGst = $('#pfStatGst');
        const $statAmount = $('#pfStatAmount');

        function parseIsoDateValue(value) {
            const raw = String(value || '').trim().slice(0, 10);
            const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(raw);
            if (!m) return null;
            return new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
        }

        function renderIssueDate(value, type) {
            const raw = String(value || '').trim().slice(0, 10);
            const label = formatUiDate(value, 'display');
            if (type === 'sort' || type === 'type' || type === 'filter') return raw;
            return '<div class="bms-proforma-date-stack">' +
                '<span class="bms-proforma-date-main">' + escapeHtml(label) + '</span>' +
                '<span class="bms-proforma-date-meta">Issued</span>' +
            '</div>';
        }

        function renderDueDate(value, type) {
            const raw = String(value || '').trim().slice(0, 10);
            const label = formatUiDate(value, 'display');
            if (type === 'sort' || type === 'type' || type === 'filter') return raw;

            let statusText = 'Scheduled';
            let statusClass = 'is-scheduled';
            const dueDate = parseIsoDateValue(value);
            if (dueDate) {
                const today = new Date();
                const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                if (dueDate.getTime() < todayStart.getTime()) {
                    statusText = 'Overdue';
                    statusClass = 'is-overdue';
                } else if (dueDate.getTime() === todayStart.getTime()) {
                    statusText = 'Due Today';
                    statusClass = 'is-today';
                } else {
                    statusText = 'Upcoming';
                    statusClass = 'is-upcoming';
                }
            }

            return '<div class="bms-proforma-date-stack">' +
                '<span class="bms-proforma-date-main">' + escapeHtml(label) + '</span>' +
                '<span class="bms-proforma-date-meta ' + statusClass + '">' + statusText + '</span>' +
            '</div>';
        }

        function renderNameStack(value, type, hint) {
            const text = descriptionPlainText(value);
            if (type === 'sort' || type === 'type' || type === 'filter') return text || '-';
            const display = renderTruncatedDescription(text || '-', 'display', hint === 'Customer' ? 16 : 18);
            return '<div class="bms-proforma-name-stack">' +
                display +
                '<span class="bms-proforma-name-hint">' + escapeHtml(hint) + '</span>' +
            '</div>';
        }

        function updateProformaSummary() {
            const allRows = table.rows().data().toArray();
            const visibleCount = table.rows({ search: 'applied' }).count();
            let exportCount = 0;
            let gstCount = 0;
            let totalAmount = 0;

            allRows.forEach(function (row) {
                const invoiceType = String((row && row.invoice_type) || 'GST Invoice').toLowerCase();
                if (invoiceType.indexOf('export') !== -1) exportCount += 1;
                else if (invoiceType.indexOf('gst') !== -1) gstCount += 1;
                totalAmount += parseFloat(String((row && (row.net_amount != null && row.net_amount !== '')) ? row.net_amount : (row && row.total_amount) || '0').replace(/,/g, '')) || 0;
            });

            $statTotal.text(String(allRows.length));
            $statVisible.text(String(visibleCount) + ' in current view');
            $statExport.text(String(exportCount));
            $statGst.text(String(gstCount));
            $statAmount.text(formatMoneyValue(totalAmount));
        }

        const table = $tableEl.DataTable($.extend(true, {}, dtDefaults(), {
            responsive: false,
            scrollX: false,
            autoWidth: false,
            ajax: {
                url: base('proforma/list'),
                dataSrc: 'data',
                data: function (d) {
                    d.start_date = $('#pfFilterStartDate').val();
                    d.end_date = $('#pfFilterEndDate').val();
                }
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id', width: '5%', className: 'text-nowrap bms-proforma-sno', render: function (d, t, _r, meta) {
                    if (t === 'sort' || t === 'type') return parseInt(d || '0', 10) || 0;
                    return (meta.row + meta.settings._iDisplayStart + 1);
                }},
                { data: 'proforma_number', width: '11%', className: 'text-nowrap bms-proforma-number', render: function (d, t) {
                    const value = String(d || '-').trim() || '-';
                    if (t === 'sort' || t === 'type' || t === 'filter') return value;
                    return '<span class="bms-proforma-code">' + escapeHtml(value) + '</span>';
                }},
                { data: 'invoice_type', width: '12%', className: 'text-nowrap bms-proforma-type', orderable: false, render: function (d, t) {
                    const value = String(d || 'GST Invoice').trim() || 'GST Invoice';
                    if (t === 'sort' || t === 'type' || t === 'filter') return value;
                    let tone = 'is-default';
                    const key = value.toLowerCase();
                    if (key.indexOf('export') !== -1) tone = 'is-export';
                    else if (key.indexOf('gst') !== -1) tone = 'is-gst';
                    return '<span class="bms-proforma-pill ' + tone + '">' + escapeHtml(value) + '</span>';
                } },
                { data: 'proforma_date', width: '10%', className: 'text-nowrap bms-proforma-date', render: renderIssueDate },
                { data: 'billing_to', width: '10%', className: 'text-nowrap bms-proforma-due', render: renderDueDate },
                { data: null, width: '14%', className: 'bms-proforma-customer', render: function (_d, t, row) {
                    const value = (row && (row.customer_name || row.contact_person || row.client_name)) ? (row.customer_name || row.contact_person || row.client_name) : '-';
                    return renderNameStack(value, t, 'Customer');
                } },
                { data: null, width: '16%', className: 'bms-proforma-company', render: function (_d, t, row) {
                    const value = (row && (row.company_name || row.client_name)) ? (row.company_name || row.client_name) : '-';
                    return renderNameStack(value, t, 'Company');
                } },
                { data: null, width: '10%', className: 'text-nowrap bms-proforma-amount', render: function (_d, t, row) {
                    const raw = (row && row.net_amount != null && row.net_amount !== '') ? row.net_amount : (row.total_amount || '0.00');
                    const amount = parseFloat(String(raw || '0').replace(/,/g, '')) || 0;
                    if (t === 'sort' || t === 'type') return amount;
                    if (t === 'filter') return String(raw || '0.00');
                    return '<span class="bms-proforma-amount-value">' + formatMoneyValue(amount) + '</span>';
                } },
                { data: null, width: '12%', orderable: false, className: 'text-nowrap bms-proforma-actions', render: function (row) {
                    if (!row || !row.id) return '';
                    return actionGroup(
                        actionLink('view', 'btn-outline-dark', 'View', base('proforma/show/' + row.id)) +
                        actionLink('edit', 'btn-outline-primary', 'Edit', base('proforma/edit/' + row.id)) +
                        actionBtn('del', 'btn-outline-danger pf-btn-del', 'Delete').replace('type="button"', 'type="button" data-id="' + row.id + '"') +
                        actionBtn('print', 'btn-outline-dark pf-btn-print', 'Print').replace('type="button"', 'type="button" data-id="' + row.id + '"')
                    );
                }},
            ],
            columnDefs: [
                { targets: [0, 8], searchable: false },
            ],
        }));

        $('#pfFilterStartDate, #pfFilterEndDate').on('change', function () {
            table.ajax.reload();
        });

        $('#pfBtnReset').on('click', function () {
            $('#pfFilterStartDate').val('');
            if ($('#pfFilterStartDate')[0]._flatpickr) $('#pfFilterStartDate')[0]._flatpickr.clear();
            $('#pfFilterEndDate').val('');
            if ($('#pfFilterEndDate')[0]._flatpickr) $('#pfFilterEndDate')[0]._flatpickr.clear();
            table.ajax.reload();
        });

        table.on('draw.dt', updateProformaSummary);

        function clearFilters() {
            $tableEl.find('thead input.pf-col-filter').val('');
            $('#pf_issue_native').val('');
            $('#pf_due_native').val('');
            table.search('');
            table.columns().search('');
            table.draw();
        }

        // Column filters (second header row)
        $tableEl.find('thead').on('input change', 'input.pf-col-filter', function () {
            const col = parseInt($(this).data('col') || '0', 10) || 0;
            const raw = String($(this).val() || '');
            const iso = $(this).hasClass('pf-col-filter-dmy') ? textToIso(raw) : '';
            table.column(col).search(iso || raw).draw();
        });

        // Premium Flatpickr for issue/due filters in column header if needed
        $tableEl.find('thead input.pf-col-filter[data-col=\"3\"], thead input.pf-col-filter[data-col=\"4\"]').each(function() {
            const colIndex = parseInt($(this).data('col'), 10);
            flatpickr(this, {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    table.column(colIndex).search(dateStr || '').draw();
                }
            });
        });

        // Clear button resets filters
        $('#pfBtnClear').on('click', function () {
            clearFilters();
        });

        $('#pfBtnExport').on('click', function () {
            const start = $('#pfFilterStartDate').val();
            const end = $('#pfFilterEndDate').val();
            let url = base('proforma/download');
            const params = [];
            if (start) params.push('start_date=' + encodeURIComponent(start));
            if (end) params.push('end_date=' + encodeURIComponent(end));
            if (params.length) url += '?' + params.join('&');
            window.location.href = url;
        });

        $tableEl.on('click', 'tbody .pf-btn-print', function () {
            const id = parseInt($(this).data('id') || '0', 10) || 0;
            if (!id) return;
            window.open(base('proforma/print/' + id + '?autoprint=1'), '_blank');
        });

        $tableEl.on('click', 'tbody .pf-btn-del', function () {
            const id = parseInt($(this).data('id') || '0', 10) || 0;
            if (!id) return;
            if (!confirm('Delete this invoice?')) return;
            postJson('proforma/delete', { id: id })
                .done(function (res) {
                    notify(res.message || 'Deleted.', 'success');
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger');
                });
        });
    };

    BMS.initPayments = function () {
        const $tableEl = $('#dtPayments');
        if (! $tableEl.length) return;
        BMS.initPremiumDatePickers();

        let table = null;
        let pendingInvoiceId = 0;
        try {
            table = $tableEl.DataTable($.extend(true, {}, dtDefaults(), {
                ajax: {
                    url: base('payments/list'),
                    dataSrc: 'data',
                    data: function (d) {
                        d.start_date = $('#payFilterStartDate').val();
                        d.end_date = $('#payFilterEndDate').val();
                    }
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', render: function (d, t, _r, meta) {
                        if (t === 'sort' || t === 'type') return parseInt(d || '0', 10) || 0;
                        return (meta.row + meta.settings._iDisplayStart + 1);
                    }},
                    { data: 'proforma_number', defaultContent: '-' },
                    { data: null, render: function (_d, _t, row) { return (row && (row.customer_name || row.company_name)) ? (row.customer_name || row.company_name) : '-'; } },
                    { data: 'total_paid', className: 'text-start', defaultContent: '0.00' },
                    { data: 'remaining_balance', className: 'text-start', defaultContent: '0.00' },
                    { data: 'payment_status', defaultContent: '-' },
                    { data: null, orderable: false, className: 'text-nowrap', render: function (row) {
                        if (!row || !row.id) return '';
                        return actionGroup(
                            actionLink('view', 'btn-outline-dark', 'View', base('payments/view/' + row.id)) +
                            actionLink('edit', 'btn-outline-primary', 'Edit', base('payments?add=1&invoice_id=' + row.id))
                        );
                    }},
                ],
                columnDefs: [
                    { targets: [0, 6], searchable: false },
                ],
            }));
        } catch (e) {
            notify('Payments table failed to initialize. Please refresh the page.', 'danger');
        }

        $('#payFilterStartDate, #payFilterEndDate').on('change', function () {
            table.ajax.reload();
        });

        $('#payBtnReset').on('click', function () {
            $('#payFilterStartDate').val('');
            if ($('#payFilterStartDate')[0]._flatpickr) $('#payFilterStartDate')[0]._flatpickr.clear();
            $('#payFilterEndDate').val('');
            if ($('#payFilterEndDate')[0]._flatpickr) $('#payFilterEndDate')[0]._flatpickr.clear();
            table.ajax.reload();
        });

        function showListPanel() {
            $('#payAddPanel').addClass('d-none');
            $('#payListPanel').removeClass('d-none');
            $('#payBtnAdd').prop('disabled', false);
        }

        function showAddPanel() {
            $('#payListPanel').addClass('d-none');
            $('#payAddPanel').removeClass('d-none');
            $('#payBtnAdd').prop('disabled', true);
        }

        function clearFilters() {
            if (!table) return;
            $tableEl.find('thead input.pay-col-filter').val('');
            table.search('');
            table.columns().search('');
            table.draw();
        }

        let customersLoaded = false;
        let customersCache = [];

        function loadCustomers() {
            if (customersLoaded) return $.Deferred().resolve(customersCache).promise();
            return getJson('payments/customers')
                .done(function (res) {
                    customersCache = (res && res.data) ? res.data : [];
                    customersLoaded = true;
                })
                .fail(function () {
                    notify('Unable to load customers.', 'danger');
                });
        }

        function loadInvoicesByCustomer(clientId) {
            return getJson('payments/invoices-by-customer/' + clientId)
                .fail(function () {
                    notify('Unable to load invoices for this customer.', 'danger');
                });
        }

        function setSelectState($sel, stateText, disabled) {
            if (!($sel && $sel.length)) return;
            $sel.empty();
            $sel.append('<option value=\"\">' + String(stateText || 'Select') + '</option>');
            $sel.prop('disabled', !!disabled);
        }

        function clearSummary() {
            // Add form currently matches reference screenshot (no summary section).
        }

        function renderCustomerOptions(selectedClientId) {
            const $sel = $('#payCustomer');
            if (! $sel.length) return;
            $sel.empty();
            $sel.append('<option value=\"\">-- Select Customer --</option>');
            (customersCache || []).forEach(function (c) {
                const cid = parseInt(c.client_id || c.id || '0', 10) || 0;
                const label = String(c.label || c.contact_person || c.name || ('Customer #' + cid));
                const opt = $('<option></option>').attr('value', String(cid)).text(label);
                if (selectedClientId && cid === selectedClientId) opt.attr('selected', 'selected');
                $sel.append(opt);
            });
        }

        function renderInvoiceOptionsForClient(clientId, selectedInvoiceId) {
            const $sel = $('#payInvoice');
            if (! $sel.length) return;

            if (!clientId) {
                setSelectState($sel, '-- Select Customer First --', true);
                return;
            }

            $sel.empty();
            $sel.append('<option value=\"\">-- Select Invoice --</option>');
            // Options are populated via API call in customer change handler.
            $sel.prop('disabled', false);
        }

        function openAddFlow(selectedInvoiceId) {
            clearSummary();
            $('#payAmount').val('');
            $('#payMode').val('');
            $('#payRemarks').val('');

            setSelectState($('#payCustomer'), 'Loading...', true);
            setSelectState($('#payInvoice'), '-- Select Customer First --', true);
            showAddPanel();
            pendingInvoiceId = parseInt(selectedInvoiceId || '0', 10) || 0;

            loadCustomers()
                .done(function () {
                    if (!customersCache || !customersCache.length) {
                        notify('No customers found. Create an invoice first.', 'info');
                    }
                    renderCustomerOptions(0);
                    $('#payCustomer').prop('disabled', false);
                    setSelectState($('#payInvoice'), '-- Select Customer First --', true);

                    if (pendingInvoiceId) {
                        getJson('payments/invoice/' + pendingInvoiceId)
                            .done(function (res) {
                                const inv = res && res.invoice ? res.invoice : null;
                                const cid = inv ? (parseInt(inv.client_id || '0', 10) || 0) : 0;
                                if (cid) {
                                    $('#payCustomer').val(String(cid)).trigger('change');
                                }
                            });
                    }
                })
                .fail(function () {
                    setSelectState($('#payCustomer'), 'Unable to load customers', true);
                    setSelectState($('#payInvoice'), 'Unable to load invoices', true);
                });
        }

        $(document).off('click.pay', '#payBtnAdd').on('click.pay', '#payBtnAdd', function (e) {
            // If JS is working, stay on the same page; if not, the link navigates to ?add=1 as fallback.
            e.preventDefault();
            openAddFlow(0);
        });

        $(document).off('change.pay', '#payCustomer').on('change.pay', '#payCustomer', function () {
            const clientId = parseInt($(this).val() || '0', 10) || 0;
            clearSummary();
            if (!clientId) {
                setSelectState($('#payInvoice'), '-- Select Customer First --', true);
                $('#payInvHistory').addClass('d-none');
                $('#payAmountMax').text('');
                return;
            }

            setSelectState($('#payInvoice'), 'Loading...', true);
            loadInvoicesByCustomer(clientId)
                .done(function (res) {
                    const rows = (res && res.data) ? res.data : [];
                    const $sel = $('#payInvoice');
                    $sel.empty();
                    $sel.append('<option value=\"\">-- Select Invoice --</option>');
                    rows.forEach(function (r) {
                        const id = parseInt(r.id || '0', 10) || 0;
                        const invNo = r.proforma_number || ('Invoice #' + id);
                        const bal = (r.remaining_balance != null) ? String(r.remaining_balance) : '';
                        const label = invNo + (bal ? (' (Balance: ' + bal + ')') : '');
                        $sel.append($('<option></option>').attr('value', String(id)).text(label));
                    });
                    $sel.prop('disabled', false);
                    $('#payInvHistory').addClass('d-none');
                    $('#payAmountMax').text('');

                    if (pendingInvoiceId) {
                        const pid = pendingInvoiceId;
                        pendingInvoiceId = 0;
                        $sel.val(String(pid)).trigger('change');
                    }
                })
                .fail(function () {
                    setSelectState($('#payInvoice'), 'Unable to load invoices', true);
                    $('#payInvHistory').addClass('d-none');
                    $('#payAmountMax').text('');
                });
        });

        function renderHistory(inv) {
            if (!inv) {
                $('#payInvHistory').addClass('d-none');
                $('#payAmountMax').text('');
                return;
            }

            $('#payHistInvDate').text(inv.invoice_date ? formatUiDate(inv.invoice_date, 'display') : '-');
            $('#payHistTotal').text(inv.invoice_total || '0.00');
            $('#payHistPaid').text(inv.total_paid || '0.00');
            $('#payHistBal').text(inv.remaining || '0.00');
            $('#payInvHistory').removeClass('d-none');

            const max = parseFloat(String(inv.remaining || '').replace(/,/g, '')) || 0;
            if (max > 0) {
                $('#payAmount').attr('max', String(max.toFixed(2)));
                $('#payAmountMax').text('Maximum: ' + max.toFixed(2));
            } else {
                $('#payAmount').removeAttr('max');
                $('#payAmountMax').text('');
            }
        }

        function openTxnHistory(invoiceId) {
            const modalEl = document.getElementById('payViewModal');
            if (!invoiceId || !modalEl || typeof bootstrap === 'undefined') return;

            getJson('payments/invoice/' + invoiceId)
                .done(function (res) {
                    const inv = res && res.invoice ? res.invoice : null;
                    const rows = res && res.payments ? res.payments : [];
                    $('#payViewInvoiceNo').text('Invoice: ' + (inv ? (inv.invoice_no || '-') : '-'));
                    $('#payViewCustomer').text('Customer: ' + (inv ? (inv.customer_name || '-') : '-'));
                    $('#payViewTotal').text(inv ? inv.invoice_total : '0.00');
                    $('#payViewPaid').text(inv ? inv.total_paid : '0.00');
                    $('#payViewRemaining').text(inv ? inv.remaining : '0.00');

                    const $body = $('#payViewBody');
                    $body.empty();
                    if (!rows.length) {
                        $body.append('<tr><td colspan=\"6\" class=\"text-center text-muted\">No payments.</td></tr>');
                    } else {
                        rows.forEach(function (p, idx) {
                            const dt = p.payment_date || '-';
                            const md = p.payment_mode || '-';
                            const rf = p.reference_number || '-';
                            const rm = p.remarks || '-';
                            const amt = p.amount != null ? String(p.amount) : '0.00';
                            $body.append(
                                '<tr>' +
                                '<td>' + (idx + 1) + '</td>' +
                                '<td>' + dt + '</td>' +
                                '<td>' + md + '</td>' +
                                '<td>' + rf + '</td>' +
                                '<td>' + rm + '</td>' +
                                '<td class=\"text-end\">' + amt + '</td>' +
                                '</tr>'
                            );
                        });
                    }

                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                })
                .fail(function (xhr) {
                    notify((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to load payment details.', 'danger');
                });
        }

        $(document).off('change.pay', '#payInvoice').on('change.pay', '#payInvoice', function () {
            const invoiceId = parseInt($(this).val() || '0', 10) || 0;
            if (!invoiceId) {
                renderHistory(null);
                return;
            }

            getJson('payments/invoice/' + invoiceId)
                .done(function (res) {
                    renderHistory(res && res.invoice ? res.invoice : null);
                })
                .fail(function () {
                    renderHistory(null);
                    notify('Unable to load invoice history.', 'danger');
                });
        });

        $(document).off('click.pay', '#payTxnBtn').on('click.pay', '#payTxnBtn', function () {
            const invoiceId = parseInt($('#payInvoice').val() || '0', 10) || 0;
            openTxnHistory(invoiceId);
        });

        $(document).off('click.pay', '#paySaveBtn').on('click.pay', '#paySaveBtn', function () {
            const proformaId = parseInt($('#payInvoice').val() || '0', 10) || 0;
            const paymentDate = String($('#payDate').val() || '').trim();
            const amount = parseFloat(String($('#payAmount').val() || '').trim());
            const mode = String($('#payMode').val() || '').trim();
            const remarks = String($('#payRemarks').val() || '').trim();

            if (!proformaId) { notify('Invoice is required.', 'danger'); return; }
            if (!paymentDate) { notify('Payment date is required.', 'danger'); return; }
            if (!(amount > 0)) { notify('Amount must be greater than 0.', 'danger'); return; }
            if (!mode) { notify('Payment method is required.', 'danger'); return; }

            $('#paySaveBtn').prop('disabled', true);
            postJson('payments/save', {
                proforma_id: proformaId,
                payment_date: paymentDate,
                amount: amount.toFixed(2),
                payment_mode: mode,
                reference_number: '',
                remarks: remarks
            }).done(function (res) {
                notify((res && res.message) ? res.message : 'Saved.', 'success');
                window.location.href = base('payments/view/' + proformaId);
            }).fail(function (xhr) {
                notify((xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.', 'danger');
            }).always(function () {
                $('#paySaveBtn').prop('disabled', false);
            });
        });

        $(document).off('click.pay', '#payAddBackBtn').on('click.pay', '#payAddBackBtn', function (e) {
            e.preventDefault();
            showListPanel();
            if (table) table.ajax.reload(null, false);
        });

        // If server rendered the Add panel (payments?add=1), hydrate it on load.
        if (!$('#payAddPanel').hasClass('d-none')) {
            let invId = 0;
            try {
                invId = parseInt((new URLSearchParams(window.location.search || '')).get('invoice_id') || '0', 10) || 0;
            } catch (e) {
                invId = 0;
            }
            openAddFlow(invId);
        }
    };

    BMS.initPaymentReport = function () {
        const $tableEl = $('#dtPaymentReport');
        if (! $tableEl.length) return;

        BMS.initPremiumDatePickers();

        const table = $tableEl.DataTable($.extend(true, {}, dtDefaults(), {
            ajax: {
                url: base('payment-report/list'),
                dataSrc: 'data',
                data: function (d) {
                    d.payment_status = $('#prPaymentStatus').val();
                    d.start_date = $('#prFilterStartDate').val();
                    d.end_date = $('#prFilterEndDate').val();
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: null, orderable: false, render: function (_d, _t, _r, meta) {
                    return (meta.row + meta.settings._iDisplayStart + 1);
                }},
                { data: 'invoice', defaultContent: '-' },
                { data: 'customer_name', defaultContent: '-' },
                { data: 'total_amount', className: 'text-end', defaultContent: '0.00' },
                { data: 'due_date', render: formatUiDateDmy },
                { data: 'total_paid', className: 'text-end', defaultContent: '0.00' },
                { data: 'remaining_balance', className: 'text-end', defaultContent: '0.00' },
                { data: 'payment_status', defaultContent: '-' },
            ],
        }));

        $(document).off('change.pr', '#prPaymentStatus, #prFilterStartDate, #prFilterEndDate').on('change.pr', '#prPaymentStatus, #prFilterStartDate, #prFilterEndDate', function () {
            table.ajax.reload();
        });

        $(document).off('click.pr', '#prBtnReset').on('click.pr', '#prBtnReset', function () {
            $('#prPaymentStatus').val('All');
            $('#prFilterStartDate').val('');
            if ($('#prFilterStartDate')[0]._flatpickr) $('#prFilterStartDate')[0]._flatpickr.clear();
            $('#prFilterEndDate').val('');
            if ($('#prFilterEndDate')[0]._flatpickr) $('#prFilterEndDate')[0]._flatpickr.clear();
            table.ajax.reload();
        });

        $(document).off('click.pr', '#prBtnDownload').on('click.pr', '#prBtnDownload', function () {
            const st = String($('#prPaymentStatus').val() || 'All');
            const start = $('#prFilterStartDate').val();
            const end = $('#prFilterEndDate').val();
            let url = base('payment-report/download?payment_status=' + encodeURIComponent(st));
            if (start) url += '&start_date=' + encodeURIComponent(start);
            if (end) url += '&end_date=' + encodeURIComponent(end);
            window.location.href = url;
        });
    };

    BMS.initDailyExpenseForm = function () {
        const $tableEl = $('#dtDailyExpenses');
        if (! $tableEl.length) return;

        BMS.initPremiumDatePickers();

        const table = $tableEl.DataTable($.extend(true, {}, dtDefaults(), {
            ajax: { url: base('day-book/daily-expense-form/list'), dataSrc: 'data' },
            order: [[2, 'desc']],
            columns: [
                { data: null, orderable: false, render: function (_d, _t, _r, meta) {
                    return (meta.row + meta.settings._iDisplayStart + 1);
                }},
                { data: 'expense_code', defaultContent: '-' },
                { data: 'expense_date', render: formatUiDateDmy },
                { data: 'category', defaultContent: '-' },
                { data: 'description', defaultContent: '-', render: function (d, t) {
                    return renderTruncatedDescription(d, t, 20);
                }},
                { data: 'amount', className: 'text-end', defaultContent: '0.00' },
                { data: 'payment_method', defaultContent: '-' },
                { data: 'paid_to', defaultContent: '-' },
                { data: null, orderable: false, render: function (_d, _t, row) {
                    const id = row && row.id ? row.id : 0;
                    return actionGroup(
                        actionBtn('view', 'btn-outline-dark de-btn-view', 'View').replace('type="button"', 'type="button" data-id="' + id + '"') +
                        actionLink('edit', 'btn-outline-primary', 'Edit', base('day-book/daily-expense-form/edit/' + id)) +
                        actionBtn('del', 'btn-outline-danger de-btn-del', 'Delete').replace('type="button"', 'type="button" data-id="' + id + '"')
                    );
                }},
            ],
        }));

        function openModal(mode, row) {
            const modalEl = document.getElementById('deModal');
            if (!modalEl || typeof bootstrap === 'undefined') return;

            $('#deModalLabel').text(mode === 'edit' ? 'Edit Daily Expense Entry' : 'New Daily Expense Entry');
            $('#deId').val(row && row.id ? row.id : '');
            $('#deDate').val((row && row.expense_date) ? String(row.expense_date).slice(0, 10) : '');
            $('#deCategory').val((row && row.category) ? row.category : '');
            $('#deDesc').val((row && row.description) ? row.description : '');
            $('#deAmount').val((row && row.amount != null) ? row.amount : '');
            $('#deMethod').val((row && row.payment_method) ? row.payment_method : '');
            $('#dePaidTo').val((row && row.paid_to) ? row.paid_to : '');

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        function findRowById(id) {
            const data = table.rows().data().toArray();
            return data.find(function (r) { return parseInt(r.id || '0', 10) === id; }) || null;
        }

        // New entry is handled by server-rendered create page.

        $(document).off('click.de', '#deBtnSearch').on('click.de', '#deBtnSearch', function () {
            const q = String($('#deSearch').val() || '').trim();
            table.search(q).draw();
        });
        $(document).off('keydown.de', '#deSearch').on('keydown.de', '#deSearch', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $('#deBtnSearch').trigger('click');
            }
        });

        $(document).off('click.de', '#deBtnSave').on('click.de', '#deBtnSave', function () {
            const id = parseInt($('#deId').val() || '0', 10) || 0;
            const expenseDate = String($('#deDate').val() || '').trim();
            const category = String($('#deCategory').val() || '').trim();
            const description = String($('#deDesc').val() || '').trim();
            const amount = parseFloat(String($('#deAmount').val() || '').trim());
            const method = String($('#deMethod').val() || '').trim();
            const paidTo = String($('#dePaidTo').val() || '').trim();

            if (!expenseDate) { notify('Date is required.', 'danger'); return; }
            if (!(amount > 0)) { notify('Amount must be greater than 0.', 'danger'); return; }
            if (!method) { notify('Payment method is required.', 'danger'); return; }

            $('#deBtnSave').prop('disabled', true);
            postJson('day-book/daily-expense-form/save', {
                id: id ? id : '',
                expense_date: expenseDate,
                category: category,
                description: description,
                amount: amount.toFixed(2),
                payment_method: method,
                paid_to: paidTo
            }).done(function (res) {
                notify((res && res.message) ? res.message : 'Saved.', 'success');
                table.ajax.reload(null, false);
                const modalEl = document.getElementById('deModal');
                if (modalEl && typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }).fail(function (xhr) {
                notify((xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.', 'danger');
            }).always(function () {
                $('#deBtnSave').prop('disabled', false);
            });
        });

        $(document).off('click.de', '.de-btn-view').on('click.de', '.de-btn-view', function () {
            const id = parseInt($(this).data('id') || '0', 10) || 0;
            const row = findRowById(id);
            const modalEl = document.getElementById('deViewModal');
            if (!modalEl || typeof bootstrap === 'undefined') return;

            $('#deVCode').text(row && row.expense_code ? row.expense_code : '-');
            $('#deVDate').text(row && row.expense_date ? formatUiDateDmy(row.expense_date, 'display') : '-');
            $('#deVCat').text(row && row.category ? row.category : '-');
            $('#deVDesc').text(row && row.description ? row.description : '-');
            $('#deVAmt').text(row && row.amount != null ? String(row.amount) : '0.00');
            $('#deVMethod').text(row && row.payment_method ? row.payment_method : '-');
            $('#deVPaidTo').text(row && row.paid_to ? row.paid_to : '-');

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        // Edit action goes to server-rendered edit page.

        $(document).off('click.de', '.de-btn-del').on('click.de', '.de-btn-del', function () {
            const id = parseInt($(this).data('id') || '0', 10) || 0;
            if (!id) return;
            if (!confirm('Delete this expense?')) return;
            postJson('day-book/daily-expense-form/delete', { id: id })
                .done(function (res) {
                    notify((res && res.message) ? res.message : 'Deleted.', 'success');
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    notify((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.', 'danger');
                });
        });
    };

    BMS.initDailyExpenseReport = function () {
        const $cat = $('#derCategory');
        if (! $cat.length) return;

        flatpickr('#derStart', {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: true,
            onChange: function(selectedDates, dateStr) {
                $('#derStartNative').val(dateStr);
            }
        });
        flatpickr('#derEnd', {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: true,
            onChange: function(selectedDates, dateStr) {
                $('#derEndNative').val(dateStr);
            }
        });

        function currentFilters() {
            return {
                start_date: String($('#derStartNative').val() || '').trim(),
                end_date: String($('#derEndNative').val() || '').trim(),
                category: String($('#derCategory').val() || 'All').trim(),
            };
        }

        function loadCategories() {
            return getJson('day-book/daily-expense-report/categories')
                .done(function (res) {
                    const cats = (res && res.data) ? res.data : [];
                    $cat.find('option').not(':first').remove();
                    cats.forEach(function (c) {
                        $cat.append($('<option></option>').attr('value', c).text(c));
                    });
                });
        }

        function renderReport(payload) {
            const summary = payload && payload.summary ? payload.summary : null;
            $('#derTotalEntries').text(summary ? String(summary.total_entries || 0) : '0');
            $('#derTotalAmount').text(summary ? String(summary.total_amount || '0.00') : '0.00');
            $('#derCategories').text(summary ? String(summary.categories || 0) : '0');
            $('#derDetailTotal').text(summary ? String(summary.total_amount || '0.00') : '0.00');

            const $catBody = $('#derCatTable tbody');
            $catBody.empty();
            const byCat = payload && payload.by_category ? payload.by_category : [];
            if (!byCat.length) {
                $catBody.append('<tr><td colspan=\"3\" class=\"text-center text-muted\">No records.</td></tr>');
            } else {
                let grandCount = 0;
                let grandTotal = 0;
                byCat.forEach(function (r) {
                    grandCount += parseInt(r.count || '0', 10) || 0;
                    grandTotal += parseFloat(String(r.total_amount || '0').replace(/,/g, '')) || 0;
                    $catBody.append(
                        '<tr>' +
                        '<td>' + (r.category || '-') + '</td>' +
                        '<td class=\"text-end\">' + (r.count || 0) + '</td>' +
                        '<td class=\"text-end\">' + (r.total_amount || '0.00') + '</td>' +
                        '</tr>'
                    );
                });
                $catBody.append(
                    '<tr class=\"table-light\">' +
                    '<td class=\"fw-semibold\">Grand Total</td>' +
                    '<td class=\"text-end fw-semibold\">' + grandCount + '</td>' +
                    '<td class=\"text-end fw-semibold\">' + grandTotal.toFixed(2) + '</td>' +
                    '</tr>'
                );
            }

            const $det = $('#derDetailTable tbody');
            $det.empty();
            const details = payload && payload.details ? payload.details : [];
            if (!details.length) {
                $det.append('<tr><td colspan=\"7\" class=\"text-center text-muted\">No records.</td></tr>');
                $('#derDetailTotal').text('0.00');
            } else {
                let sum = 0;
                details.forEach(function (r) {
                    sum += parseFloat(String(r.amount || '0').replace(/,/g, '')) || 0;
                    $det.append(
                        '<tr>' +
                        '<td>' + (r.expense_code || '-') + '</td>' +
                        '<td>' + (r.expense_date ? formatUiDateDmy(r.expense_date, 'display') : '-') + '</td>' +
                        '<td>' + (r.category || '-') + '</td>' +
                        '<td>' + renderTruncatedDescription((r.description || ''), 'display', 20) + '</td>' +
                        '<td class=\"text-end\">' + (r.amount || '0.00') + '</td>' +
                        '<td>' + (r.payment_method || '-') + '</td>' +
                        '<td>' + (r.paid_to || '-') + '</td>' +
                        '</tr>'
                    );
                });
                $('#derDetailTotal').text(sum.toFixed(2));
            }
        }

        function fetchAndRender() {
            const f = currentFilters();
            return getJson('day-book/daily-expense-report/data', f)
                .done(function (res) {
                    renderReport(res);
                })
                .fail(function () {
                    notify('Unable to generate report.', 'danger');
                });
        }

        $(document).off('click.der', '#derBtnGenerate').on('click.der', '#derBtnGenerate', function () {
            fetchAndRender();
        });

        $(document).off('click.der', '#derBtnExcel').on('click.der', '#derBtnExcel', function () {
            const f = currentFilters();
            window.location.href = base('day-book/daily-expense-report/export-csv?start_date=' + encodeURIComponent(f.start_date) + '&end_date=' + encodeURIComponent(f.end_date) + '&category=' + encodeURIComponent(f.category));
        });

        $(document).off('click.der', '#derBtnPdf').on('click.der', '#derBtnPdf', function () {
            const f = currentFilters();
            window.location.href = base('day-book/daily-expense-report/export-pdf?start_date=' + encodeURIComponent(f.start_date) + '&end_date=' + encodeURIComponent(f.end_date) + '&category=' + encodeURIComponent(f.category));
        });

        loadCategories().always(function () {
            fetchAndRender();
        });
    };

    BMS.initDailyExpenseEntry = function () {
        const $txt = $('#de_exp_date');
        const $nat = $('#de_exp_date_native');
        if (!($txt.length && $nat.length)) return;
        flatpickr('#de_exp_date', {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: true,
            onChange: function(selectedDates, dateStr) {
                $('#de_exp_date_native').val(dateStr);
            }
        });
    };

	    BMS.initProformaCreate = function () {
	        function parseMoney(v) {
	            const n = parseFloat(String(v || '').replace(/,/g, ''));
	            return isFinite(n) ? n : 0;
	        }

	        function setMoney($el, value) {
	            if (!($el && $el.length)) return;
	            $el.val((parseFloat(value) || 0).toFixed(2));
	        }

	        function gstEnabled() {
	            return ($('#pf_invoice_type').val() || '') === 'GST Invoice';
	        }

	        function toggleGstNo() {
	            const show = gstEnabled();
	            $('#pf_gst_row, #pf_gst_col').toggleClass('d-none', !show);
	            if (!show) {
	                $('#pf_gst').val('');
	            }
	        }

	        function recalcGst() {
	            const total = parseMoney($('#pf_total').text());
	            const enabled = gstEnabled();
	            const percent = Math.max(0, parseFloat($('#pf_gst_percent').val() || '0') || 0);
	            const mode = $('input[name="pf_gst_mode"]:checked').val() || 'CGST_SGST';

	            toggleGstNo();

	            if (!enabled) {
	                $('#pf_gst_box').addClass('d-none');
	                setMoney($('#pf_total_gst'), 0);
	                setMoney($('#pf_net_amount'), total);
	                setMoney($('#pf_cgst_amt'), 0); setMoney($('#pf_cgst_val'), 0);
	                setMoney($('#pf_sgst_amt'), 0); setMoney($('#pf_sgst_val'), 0);
	                setMoney($('#pf_igst_amt'), 0); setMoney($('#pf_igst_val'), 0);
	                return;
	            }

	            $('#pf_gst_box').removeClass('d-none');

	            const tax = (total * percent) / 100.0;
	            let cgst = 0, sgst = 0, igst = 0;
	            if (mode === 'IGST') {
	                igst = tax;
	            } else {
	                cgst = tax / 2.0;
	                sgst = tax / 2.0;
	            }
	            const totalGst = cgst + sgst + igst;
	            const net = total + totalGst;

	            setMoney($('#pf_cgst_amt'), percent > 0 && mode !== 'IGST' ? (percent / 2.0) : 0);
	            setMoney($('#pf_cgst_val'), cgst);
	            setMoney($('#pf_sgst_amt'), percent > 0 && mode !== 'IGST' ? (percent / 2.0) : 0);
	            setMoney($('#pf_sgst_val'), sgst);
	            setMoney($('#pf_igst_amt'), percent > 0 && mode === 'IGST' ? percent : 0);
	            setMoney($('#pf_igst_val'), igst);
	            setMoney($('#pf_total_gst'), totalGst);
	            setMoney($('#pf_net_amount'), net);
	        }

	        function setTotal(total, hasItems) {
	            const value = (parseFloat(total) || 0).toFixed(2);
	            const $span = $('#pf_total');
	            if ($span.length) $span.text(value);
	            const $input = $('#pf_total_input');
	            if ($input.length) $input.val(value);
	            $('#btnSaveProforma').prop('disabled', !hasItems);
	            recalcGst();
	        }

	        function rowHasAnyInput($tr) {
	            const desc = getBulletEditorText($tr.find('.pf-item-desc-editor'));
	            const qty = String($tr.find('.pf-item-qty').val() || '').trim();
	            const uom = String($tr.find('.pf-item-uom').val() || '').trim();
	            const price = String($tr.find('.pf-item-price').val() || '').trim();
	            const amt = String($tr.find('.pf-item-amt').val() || '').trim();
	            return (desc + qty + uom + price + amt) !== '';
	        }

	        function escapeHtml(s) {
	            return String(s || '')
	                .replace(/&/g, '&amp;')
	                .replace(/</g, '&lt;')
	                .replace(/>/g, '&gt;')
	                .replace(/"/g, '&quot;')
	                .replace(/'/g, '&#39;');
	        }

	        function stripBulletPrefix(line) {
	            return String(line || '').replace(/^[\s\u2022\u00B7\-\*]+/, '').trim();
	        }

	        function normalizeBulletText(text) {
	            return String(text || '')
	                .split(/\r?\n/)
	                .map(stripBulletPrefix)
	                .filter(function (l) { return l !== ''; })
	                .join("\n");
	        }

	        function getBulletEditorText($el) {
	            const el = $el && $el.length ? $el[0] : null;
	            if (!el) return '';

	            const liNodes = el.querySelectorAll('li');
	            if (liNodes && liNodes.length) {
	                return Array.from(liNodes)
	                    .map(function (li) {
	                        return stripBulletPrefix(String(li.innerText || li.textContent || '').replace(/\u00A0/g, ' ').trim());
	                    })
	                    .filter(function (l) { return l !== ''; })
	                    .join("\n");
	            }

	            return normalizeBulletText($el.text());
	        }

	        function ensureBulletList($el) {
	            const text = getBulletEditorText($el);
	            if (!text) {
	                $el.html('<ul><li><br></li></ul>');
	                return;
	            }
	            const lines = text.split("\n");
	            const html = '<ul>' + lines.map(function (l) { return '<li>' + escapeHtml(l) + '</li>'; }).join('') + '</ul>';
	            $el.html(html);
	        }

	        function recalcTotal() {
	            let total = 0;
	            let hasItems = false;
	            $('#pfItemsTable tbody tr').each(function () {
	                const $tr = $(this);
	                if (!rowHasAnyInput($tr)) return;
	                const amt = parseMoney($tr.find('.pf-item-amt').val());
	                total += amt;
	                const desc = getBulletEditorText($tr.find('.pf-item-desc-editor'));
	                if (desc) hasItems = true;
	            });
	            setTotal(total, hasItems);
	        }

	        function syncAmountFromQtyPrice($tr) {
	            const qty = parseMoney($tr.find('.pf-item-qty').val());
	            const price = parseMoney($tr.find('.pf-item-price').val());
	            const amt = qty * price;
	            $tr.find('.pf-item-amt').val(amt.toFixed(2));
	        }

	        function syncPriceFromAmount($tr) {
	            const qty = parseMoney($tr.find('.pf-item-qty').val());
	            const amt = parseMoney($tr.find('.pf-item-amt').val());
	            if (qty > 0) {
	                $tr.find('.pf-item-price').val((amt / qty).toFixed(2));
	            }
	        }

	        function rowHtml() {
	            return '' +
	                '<tr>' +
	                    '<td><div class="form-control form-control-sm pf-item-desc-editor" contenteditable="true" data-placeholder="Description (one bullet per line)" style="min-height:90px;"></div></td>' +
	                    '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-qty" value="1"></td>' +
	                    '<td><input type="text" class="form-control form-control-sm pf-item-uom" value="Nos"></td>' +
	                    '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-price" value="0.00"></td>' +
	                    '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-amt" value="0.00"></td>' +
	                    '<td class="text-center">' +
	                        '<div class="btn-group" role="group" aria-label="Row actions">' +
	                            '<button type="button" class="btn btn-sm btn-primary bms-pf-icon-btn pf-row-add">+</button>' +
	                            '<button type="button" class="btn btn-sm btn-danger bms-pf-icon-btn pf-row-remove">-</button>' +
	                        '</div>' +
	                    '</td>' +
	                '</tr>';
	        }

	        function addRow(afterTr) {
	            const $row = $(rowHtml());
	            if (afterTr && afterTr.length) {
	                $row.insertAfter(afterTr);
	            } else {
	                $('#pfItemsTable tbody').append($row);
	            }
	            const $desc = $row.find('.pf-item-desc-editor');
	            if ($desc.length) ensureBulletList($desc);
	            recalcTotal();
	            return $row;
	        }

	        function ensureOneRow() {
	            const $rows = $('#pfItemsTable tbody tr');
	            if (!$rows.length) addRow(null);
	        }

	        function collectItems() {
	            const items = [];
	            $('#pfItemsTable tbody tr').each(function () {
	                const $tr = $(this);
	                const desc = getBulletEditorText($tr.find('.pf-item-desc-editor'));
	                const qty = parseMoney($tr.find('.pf-item-qty').val());
	                const price = parseMoney($tr.find('.pf-item-price').val());
	                const amt = parseMoney($tr.find('.pf-item-amt').val());

	                if (!desc && qty === 0 && price === 0 && amt === 0) return;
	                items.push({
	                    description: desc,
	                    quantity: qty,
	                    unit_price: price,
	                    amount: amt
	                });
	            });
	            return items;
	        }

	        const $clientSelect = $('#pf_client_id');
        let currentClientCountry = '';

        function syncInvoiceTypeFromCountry() {
            $('#pf_invoice_type').val(resolveInvoiceTypeByCountry(currentClientCountry));
            recalcGst();
        }

        function clearClientFields() {
            currentClientCountry = '';
            if ($clientSelect.length) $clientSelect.val('');
            $('#pf_company').val('');
            $('#pf_gst').val('');
            $('#pf_addr1').val('');
            $('#pf_addr2').val('');
            $('#pf_city').val('');
            $('#pf_state').val('');
            $('#pf_pincode').val('');
            syncInvoiceTypeFromCountry();
        }

        function applyClientOption($opt) {
            currentClientCountry = ($opt.data('country') || '').toString().trim();
            $('#pf_company').val(($opt.data('company') || '').toString().trim());
            $('#pf_gst').val(($opt.data('gst') || '').toString().trim());
            $('#pf_addr1').val(($opt.data('addr1') || '').toString().trim());
            $('#pf_addr2').val(($opt.data('addr2') || '').toString().trim());
            $('#pf_city').val(($opt.data('city') || '').toString().trim());
            $('#pf_state').val(($opt.data('state') || '').toString().trim());
            $('#pf_pincode').val(($opt.data('pincode') || '').toString().trim());
            syncInvoiceTypeFromCountry();
        }
	        if ($clientSelect.length) {
	            $clientSelect.on('change', function () {
	                const $opt = $(this).find('option:selected');
	                if (!$opt.length || !String($opt.val() || '').trim()) {
	                    clearClientFields();
	                    return;
	                }
	                applyClientOption($opt);
	            });
	        }
        syncInvoiceTypeFromCountry();

	        function syncDueDateFromIssue(iso) {
	            const dueIso = addDaysToIso(iso, 30);
	            setDateFieldValue('#pf_due', dueIso);
	        }

	        flatpickr('#pf_date', {
	            dateFormat: "Y-m-d",
	            altInput: true,
	            altFormat: "d M Y",
	            allowInput: true,
	            onChange: function(selectedDates, dateStr) { $('#pf_date_native').val(dateStr); syncDueDateFromIssue(dateStr); }
	        });
	        flatpickr('#pf_due', {
	            dateFormat: "Y-m-d",
	            altInput: true,
	            altFormat: "d M Y",
	            allowInput: true,
	            onChange: function(selectedDates, dateStr) { $('#pf_due_native').val(dateStr); }
	        });
	        $('#pf_date').off('change.bmsInvoiceDate input.bmsInvoiceDate').on('change.bmsInvoiceDate input.bmsInvoiceDate', function () {
	            syncDueDateFromIssue(readDateFieldIso('#pf_date', textToIso));
	        });
	        syncDueDateFromIssue(readDateFieldIso('#pf_date', textToIso));

	        $('#pf_invoice_type').on('change', recalcGst);
	        $('#pf_gst_percent').on('input', recalcGst);
	        $(document).on('change', 'input[name="pf_gst_mode"]', recalcGst);
	        recalcGst();

	        ensureOneRow();
	        setTotal(0, false);

	        $(document)
	            .off('focus.bmsPfDesc', '.pf-item-desc-editor')
            .on('focus.bmsPfDesc', '.pf-item-desc-editor', function () {
                const $el = $(this);
                if (!String($el.text() || '').trim()) {
                    ensureBulletList($el);
                }
            });

        $(document)
            .off('blur.bmsPfDesc', '.pf-item-desc-editor')
            .on('blur.bmsPfDesc', '.pf-item-desc-editor', function () {
                ensureBulletList($(this));
            });

        $(document)
            .off('keydown.bmsPfDesc', '.pf-item-desc-editor')
            .on('keydown.bmsPfDesc', '.pf-item-desc-editor', function (e) {
                if (e.key !== 'Enter') return;
                const sel = window.getSelection();
                const li = sel && sel.anchorNode ? $(sel.anchorNode).closest('li') : $();
                if (!li.length) {
                    e.preventDefault();
                    ensureBulletList($(this));
                }
            });

	        $('#pfItemsTable')
	            .off('click', 'button.pf-row-add')
	            .on('click', 'button.pf-row-add', function (e) {
	                e.preventDefault();
	                addRow($(this).closest('tr'));
	            });

	        $('#pfItemsTable')
	            .off('click', 'button.pf-row-remove')
	            .on('click', 'button.pf-row-remove', function (e) {
	                e.preventDefault();
	                const $tr = $(this).closest('tr');
	                $tr.remove();
	                ensureOneRow();
	                recalcTotal();
	            });

	        $('#pfItemsTable')
	            .off('input', '.pf-item-qty,.pf-item-price')
	            .on('input', '.pf-item-qty,.pf-item-price', function () {
	                const $tr = $(this).closest('tr');
	                syncAmountFromQtyPrice($tr);
	                recalcTotal();
	            });

	        $('#pfItemsTable')
	            .off('input', '.pf-item-amt')
	            .on('input', '.pf-item-amt', function () {
	                const $tr = $(this).closest('tr');
	                syncPriceFromAmount($tr);
	                recalcTotal();
	            });

	        $('#pfItemsTable')
	            .off('input', '.pf-item-desc-editor,.pf-item-uom')
	            .on('input', '.pf-item-desc-editor,.pf-item-uom', recalcTotal);

	        $('#btnSaveProforma').on('click', function () {
	            const invoiceNo = String($('#pf_invoice_no').val() || '').trim();
	            const clientId = String($('#pf_client_id').val() || '').trim();
	            const pfDate = readDateFieldIso('#pf_date', textToIso);
	            const currency = ($('#pf_currency').val() || '').trim();
            const dueDate = readDateFieldIso('#pf_due', textToIso);
            const billingFrom = String($('#pf_from').val() || '').trim() || pfDate;
	            const invoiceType = resolveInvoiceTypeByCountry(currentClientCountry);
	            const gstPercent = ($('#pf_gst_percent').val() || '').trim();
	            const gstMode = ($('input[name="pf_gst_mode"]:checked').val() || '').trim();
	            const items = collectItems();

	            $('#pf_invoice_no, #pf_client_id, #pf_date, #pf_invoice_type, #pf_currency, #pf_due').removeClass('is-invalid');

	            let hasInvalid = false;
	            if (!invoiceNo) { $('#pf_invoice_no').addClass('is-invalid'); hasInvalid = true; }
	            if (!clientId) { $('#pf_client_id').addClass('is-invalid'); hasInvalid = true; }
	            if (!pfDate) { $('#pf_date').addClass('is-invalid'); hasInvalid = true; }
	            if (!invoiceType) { $('#pf_invoice_type').addClass('is-invalid'); hasInvalid = true; }
	            if (!currency) { $('#pf_currency').addClass('is-invalid'); hasInvalid = true; }
	            if (!dueDate) { $('#pf_due').addClass('is-invalid'); hasInvalid = true; }

	            if (hasInvalid) {
	                notify('Please fill the required fields.', 'danger');
	                return;
	            }
	            if (!items.length) {
	                notify('Add at least one item.', 'danger');
	                return;
	            }

	            postJson('proforma/save', {
	                client_id: clientId,
	                proforma_number: invoiceNo,
	                proforma_date: pfDate,
	                invoice_type: invoiceType,
                billing_from: billingFrom,
                billing_to: dueDate,
	                currency: currency,
	                gst_percent: gstEnabled() ? gstPercent : '',
	                gst_mode: gstEnabled() ? gstMode : '',
	                items: items,
	            })
	                .done(function (res) {
	                    notify(res.message || 'Invoice created.', 'success');
	                    window.location.href = base('proforma');
	                })
	                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.', 'danger'); });
	        });
	    };

	    BMS.initProformaEdit = function () {
	        // New edit form uses the same structure as Create (editable item rows).
	        if ($('#pfItemsTable').length) {
	            function parseMoney(v) {
	                const n = parseFloat(String(v || '').replace(/,/g, ''));
	                return isFinite(n) ? n : 0;
	            }

	            function setMoney($el, value) {
	                if (!($el && $el.length)) return;
	                $el.val((parseFloat(value) || 0).toFixed(2));
	            }

	            function gstEnabled() {
	                return ($('#pf_invoice_type').val() || '') === 'GST Invoice';
	            }

	            function toggleGstNo() {
	                const show = gstEnabled();
	                $('#pf_gst_row, #pf_gst_col').toggleClass('d-none', !show);
	                if (!show) {
	                    $('#pf_gst').val('');
	                }
	            }

	            function recalcGst() {
	                const total = parseMoney($('#pf_total').text());
	                const enabled = gstEnabled();
	                const percent = Math.max(0, parseFloat($('#pf_gst_percent').val() || '0') || 0);
	                const mode = $('input[name="pf_gst_mode"]:checked').val() || 'CGST_SGST';

	                toggleGstNo();

	                if (!enabled) {
	                    $('#pf_gst_box').addClass('d-none');
	                    setMoney($('#pf_total_gst'), 0);
	                    setMoney($('#pf_net_amount'), total);
	                    setMoney($('#pf_cgst_amt'), 0); setMoney($('#pf_cgst_val'), 0);
	                    setMoney($('#pf_sgst_amt'), 0); setMoney($('#pf_sgst_val'), 0);
	                    setMoney($('#pf_igst_amt'), 0); setMoney($('#pf_igst_val'), 0);
	                    return;
	                }

	                $('#pf_gst_box').removeClass('d-none');

	                const tax = (total * percent) / 100.0;
	                let cgst = 0, sgst = 0, igst = 0;
	                if (mode === 'IGST') {
	                    igst = tax;
	                } else {
	                    cgst = tax / 2.0;
	                    sgst = tax / 2.0;
	                }
	                const totalGst = cgst + sgst + igst;
	                const net = total + totalGst;

	                setMoney($('#pf_cgst_amt'), percent > 0 && mode !== 'IGST' ? (percent / 2.0) : 0);
	                setMoney($('#pf_cgst_val'), cgst);
	                setMoney($('#pf_sgst_amt'), percent > 0 && mode !== 'IGST' ? (percent / 2.0) : 0);
	                setMoney($('#pf_sgst_val'), sgst);
	                setMoney($('#pf_igst_amt'), percent > 0 && mode === 'IGST' ? percent : 0);
	                setMoney($('#pf_igst_val'), igst);
	                setMoney($('#pf_total_gst'), totalGst);
	                setMoney($('#pf_net_amount'), net);
	            }

	            function setTotal(total, hasItems) {
	                const value = (parseFloat(total) || 0).toFixed(2);
	                const $span = $('#pf_total');
	                if ($span.length) $span.text(value);
	                const $input = $('#pf_total_input');
	                if ($input.length) $input.val(value);
	                $('#btnUpdateProforma').prop('disabled', !hasItems);
	                recalcGst();
	            }

	            function rowHasAnyInput($tr) {
	                const desc = getBulletEditorText($tr.find('.pf-item-desc-editor'));
	                const qty = String($tr.find('.pf-item-qty').val() || '').trim();
	                const uom = String($tr.find('.pf-item-uom').val() || '').trim();
	                const price = String($tr.find('.pf-item-price').val() || '').trim();
	                const amt = String($tr.find('.pf-item-amt').val() || '').trim();
	                return (desc + qty + uom + price + amt) !== '';
	            }

	            function escapeHtml(s) {
	                return String(s || '')
	                    .replace(/&/g, '&amp;')
	                    .replace(/</g, '&lt;')
	                    .replace(/>/g, '&gt;')
	                    .replace(/"/g, '&quot;')
	                    .replace(/'/g, '&#39;');
	            }

	            function stripBulletPrefix(line) {
	                return String(line || '').replace(/^[\s\u2022\u00B7\-\*]+/, '').trim();
	            }

	            function normalizeBulletText(text) {
	                return String(text || '')
	                    .split(/\r?\n/)
	                    .map(stripBulletPrefix)
	                    .filter(function (l) { return l !== ''; })
	                    .join("\n");
	            }

	            function getBulletEditorText($el) {
	                const el = $el && $el.length ? $el[0] : null;
	                if (!el) return '';

	                const liNodes = el.querySelectorAll('li');
	                if (liNodes && liNodes.length) {
	                    return Array.from(liNodes)
	                        .map(function (li) {
	                            return stripBulletPrefix(String(li.innerText || li.textContent || '').replace(/\u00A0/g, ' ').trim());
	                        })
	                        .filter(function (l) { return l !== ''; })
	                        .join("\n");
	                }

	                return normalizeBulletText($el.text());
	            }

	            function ensureBulletList($el) {
	                const text = getBulletEditorText($el);
	                if (!text) {
	                    $el.html('<ul><li><br></li></ul>');
	                    return;
	                }
	                const lines = text.split("\n");
	                const html = '<ul>' + lines.map(function (l) { return '<li>' + escapeHtml(l) + '</li>'; }).join('') + '</ul>';
	                $el.html(html);
	            }

	            function recalcTotal() {
	                let total = 0;
	                let hasItems = false;
	                $('#pfItemsTable tbody tr').each(function () {
	                    const $tr = $(this);
	                    if (!rowHasAnyInput($tr)) return;
	                    const amt = parseMoney($tr.find('.pf-item-amt').val());
	                    total += amt;
	                    const desc = getBulletEditorText($tr.find('.pf-item-desc-editor'));
	                    if (desc) hasItems = true;
	                });
	                setTotal(total, hasItems);
	            }

	            function syncAmountFromQtyPrice($tr) {
	                const qty = parseMoney($tr.find('.pf-item-qty').val());
	                const price = parseMoney($tr.find('.pf-item-price').val());
	                const amt = qty * price;
	                $tr.find('.pf-item-amt').val(amt.toFixed(2));
	            }

	            function syncPriceFromAmount($tr) {
	                const qty = parseMoney($tr.find('.pf-item-qty').val());
	                const amt = parseMoney($tr.find('.pf-item-amt').val());
	                if (qty > 0) {
	                    $tr.find('.pf-item-price').val((amt / qty).toFixed(2));
	                }
	            }

	            function rowHtml() {
	                return '' +
	                    '<tr>' +
	                        '<td>' +
	                            '<input type="hidden" class="pf-item-id" value="0">' +
	                            '<div class="form-control form-control-sm pf-item-desc-editor" contenteditable="true" data-placeholder="Description (one bullet per line)" style="min-height:90px;"></div>' +
	                        '</td>' +
	                        '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-qty" value="1"></td>' +
	                        '<td><input type="text" class="form-control form-control-sm pf-item-uom" value="Nos"></td>' +
	                        '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-price" value="0.00"></td>' +
	                        '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-amt" value="0.00"></td>' +
	                        '<td class="text-center">' +
	                            '<div class="btn-group" role="group" aria-label="Row actions">' +
	                                '<button type="button" class="btn btn-sm btn-primary bms-pf-icon-btn pf-row-add">+</button>' +
	                                '<button type="button" class="btn btn-sm btn-danger bms-pf-icon-btn pf-row-remove">-</button>' +
	                            '</div>' +
	                        '</td>' +
	                    '</tr>';
	            }

	            function addRow(afterTr) {
	                const $row = $(rowHtml());
	                if (afterTr && afterTr.length) {
	                    $row.insertAfter(afterTr);
	                } else {
	                    $('#pfItemsTable tbody').append($row);
	                }
	                const $desc = $row.find('.pf-item-desc-editor');
	                if ($desc.length) ensureBulletList($desc);
	                recalcTotal();
	                return $row;
	            }

	            function ensureOneRow() {
	                const $rows = $('#pfItemsTable tbody tr');
	                if (!$rows.length) addRow(null);
	            }

	            function collectItems() {
	                const items = [];
	                $('#pfItemsTable tbody tr').each(function () {
	                    const $tr = $(this);
	                    const id = parseInt($tr.find('.pf-item-id').val() || '0', 10) || 0;
	                    const desc = getBulletEditorText($tr.find('.pf-item-desc-editor'));
	                    const qty = parseMoney($tr.find('.pf-item-qty').val());
	                    const price = parseMoney($tr.find('.pf-item-price').val());
	                    const amt = parseMoney($tr.find('.pf-item-amt').val());

	                    if (!desc && qty === 0 && price === 0 && amt === 0) return;
	                    items.push({
	                        id: id,
	                        description: desc,
	                        quantity: qty,
	                        unit_price: price,
	                        amount: amt
	                    });
	                });
	                return items;
	            }

            let currentClientCountry = '';

            function syncInvoiceTypeFromCountry() {
                $('#pf_invoice_type').val(resolveInvoiceTypeByCountry(currentClientCountry));
                recalcGst();
            }

            function initCompanyField() {
                const $opt = $('#pf_client_id option:selected');
                currentClientCountry = ($opt.data('country') || '').toString().trim();
                $('#pf_company').val(($opt.data('company') || '').toString().trim());
                $('#pf_gst').val(($opt.data('gst') || '').toString().trim());
                $('#pf_addr1').val(($opt.data('addr1') || '').toString().trim());
                $('#pf_addr2').val(($opt.data('addr2') || '').toString().trim());
                $('#pf_city').val(($opt.data('city') || '').toString().trim());
                $('#pf_state').val(($opt.data('state') || '').toString().trim());
                $('#pf_pincode').val(($opt.data('pincode') || '').toString().trim());
                syncInvoiceTypeFromCountry();
            }
	            initCompanyField();
	            $('#pf_client_id').on('change', initCompanyField);
	            function syncDueDateFromIssue(iso) {
	                const dueIso = addDaysToIso(iso, 30);
	                setDateFieldValue('#pf_due', dueIso);
	            }

	            flatpickr('#pf_date', {
	                dateFormat: "Y-m-d",
	                altInput: true,
	                altFormat: "d M Y",
	                allowInput: true,
	                onChange: function(selectedDates, dateStr) { $('#pf_date_native').val(dateStr); syncDueDateFromIssue(dateStr); }
	            });
	            flatpickr('#pf_due', {
	                dateFormat: "Y-m-d",
	                altInput: true,
	                altFormat: "d M Y",
	                allowInput: true,
	                onChange: function(selectedDates, dateStr) { $('#pf_due_native').val(dateStr); }
	            });
	            $('#pf_date').off('change.bmsInvoiceDate input.bmsInvoiceDate').on('change.bmsInvoiceDate input.bmsInvoiceDate', function () {
	                syncDueDateFromIssue(readDateFieldIso('#pf_date'));
	            });
	            if (!readDateFieldIso('#pf_due')) {
	                syncDueDateFromIssue(readDateFieldIso('#pf_date'));
	            }

	            $('#pf_invoice_type').on('change', recalcGst);
	            $('#pf_gst_percent').on('input', recalcGst);
	            $(document).on('change', 'input[name="pf_gst_mode"]', recalcGst);

	            ensureOneRow();
	            recalcTotal();

	            $(document)
	                .off('focus.bmsPfDesc', '.pf-item-desc-editor')
                .on('focus.bmsPfDesc', '.pf-item-desc-editor', function () {
                    const $el = $(this);
                    if (!String($el.text() || '').trim()) {
                        ensureBulletList($el);
                    }
                });

            $(document)
                .off('blur.bmsPfDesc', '.pf-item-desc-editor')
                .on('blur.bmsPfDesc', '.pf-item-desc-editor', function () {
                    ensureBulletList($(this));
                });

            $(document)
                .off('keydown.bmsPfDesc', '.pf-item-desc-editor')
                .on('keydown.bmsPfDesc', '.pf-item-desc-editor', function (e) {
                    if (e.key !== 'Enter') return;
                    const sel = window.getSelection();
                    const li = sel && sel.anchorNode ? $(sel.anchorNode).closest('li') : $();
                    if (!li.length) {
                        e.preventDefault();
                        ensureBulletList($(this));
                    }
                });

	            $('#pfItemsTable')
	                .off('click', 'button.pf-row-add')
	                .on('click', 'button.pf-row-add', function (e) {
	                    e.preventDefault();
	                    addRow($(this).closest('tr'));
	                });

	            $('#pfItemsTable')
	                .off('click', 'button.pf-row-remove')
	                .on('click', 'button.pf-row-remove', function (e) {
	                    e.preventDefault();
	                    const $tr = $(this).closest('tr');
	                    $tr.remove();
	                    ensureOneRow();
	                    recalcTotal();
	                });

	            $('#pfItemsTable')
	                .off('input', '.pf-item-qty,.pf-item-price')
	                .on('input', '.pf-item-qty,.pf-item-price', function () {
	                    const $tr = $(this).closest('tr');
	                    syncAmountFromQtyPrice($tr);
	                    recalcTotal();
	                });

	            $('#pfItemsTable')
	                .off('input', '.pf-item-amt')
	                .on('input', '.pf-item-amt', function () {
	                    const $tr = $(this).closest('tr');
	                    syncPriceFromAmount($tr);
	                    recalcTotal();
	                });

	        $('#pfItemsTable')
	            .off('input', '.pf-item-desc-editor,.pf-item-uom')
	            .on('input', '.pf-item-desc-editor,.pf-item-uom', recalcTotal);

	            $('#btnUpdateProforma').on('click', function () {
	                const proformaId = parseInt($('#pf_id').val() || '0', 10) || 0;
	                const invoiceNo = String($('#pf_invoice_no').val() || '').trim();
	                const clientId = $('#pf_client_id').val();
	                const pfDate = readDateFieldIso('#pf_date');
	                const currency = ($('#pf_currency').val() || '').trim();
                const dueDate = readDateFieldIso('#pf_due');
                const billingFrom = String($('#pf_from').val() || '').trim() || pfDate;
	                const invoiceType = resolveInvoiceTypeByCountry(currentClientCountry);
	                const gstPercent = ($('#pf_gst_percent').val() || '').trim();
	                const gstMode = ($('input[name="pf_gst_mode"]:checked').val() || '').trim();
	                const items = collectItems();

	                if (!proformaId) return;
	                if (!invoiceNo) {
	                    notify('Invoice No is required.', 'danger');
	                    return;
	                }
	                if (!clientId) {
	                    notify('Client is required.', 'danger');
	                    return;
	                }
	                if (!pfDate) {
	                    notify('Date Of Issue is required.', 'danger');
	                    return;
	                }
	                if (!invoiceType) {
	                    notify('Invoice Type is required.', 'danger');
	                    return;
	                }
	                if (!currency) {
	                    notify('Currency is required.', 'danger');
	                    return;
	                }
	                if (!dueDate) {
	                    notify('Due Date is required.', 'danger');
	                    return;
	                }
	                if (!items.length) {
	                    notify('Add at least one item.', 'danger');
	                    return;
	                }

	                postJson('proforma/update', {
	                    proforma_id: proformaId,
	                    proforma_number: invoiceNo,
	                    client_id: clientId,
	                    proforma_date: pfDate,
	                    invoice_type: invoiceType,
                    billing_from: billingFrom,
                    billing_to: dueDate,
	                    currency: currency,
	                    gst_percent: gstEnabled() ? gstPercent : '',
	                    gst_mode: gstEnabled() ? gstMode : '',
	                    items: items,
	                })
	                    .done(function (res) {
	                        notify(res.message || 'Updated.', 'success');
	                        window.location.href = base('proforma');
	                    })
	                    .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.', 'danger'); });
	            });

	            return;
	        }

	        let table = null;
	        const viewModalEl = document.getElementById('billableViewModal');
	        const viewModal = viewModalEl ? new bootstrap.Modal(viewModalEl) : null;

	        function parseMoney(v) {
	            const n = parseFloat(String(v || '').replace(/,/g, ''));
	            return isFinite(n) ? n : 0;
	        }

	        function setTotal(total) {
	            const value = (parseFloat(total) || 0).toFixed(2);
	            const $span = $('#pf_total');
	            if ($span.length) $span.text(value);
	            const $input = $('#pf_total_input');
	            if ($input.length) $input.val(value);
	        }

	        function recalcTotal() {
	            let total = 0;
	            $('#dtProformaItems input.pf-chk:checked').each(function () {
	                total += (parseFloat($(this).data('amount')) || 0);
	            });
	            setTotal(total);
	            $('#btnUpdateProforma').prop('disabled', ($('#dtProformaItems input.pf-chk:checked').length === 0));
	        }

        const proformaId = parseInt($('#pf_id').val(), 10) || 0;
        const clientId = $('#pf_client_id').val();

        function syncLegacyDueDateFromIssue(iso) {
            const dueIso = addDaysToIso(iso, 30);
            setDateFieldValue('#pf_to', dueIso);
        }

        flatpickr('#pf_date', {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: true,
            onChange: function(selectedDates, dateStr) { $('#pf_date_native').val(dateStr); syncLegacyDueDateFromIssue(dateStr); }
        });
        flatpickr('#pf_from', {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: true,
            onChange: function(selectedDates, dateStr) { $('#pf_from_native').val(dateStr); }
        });
        flatpickr('#pf_to', {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: true,
            onChange: function(selectedDates, dateStr) { $('#pf_to_native').val(dateStr); }
        });
        $('#pf_date').off('change.bmsInvoiceDate input.bmsInvoiceDate').on('change.bmsInvoiceDate input.bmsInvoiceDate', function () {
            syncLegacyDueDateFromIssue(readDateFieldIso('#pf_date'));
        });
        syncLegacyDueDateFromIssue(readDateFieldIso('#pf_date'));

	        table = $('#dtProformaItems').DataTable($.extend(true, {}, dtDefaults(), {
            ajax: function (_data, callback) {
                if (!clientId || !proformaId) {
                    callback({ data: [] });
                    return;
                }
                // For edit, load pending items + currently included items (server returns included=1).
                getJson('proforma/edit-items', { client_id: clientId, proforma_id: proformaId })
                    .done(function (res) { callback(res); })
                    .fail(function () { callback({ data: [] }); });
            },
            order: [[2, 'desc']],
	            columns: [
	                { data: null, orderable: false, render: function (row) {
	                    const checked = row.included ? 'checked' : '';
	                    return '<input class="form-check-input pf-chk" type="checkbox" value="' + row.id + '" data-amount="' + row.amount + '" ' + checked + '>';
	                }},
	                { data: 'entry_no', render: function (d, t, row) { return d || ('BI-' + String(row.id).padStart(5, '0')); } },
	                { data: 'entry_date', render: formatUiDate },
	                { data: 'description', orderable: false, render: function (d, t, row) {
	                    const plain = descriptionPlainText(d);
	                    if (t === 'sort' || t === 'type' || t === 'filter') return plain;
	                    const inner = renderTruncatedDescriptionBullets(d, 'display', 25);
	                    if (!plain) return inner;
	                    return '' +
	                        '<button type="button" class="btn btn-link p-0 text-start text-decoration-none link-dark pf-edit-desc-view" data-id="' + row.id + '">' +
	                            inner +
	                        '</button>';
	                }},
	                { data: 'quantity', className: 'text-end' },
	                { data: 'unit_price', className: 'text-end' },
	                { data: 'amount', className: 'text-end' },
	                { data: 'billing_month', render: function (d) { return d || '-'; } },
	            ],
	        }));

	        $('#dtProformaItems').off('click', '.pf-edit-desc-view').on('click', '.pf-edit-desc-view', function () {
	            if (!viewModal || !table) return;
	            let $tr = $(this).closest('tr');
	            if ($tr.hasClass('child')) $tr = $tr.prev();
	            const row = table.row($tr).data();
	            if (!row) return;
	            const desc = String(row.description || '').trim();
	            if (!desc) return;

	            const entryNo = row.entry_no || ('BI-' + String(row.id || '').padStart(5, '0'));
	            $('#bi_view_entry_no').text(entryNo || '-');
	            $('#bi_view_date').text(formatUiDate(row.entry_date, 'display'));
	            $('#bi_view_client').text(String($('#pf_client_id option:selected').text() || '').trim() || '-');
	            $('#bi_view_month').text(String(row.billing_month || '').trim() || '-');
	            $('#bi_view_amount').text(String(row.amount || '').trim() || '-');
	            $('#bi_view_status').text(String(row.status || '').trim() || '-');
	            $('#bi_view_description').html(renderBulletText(row.description, 'display'));

	            viewModal.show();
	        });

        $('#dtProformaItems').on('draw.dt', function () {
            $('#pf_chkAll').prop('checked', false);
            recalcTotal();
        });

        $('#pf_chkAll').off('change').on('change', function () {
            const checked = $(this).is(':checked');
            $('#dtProformaItems input.pf-chk').prop('checked', checked);
            recalcTotal();
        });

        $('#dtProformaItems').off('change', 'input.pf-chk').on('change', 'input.pf-chk', recalcTotal);

	        $('#btnUpdateProforma').on('click', function () {
	            const ids = [];
	            $('#dtProformaItems input.pf-chk:checked').each(function () { ids.push(parseInt($(this).val(), 10)); });
	            if (ids.length === 0) return;
	            const invoiceNo = String($('#pf_invoice_no').val() || '').trim();
	            if (!invoiceNo) {
	                notify('Invoice No is required.', 'danger');
	                return;
	            }
	            const pfDate = readDateFieldIso('#pf_date');
	            if (!pfDate) {
	                notify('Date Of Issue is required.', 'danger');
	                return;
	            }
	            postJson('proforma/update', {
	                proforma_id: proformaId,
	                proforma_number: invoiceNo,
	                item_ids: ids,
	                proforma_date: pfDate,
	                billing_from: readDateFieldIso('#pf_from'),
	                billing_to: readDateFieldIso('#pf_due') || readDateFieldIso('#pf_to'),
	            })
                .done(function (res) {
                    notify(res.message || 'Updated.', 'success');
                    if (res.proforma && res.proforma.id) {
                        window.location.href = base('proforma/show/' + res.proforma.id);
                    }
                })
                .fail(function (xhr) { notify((xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.', 'danger'); });
        });
    };

    $(function () {
        $.fn.dataTable && ($.fn.dataTable.defaults.language = { emptyTable: 'No records found.' });

        const sidebarBreakpoint = window.matchMedia('(max-width: 991.98px)');
        document.body.classList.remove('bms-sidebar-collapsed');

        function setSidebarState(open, persist) {
            document.body.classList.toggle('bms-sidebar-open', open);
            document.body.classList.toggle('bms-sidebar-hidden', !open);

            const overlay = document.getElementById('appSidebarOverlay');
            if (overlay) {
                overlay.hidden = !open;
                overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
            }

            const openBtn = document.getElementById('btnOpenSidebar');
            if (openBtn) {
                openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            const toggleBtn = document.getElementById('btnToggleSidebar');
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            const reopenBtn = document.getElementById('btnReopenSidebar');
            if (reopenBtn) {
                if (!open) {
                    reopenBtn.classList.remove('d-none');
                } else {
                    reopenBtn.classList.add('d-none');
                }
            }

            if (persist) {
                try {
                    localStorage.setItem(SIDEBAR_KEY, open ? '0' : '1');
                } catch (e) {}
            }
        }

        function toggleSidebarState() {
            setSidebarState(document.body.classList.contains('bms-sidebar-hidden'), true);
        }
        window.BMS.toggleSidebarState = toggleSidebarState;

        function resolveInitialSidebarOpen() {
            try {
                const saved = localStorage.getItem(SIDEBAR_KEY);
                if (saved === '1' || saved === '0') {
                    return saved !== '1';
                }
            } catch (e) {}

            return !sidebarBreakpoint.matches;
        }

        setSidebarState(resolveInitialSidebarOpen(), false);

        $(document).on('click', '#btnToggleSidebar, #btnToggleSidebarHeader, #btnOpenSidebar', function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Sidebar toggle clicked');
            toggleSidebarState();
        });

        $('#appSidebarOverlay').on('click', function () {
            setSidebarState(false, true);
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && document.body.classList.contains('bms-sidebar-open')) {
                setSidebarState(false, true);
            }
        });

        $(document).on('click', '.app-sidebar a[href]:not(.nav-parent)', function () {
            if (!sidebarBreakpoint.matches) return;
            setSidebarState(false, true);
        });

        // Real-time status dropdown updates (all DataTables)
        $(document).on('change', 'select.bms-status-select', function () {
            const $sel = $(this);
            const tableName = $sel.data('table');
            const recordId = $sel.data('id');
            const statusValue = $sel.val();
            const oldVal = $sel.attr('data-old') || $sel.data('old') || null;

            $sel.prop('disabled', true);

            postJson('update-status', {
                table_name: tableName,
                record_id: recordId,
                status_value: statusValue,
            })
                .done(function (res) {
                    $sel.data('old', statusValue);
                    $sel.attr('data-old', statusValue);
                    notify((res && res.message) || 'Status updated successfully.', 'success');
                })
                .fail(function (xhr) {
                    const res = xhr.responseJSON || {};
                    notify(res.message || 'Status update failed.', 'danger');
                    if (oldVal !== null) {
                        $sel.val(oldVal);
                    }
                })
                .always(function () {
                    $sel.prop('disabled', false);
                });
        });
    });
})();






