<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class PaymentReportController extends BaseController
{
    private function computeInvoiceTotal(array $row): float
    {
        $sub = (float) ($row['total_amount'] ?? 0);
        $gst = (float) (($row['total_gst'] ?? null) ?? 0);
        $net = $row['net_amount'] ?? null;
        if ($net !== null && $net !== '') {
            return (float) $net;
        }
        return $sub + $gst;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchRows(?string $statusFilter, ?string $startDate = null, ?string $endDate = null): array
    {
        $db = db_connect();
        $builder = $db->table('proforma_invoices pi')
            ->select([
                'pi.id',
                'pi.proforma_number',
                'pi.proforma_date',
                'pi.billing_to',
                'pi.total_amount',
                'pi.total_gst',
                'pi.net_amount',
                'c.contact_person as customer_name',
                'c.name as company_name',
                'COALESCE(SUM(pp.amount), 0) as total_paid',
            ])
            ->join('clients c', 'c.id = pi.client_id', 'inner')
            ->join('proforma_payments pp', 'pp.proforma_id = pi.id', 'left');

        if ($startDate) {
            $builder->where('pi.proforma_date >=', $startDate);
        }
        if ($endDate) {
            $builder->where('pi.proforma_date <=', $endDate);
        }

        $rows = $builder->groupBy(['pi.id', 'pi.proforma_number', 'pi.proforma_date', 'pi.billing_to', 'pi.total_amount', 'pi.total_gst', 'pi.net_amount', 'c.contact_person', 'c.name'])
            ->orderBy('pi.id', 'DESC')
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $invoiceTotal = $this->computeInvoiceTotal($r);
            $paid = (float) ($r['total_paid'] ?? 0);
            $remaining = $invoiceTotal - $paid;
            if ($remaining < 0) {
                $remaining = 0.0;
            }

            $paymentStatus = 'Unpaid';
            if ($paid > 0 && $remaining > 0) {
                $paymentStatus = 'Partially Paid';
            }
            if ($remaining <= 0 && $paid > 0) {
                $paymentStatus = 'Fully Paid';
            }

            if ($statusFilter && $statusFilter !== 'All' && $paymentStatus !== $statusFilter) {
                continue;
            }

            $out[] = [
                'id'                => (int) ($r['id'] ?? 0),
                'invoice'           => (string) ($r['proforma_number'] ?? ''),
                'customer_name'     => (string) (($r['customer_name'] ?? '') ?: ($r['company_name'] ?? '')),
                'total_amount'      => number_format($invoiceTotal, 2, '.', ''),
                'due_date'          => (string) (($r['billing_to'] ?? '') ?: ''),
                'total_paid'        => number_format($paid, 2, '.', ''),
                'remaining_balance' => number_format($remaining, 2, '.', ''),
                'payment_status'    => $paymentStatus,
            ];
        }

        return $out;
    }

    public function index()
    {
        return view('payment_report/index', ['active' => 'payment_report']);
    }

    public function list()
    {
        $status = trim((string) ($this->request->getGet('payment_status') ?? ''));
        $startDate = trim((string) ($this->request->getGet('start_date') ?? ''));
        $endDate = trim((string) ($this->request->getGet('end_date') ?? ''));
        $rows = $this->fetchRows($status !== '' ? $status : null, $startDate !== '' ? $startDate : null, $endDate !== '' ? $endDate : null);
        return $this->response->setJSON(['data' => $rows]);
    }

    public function download()
    {
        $status = trim((string) ($this->request->getGet('payment_status') ?? ''));
        $startDate = trim((string) ($this->request->getGet('start_date') ?? ''));
        $endDate = trim((string) ($this->request->getGet('end_date') ?? ''));
        $rows = $this->fetchRows($status !== '' ? $status : null, $startDate !== '' ? $startDate : null, $endDate !== '' ? $endDate : null);

        $fh = fopen('php://temp', 'w+');
        fputcsv($fh, ['S.No', 'Invoice', 'Customer Name', 'Total Amount', 'Due Date', 'Total Paid', 'Remaining Balance', 'Payment Status']);
        $i = 1;
        foreach ($rows as $r) {
            fputcsv($fh, [
                $i++,
                (string) ($r['invoice'] ?? ''),
                (string) ($r['customer_name'] ?? ''),
                (string) ($r['total_amount'] ?? '0.00'),
                (string) ($r['due_date'] ?? ''),
                (string) ($r['total_paid'] ?? '0.00'),
                (string) ($r['remaining_balance'] ?? '0.00'),
                (string) ($r['payment_status'] ?? ''),
            ]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh) ?: '';
        fclose($fh);

        $filename = 'payment-report-' . date('Y-m-d') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }
}

