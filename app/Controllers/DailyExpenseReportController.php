<?php

namespace App\Controllers;

use App\Libraries\SimplePdf;
use App\Models\DailyExpenseModel;

class DailyExpenseReportController extends BaseController
{
    private function isoToDmy(?string $iso): string
    {
        $raw = trim((string) ($iso ?? ''));
        $raw = substr($raw, 0, 10);
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m)) {
            return $raw;
        }
        return $m[3] . '/' . $m[2] . '/' . $m[1];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function filteredRows(?string $start, ?string $end, ?string $category): array
    {
        $m = new DailyExpenseModel();
        if ($start) {
            $m->where('expense_date >=', $start);
        }
        if ($end) {
            $m->where('expense_date <=', $end);
        }
        if ($category && $category !== 'All') {
            $m->where('category', $category);
        }
        return $m->orderBy('expense_date', 'DESC')->orderBy('id', 'DESC')->findAll();
    }

    public function index()
    {
        return view('day_book/daily_expense_report', ['active' => 'day_book_report']);
    }

    public function categories()
    {
        $db = db_connect();
        $rows = $db->table('daily_expenses')
            ->select('category')
            ->where('category IS NOT NULL')
            ->where("TRIM(category) !=", '')
            ->groupBy('category')
            ->orderBy('category', 'ASC')
            ->get()
            ->getResultArray();

        $cats = array_values(array_filter(array_map(static fn ($r) => (string) ($r['category'] ?? ''), $rows)));
        return $this->response->setJSON(['data' => $cats]);
    }

    public function data()
    {
        $start = trim((string) ($this->request->getGet('start_date') ?? ''));
        $end = trim((string) ($this->request->getGet('end_date') ?? ''));
        $category = trim((string) ($this->request->getGet('category') ?? ''));

        $rows = $this->filteredRows($start !== '' ? $start : null, $end !== '' ? $end : null, $category !== '' ? $category : null);

        $totalEntries = count($rows);
        $totalAmount = 0.0;
        $byCategory = [];
        foreach ($rows as $r) {
            $amt = (float) ($r['amount'] ?? 0);
            $totalAmount += $amt;
            $cat = trim((string) ($r['category'] ?? 'Uncategorized'));
            if ($cat === '') $cat = 'Uncategorized';
            if (! isset($byCategory[$cat])) {
                $byCategory[$cat] = ['category' => $cat, 'count' => 0, 'total_amount' => 0.0];
            }
            $byCategory[$cat]['count'] += 1;
            $byCategory[$cat]['total_amount'] += $amt;
        }

        $cats = array_values($byCategory);
        usort($cats, static fn ($a, $b) => strcmp((string) $a['category'], (string) $b['category']));
        foreach ($cats as &$c) {
            $c['total_amount'] = number_format((float) $c['total_amount'], 2, '.', '');
        }
        unset($c);

        $details = [];
        foreach ($rows as $r) {
            $details[] = [
                'expense_code'    => (string) ($r['expense_code'] ?? ''),
                'expense_date'    => (string) ($r['expense_date'] ?? ''),
                'category'        => (string) (($r['category'] ?? '') ?: '-'),
                'description'     => (string) (($r['description'] ?? '') ?: '-'),
                'amount'          => number_format((float) ($r['amount'] ?? 0), 2, '.', ''),
                'payment_method'  => (string) (($r['payment_method'] ?? '') ?: '-'),
                'paid_to'         => (string) (($r['paid_to'] ?? '') ?: '-'),
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'summary' => [
                'total_entries' => $totalEntries,
                'total_amount'  => number_format($totalAmount, 2, '.', ''),
                'categories'    => count($byCategory),
            ],
            'by_category' => $cats,
            'details'     => $details,
        ]);
    }

    public function exportCsv()
    {
        $start = trim((string) ($this->request->getGet('start_date') ?? ''));
        $end = trim((string) ($this->request->getGet('end_date') ?? ''));
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        $rows = $this->filteredRows($start !== '' ? $start : null, $end !== '' ? $end : null, $category !== '' ? $category : null);

        $fh = fopen('php://temp', 'w+');
        fputcsv($fh, ['S.No', 'Expense ID', 'Date', 'Category', 'Description', 'Amount', 'Payment Method', 'Paid To']);
        $i = 1;
        foreach ($rows as $r) {
            fputcsv($fh, [
                $i++,
                (string) ($r['expense_code'] ?? ''),
                $this->isoToDmy((string) ($r['expense_date'] ?? '')),
                (string) (($r['category'] ?? '') ?: ''),
                (string) (($r['description'] ?? '') ?: ''),
                number_format((float) ($r['amount'] ?? 0), 2, '.', ''),
                (string) (($r['payment_method'] ?? '') ?: ''),
                (string) (($r['paid_to'] ?? '') ?: ''),
            ]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh) ?: '';
        fclose($fh);

        $filename = 'daily-expense-report-' . date('Y-m-d') . '.csv';
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    public function exportPdf()
    {
        $start = trim((string) ($this->request->getGet('start_date') ?? ''));
        $end = trim((string) ($this->request->getGet('end_date') ?? ''));
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        $rows = $this->filteredRows($start !== '' ? $start : null, $end !== '' ? $end : null, $category !== '' ? $category : null);

        $pdf = new SimplePdf();
        $pdf->addPage();
        $pdf->setFont('Helvetica', 'B', 16);
        $pdf->text(40, 48, 'Daily Expense Report');

        $pdf->setFont('Helvetica', '', 10);
        $subtitle = 'Generated: ' . date('d/m/Y');
        $pdf->text(40, 66, $subtitle);

        $y = 92.0;
        $pdf->setFont('Helvetica', 'B', 10);
        $pdf->text(40, $y, 'S.No');
        $pdf->text(75, $y, 'Expense ID');
        $pdf->text(170, $y, 'Date');
        $pdf->text(240, $y, 'Category');
        $pdf->text(410, $y, 'Amount');
        $y += 12;
        $pdf->setDrawColor(180, 180, 180);
        $pdf->line(40, $y, 555, $y);
        $y += 14;

        $pdf->setFont('Helvetica', '', 9);
        $i = 1;
        $total = 0.0;
        foreach ($rows as $r) {
            if ($y > 780) {
                $pdf->addPage();
                $y = 60;
            }
            $amt = (float) ($r['amount'] ?? 0);
            $total += $amt;
            $pdf->text(40, $y, (string) $i);
            $pdf->text(75, $y, (string) ($r['expense_code'] ?? ''));
            $pdf->text(170, $y, $this->isoToDmy((string) ($r['expense_date'] ?? '')));
            $pdf->text(240, $y, (string) (($r['category'] ?? '') ?: '-'));
            $amtTxt = number_format($amt, 2, '.', '');
            $pdf->text(555 - $pdf->estimateTextWidth($amtTxt, 9), $y, $amtTxt);
            $y += 14;
            $i++;
        }

        $y += 6;
        $pdf->setDrawColor(180, 180, 180);
        $pdf->line(40, $y, 555, $y);
        $y += 16;
        $pdf->setFont('Helvetica', 'B', 10);
        $totTxt = number_format($total, 2, '.', '');
        $pdf->text(410, $y, 'Total:');
        $pdf->text(555 - $pdf->estimateTextWidth($totTxt, 10), $y, $totTxt);

        $bin = $pdf->output();
        $filename = 'daily-expense-report-' . date('Y-m-d') . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($bin);
    }
}
