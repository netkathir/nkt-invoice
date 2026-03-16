<?php

namespace App\Libraries;

/**
 * Minimal PDF writer (standard fonts only).
 * Supports single/multi-page text + simple lines/rectangles.
 */
class SimplePdf
{
    private const A4_W_PT = 595.28;
    private const A4_H_PT = 841.89;

    /** @var array<int, string> */
    private array $pages = [];
    private int $currentPage = 0;

    private float $pageW = self::A4_W_PT;
    private float $pageH = self::A4_H_PT;

    private string $font = 'F1'; // F1 Helvetica, F2 Helvetica-Bold
    private float $fontSize = 12.0;
    /** @var array{0:float,1:float,2:float} */
    private array $textRgb = [0, 0, 0];
    /** @var array{0:float,1:float,2:float} */
    private array $drawRgb = [0, 0, 0];
    /** @var array{0:float,1:float,2:float} */
    private array $fillRgb = [1, 1, 1];
    private float $lineWidth = 1.0;

    public function addPage(): void
    {
        $this->pages[] = '';
        $this->currentPage = count($this->pages) - 1;
    }

    public function setFont(string $family = 'Helvetica', string $style = '', float $size = 12): void
    {
        $style = strtoupper(trim($style));
        $family = strtolower(trim($family));

        $isBold = str_contains($style, 'B');
        if ($family !== 'helvetica' && $family !== 'arial') {
            $isBold = $isBold; // keep style decision
        }

        $this->font = $isBold ? 'F2' : 'F1';
        $this->fontSize = max(6.0, $size);
    }

    public function setTextColor(int $r, int $g, int $b): void
    {
        $this->textRgb = [$r / 255, $g / 255, $b / 255];
    }

    public function setDrawColor(int $r, int $g, int $b): void
    {
        $this->drawRgb = [$r / 255, $g / 255, $b / 255];
    }

    public function setFillColor(int $r, int $g, int $b): void
    {
        $this->fillRgb = [$r / 255, $g / 255, $b / 255];
    }

    public function setLineWidth(float $pt): void
    {
        $this->lineWidth = max(0.1, $pt);
    }

    public function text(float $x, float $yFromTop, string $text): void
    {
        $this->ensurePage();
        $x = max(0, $x);
        $y = $this->toPdfY($yFromTop);
        $escaped = $this->escapeText($text);
        [$r, $g, $b] = $this->textRgb;

        $this->append(sprintf(
            "BT /%s %.2F Tf %.3F %.3F %.3F rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n",
            $this->font,
            $this->fontSize,
            $r,
            $g,
            $b,
            $x,
            $y,
            $escaped
        ));
    }

    public function line(float $x1, float $y1FromTop, float $x2, float $y2FromTop): void
    {
        $this->ensurePage();
        [$r, $g, $b] = $this->drawRgb;
        $this->append(sprintf("%.3F %.3F %.3F RG %.2F w\n", $r, $g, $b, $this->lineWidth));
        $this->append(sprintf("%.2F %.2F m %.2F %.2F l S\n", $x1, $this->toPdfY($y1FromTop), $x2, $this->toPdfY($y2FromTop)));
    }

    public function rect(float $x, float $yFromTop, float $w, float $h, bool $stroke = true, bool $fill = false): void
    {
        $this->ensurePage();
        [$sr, $sg, $sb] = $this->drawRgb;
        [$fr, $fg, $fb] = $this->fillRgb;
        $op = $stroke && $fill ? 'B' : ($fill ? 'f' : 'S');
        $yPdf = $this->toPdfY($yFromTop + $h);

        $this->append(sprintf("%.3F %.3F %.3F RG %.2F w\n", $sr, $sg, $sb, $this->lineWidth));
        $this->append(sprintf("%.3F %.3F %.3F rg\n", $fr, $fg, $fb));
        $this->append(sprintf("%.2F %.2F %.2F %.2F re %s\n", $x, $yPdf, $w, $h, $op));
    }

