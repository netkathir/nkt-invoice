<?php

/**
 * Billing Management System helpers.
 */

if (! function_exists('bms_date')) {
    /**
     * Format any date/datetime string as "DD Mon YYYY".
     *
     * @param string|null $value e.g. 2026-03-10 or 2026-03-10 12:30:00
     */
    function bms_date(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '-';
        }

        $ts = strtotime($value);
        if ($ts === false) {
            return $value;
        }

        return date('d M Y', $ts);
    }
}

if (! function_exists('bms_description_to_plain')) {
    /**
     * Normalize a description value to plain text (newline-separated).
     *
     * Accepts either:
     * - plain text (already newline-separated)
     * - HTML bullet list from an editor (<ul><li>..</li></ul>)
     */
    function bms_description_to_plain(?string $value): string
    {
        $value = (string) $value;
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $plain = '';

        // HTML input (e.g., <ul><li>..</li></ul>) - also handle entity-escaped HTML.
        $maybeHtml = $value;
        if (strpos($maybeHtml, '<') === false && stripos($maybeHtml, '&lt;') !== false) {
            $decoded = html_entity_decode($maybeHtml, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if (strpos($decoded, '<') !== false) {
                $maybeHtml = $decoded;
            }
        }

        if (strpos($maybeHtml, '<') !== false) {
            try {
                $doc = new \DOMDocument('1.0', 'UTF-8');
                libxml_use_internal_errors(true);
                $doc->loadHTML('<?xml encoding="UTF-8"><body>' . $maybeHtml . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                libxml_clear_errors();

                $lis = $doc->getElementsByTagName('li');
                $items = [];
                foreach ($lis as $li) {
                    $t = bms_description_node_text($li);
                    if ($t !== '') {
                        $items[] = $t;
                    }
                }

                if ($items !== []) {
                    $plain = implode("\n", $items);
                } else {
                    $body = $doc->getElementsByTagName('body')->item(0);
                    $plain = $body ? bms_description_node_text($body) : html_entity_decode(strip_tags($maybeHtml), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            } catch (\Throwable $e) {
                $plain = html_entity_decode(strip_tags($maybeHtml), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
        } else {
            $plain = $value;
        }

        // Normalize line breaks and remove common bullet prefixes.
        $plain = str_replace(["\r\n", "\r"], "\n", $plain);
        $lines = explode("\n", $plain);
        $out = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $line = preg_replace('/^[\\x{2022}\\x{2023}\\x{25E6}\\x{2043}\\x{2219}\\*\\-\\+]+\\s*/u', '', $line) ?? $line;
            $line = trim($line);
            if ($line !== '') {
                $out[] = $line;
            }
        }

        return implode("\n", $out);
    }
}

if (! function_exists('bms_description_node_text')) {
    /**
     * Extract text from a DOM node while preserving HTML line breaks as newlines.
     */
    function bms_description_node_text(\DOMNode $node): string
    {
        $parts = [];

        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText || $child instanceof \DOMCdataSection) {
                $parts[] = html_entity_decode((string) $child->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                continue;
            }

            if (! ($child instanceof \DOMElement)) {
                continue;
            }

            $name = strtolower($child->nodeName);
            if ($name === 'br') {
                $parts[] = "\n";
                continue;
            }

            $childText = bms_description_node_text($child);
            if ($childText !== '') {
                $parts[] = $childText;
            }

            if (in_array($name, ['p', 'div', 'li'], true)) {
                $parts[] = "\n";
            }
        }

        $text = implode('', $parts);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/[ \t]+\n/u", "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

        return trim($text);
    }
}

if (! function_exists('bms_company_info')) {
    /**
     * Fetch the single saved company information record.
     *
     * @return array<string, mixed>
     */
    function bms_company_info(): array
    {
        static $cached = null;
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $db = db_connect();
            if (! $db->tableExists('company_information')) {
                $cached = [];
                return $cached;
            }

            $row = $db->table('company_information')->orderBy('id', 'ASC')->get()->getRowArray();
            $cached = is_array($row) ? $row : [];
        } catch (\Throwable $e) {
            $cached = [];
        }

        return $cached;
    }
}

if (! function_exists('bms_company_logo_url')) {
    /**
     * Resolve company logo URL, with a safe default fallback.
     *
     * @param array<string, mixed>|null $info
     */
    function bms_company_logo_url(?array $info = null): string
    {
        $info = is_array($info) ? $info : bms_company_info();
        $rel = trim((string) ($info['logo_path'] ?? ''));
        if ($rel !== '') {
            return base_url(ltrim($rel, '/'));
        }

        return base_url('assets/img/Netkathir_logo.png');
    }
}

if (! function_exists('bms_company_website_url')) {
    /**
     * Normalize website text into a clickable URL.
     */
    function bms_company_website_url(?string $website): string
    {
        $website = trim((string) $website);
        if ($website === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $website) === 1) {
            return $website;
        }

        return 'https://' . $website;
    }
}

if (! function_exists('bms_is_india_country')) {
    /**
     * Treat blank country as India for backward compatibility with older client records.
     */
    function bms_is_india_country(?string $country): bool
    {
        $country = strtolower(trim((string) $country));
        if ($country === '') {
            return true;
        }

        return in_array($country, ['india', 'in'], true);
    }
}

if (! function_exists('bms_normalize_tax_region_text')) {
    /**
     * Normalize free-form region text for stable comparisons.
     */
    function bms_normalize_tax_region_text(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return $value;
    }
}

if (! function_exists('bms_extract_gstin_state_code')) {
    /**
     * Extract the leading 2-digit GSTIN state code.
     */
    function bms_extract_gstin_state_code(?string $gstin): string
    {
        $gstin = strtoupper(trim((string) $gstin));
        if ($gstin === '') {
            return '';
        }

        $gstin = preg_replace('/\s+/', '', $gstin) ?? $gstin;

        return preg_match('/^(\d{2})/', $gstin, $m) === 1 ? (string) $m[1] : '';
    }
}

if (! function_exists('bms_india_state_alias_map')) {
    /**
     * Map common Indian state names/aliases to GST state codes.
     *
     * @return array<string, string>
     */
    function bms_india_state_alias_map(): array
    {
        static $map = null;
        if (is_array($map)) {
            return $map;
        }

        $map = [
            'jammu and kashmir' => '01',
            'jk' => '01',
            'himachal pradesh' => '02',
            'hp' => '02',
            'punjab' => '03',
            'pb' => '03',
            'chandigarh' => '04',
            'ch' => '04',
            'uttarakhand' => '05',
            'uttrakhand' => '05',
            'uttaranchal' => '05',
            'uk' => '05',
            'haryana' => '06',
            'hr' => '06',
            'delhi' => '07',
            'new delhi' => '07',
            'dl' => '07',
            'rajasthan' => '08',
            'rj' => '08',
            'uttar pradesh' => '09',
            'up' => '09',
            'bihar' => '10',
            'br' => '10',
            'sikkim' => '11',
            'sk' => '11',
            'arunachal pradesh' => '12',
            'arunachal' => '12',
            'ar' => '12',
            'nagaland' => '13',
            'nl' => '13',
            'manipur' => '14',
            'mn' => '14',
            'mizoram' => '15',
            'mz' => '15',
            'tripura' => '16',
            'tr' => '16',
            'meghalaya' => '17',
            'ml' => '17',
            'assam' => '18',
            'as' => '18',
            'west bengal' => '19',
            'wb' => '19',
            'jharkhand' => '20',
            'jh' => '20',
            'odisha' => '21',
            'orissa' => '21',
            'od' => '21',
            'chhattisgarh' => '22',
            'chattisgarh' => '22',
            'cg' => '22',
            'madhya pradesh' => '23',
            'mp' => '23',
            'gujarat' => '24',
            'gj' => '24',
            'dadra and nagar haveli and daman and diu' => '26',
            'dnhdd' => '26',
            'dnh and dd' => '26',
            'maharashtra' => '27',
            'mh' => '27',
            'karnataka' => '29',
            'ka' => '29',
            'goa' => '30',
            'ga' => '30',
            'lakshadweep' => '31',
            'ld' => '31',
            'kerala' => '32',
            'kl' => '32',
            'tamil nadu' => '33',
            'tamilnadu' => '33',
            'tn' => '33',
            'puducherry' => '34',
            'pondicherry' => '34',
            'py' => '34',
            'andaman and nicobar islands' => '35',
            'andaman nicobar' => '35',
            'an' => '35',
            'telangana' => '36',
            'ts' => '36',
            'andhra pradesh' => '37',
            'andhra' => '37',
            'ap' => '37',
            'ladakh' => '38',
            'la' => '38',
        ];

        return $map;
    }
}

if (! function_exists('bms_resolve_india_state_code')) {
    /**
     * Resolve an Indian state/UT into a GST state code using GSTIN first, then text.
     */
    function bms_resolve_india_state_code(?string $state, ?string $gstin = null): string
    {
        $gstCode = bms_extract_gstin_state_code($gstin);
        if ($gstCode !== '') {
            return $gstCode;
        }

        $normalized = bms_normalize_tax_region_text($state);
        if ($normalized === '') {
            return '';
        }

        $aliases = bms_india_state_alias_map();
        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        return preg_match('/^\d{2}$/', $normalized) === 1 ? $normalized : '';
    }
}

if (! function_exists('bms_resolve_gst_mode')) {
    /**
     * Resolve GST mode from authoritative company/client location data.
     *
     * @param array<string, mixed>|null $client
     * @param array<string, mixed>|null $companyInfo
     */
    function bms_resolve_gst_mode(?array $client, ?array $companyInfo = null, string $fallback = 'CGST_SGST'): string
    {
        $fallback = strtoupper(trim($fallback)) === 'IGST' ? 'IGST' : 'CGST_SGST';
        $client = is_array($client) ? $client : [];

        $clientCountry = (string) (($client['billing_country'] ?? '') ?: ($client['country'] ?? ''));
        if (! bms_is_india_country($clientCountry)) {
            return $fallback;
        }

        $companyInfo = is_array($companyInfo) ? $companyInfo : bms_company_info();

        $companyStateCode = bms_resolve_india_state_code(
            (string) ($companyInfo['state'] ?? ''),
            (string) ($companyInfo['gstin_number'] ?? '')
        );
        $clientStateCode = bms_resolve_india_state_code(
            (string) (($client['billing_state'] ?? '') ?: ($client['state'] ?? '')),
            (string) (($client['gst_no'] ?? '') ?: ($client['gstin_number'] ?? ''))
        );

        if ($companyStateCode !== '' && $clientStateCode !== '') {
            return $companyStateCode === $clientStateCode ? 'CGST_SGST' : 'IGST';
        }

        return $fallback;
    }
}
