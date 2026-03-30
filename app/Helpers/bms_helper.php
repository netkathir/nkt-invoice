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
