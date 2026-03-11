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
                    $t = trim(html_entity_decode((string) $li->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                    if ($t !== '') {
                        $items[] = $t;
                    }
                }

                if ($items !== []) {
                    $plain = implode("\n", $items);
                } else {
                    $plain = html_entity_decode(strip_tags($maybeHtml), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