    /**
     * Very rough width estimate for standard fonts.
     */
    public function estimateTextWidth(string $text, ?float $fontSize = null): float
    {
        $fontSize = $fontSize ?? $this->fontSize;
        $len = mb_strlen($text);
        return $len * $fontSize * 0.52;
    }

    /**
     * Returns array of wrapped lines.
     *
     * @return string[]
     */
    public function wrapText(string $text, float $maxWidth, ?float $fontSize = null): array
    {
        $fontSize = $fontSize ?? $this->fontSize;
        $text = trim(preg_replace('/\\s+/u', ' ', $text) ?? '');
        if ($text === '') {
            return [''];
        }

        $words = preg_split('/\\s+/u', $text) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $w) {
            $candidate = $line === '' ? $w : ($line . ' ' . $w);
            if ($this->estimateTextWidth($candidate, $fontSize) <= $maxWidth) {
                $line = $candidate;
                continue;
            }
            if ($line !== '') {
                $lines[] = $line;
            }
            $line = $w;
        }

        if ($line !== '') {
            $lines[] = $line;
        }
        return $lines === [] ? [''] : $lines;
    }

    public function output(): string
    {
        if ($this->pages === []) {
            $this->addPage();
        }

        $objects = [];
        $offsets = [0];
        $pdf = "%PDF-1.3\n";

        $addObj = function (string $body) use (&$objects): int {
            $objects[] = $body;
            return count($objects);
        };
        $setObj = function (int $id, string $body) use (&$objects): void {
            $objects[$id - 1] = $body;
        };

        // Fonts (standard 14 fonts)
        $font1 = $addObj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
        $font2 = $addObj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");

        // Content streams
        $contentObjIds = [];
        foreach ($this->pages as $content) {
            $stream = $content;
            $contentObjIds[] = $addObj("<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream");
        }

        // Pages (placeholder) + page objects
        $pagesObjId = $addObj("<< /Type /Pages /Kids [] /Count 0 >>");

        $pageObjIds = [];
        foreach ($contentObjIds as $cid) {
            $pageBody = sprintf(
                "<< /Type /Page /Parent %d 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 %d 0 R /F2 %d 0 R >> >> /Contents %d 0 R >>",
                $pagesObjId,
                $this->pageW,
                $this->pageH,
                $font1,
                $font2,
                $cid
            );
            $pageObjIds[] = $addObj($pageBody);
        }

        $kids = '';
        foreach ($pageObjIds as $pid) {
            $kids .= $pid . " 0 R ";
        }
        $setObj($pagesObjId, "<< /Type /Pages /Kids [ " . trim($kids) . " ] /Count " . count($pageObjIds) . " >>");

        $catalog = $addObj("<< /Type /Catalog /Pages " . $pagesObjId . " 0 R >>");

        // Write objects with offsets
        foreach ($objects as $i => $body) {
            $offsets[$i + 1] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root " . $catalog . " 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

        return $pdf;
    }

    private function ensurePage(): void
    {
        if ($this->pages === []) {
            $this->addPage();
        }
    }

    private function append(string $cmd): void
    {
        $this->pages[$this->currentPage] .= $cmd;
    }

    private function toPdfY(float $yFromTop): float
    {
        return max(0.0, $this->pageH - $yFromTop);
    }

    private function escapeText(string $text): string
    {
        // Convert UTF-8 to Windows-1252 (WinAnsi) for standard PDF fonts.
        // (Characters not representable will be dropped/transliterated.)
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        } else {
            $text = preg_replace('/[^\\x09\\x0A\\x0D\\x20-\\x7E]/', '', $text) ?? $text;
        }

        $text = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);
        // Replace newlines/tabs with spaces for this primitive writer
        $text = preg_replace("/[\\r\\n\\t]+/u", ' ', $text) ?? $text;
        return $text;
    }
}
