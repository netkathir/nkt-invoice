<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Throwable;

class ConvertBillableItemDescriptionsToPlainText extends Migration
{
    private function toPlain(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $plain = '';

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
            } catch (Throwable $e) {
                $plain = html_entity_decode(strip_tags($maybeHtml), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
        } else {
            $plain = $value;
        }

        $plain = str_replace(["\r\n", "\r"], "\n", $plain);
        $lines = explode("\n", $plain);
        $out = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $line = preg_replace('/^[\\x{2022}\\x{2023}\\x{25E6}\\x{2043}\\x{2219}\\*\\-\\+]+\\s*/u', '', $line) ?? $line;
            $line = trim($line);
            if ($line !== '') {
                $out[] = $line;
            }
        }

        return implode("\n", $out);
    }

    public function up()
    {
        if (! $this->db->tableExists('billable_items')) {
            return;
        }

        // Convert any HTML stored descriptions (e.g., <ul><li>..</li></ul>) to plain text.
        $rows = $this->db->table('billable_items')
            ->select('id, description')
            ->like('description', '<', 'both')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) continue;
            $desc = (string) ($row['description'] ?? '');
            $plain = $this->toPlain($desc);
            $this->db->table('billable_items')->where('id', $id)->update(['description' => $plain]);
        }
    }

    public function down()
    {
        // no-op (cannot safely restore HTML)
    }
}
