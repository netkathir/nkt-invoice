<?php

namespace App\Controllers;

use App\Models\ProformaModel;
use App\Models\ProformaPaymentModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class PaymentsController extends BaseController
{
    private function computeInvoiceTotal(array $proforma): float
    {
        $sub = (float) ($proforma['total_amount'] ?? 0);
        $gst = (float) (($proforma['total_gst'] ?? null) ?? 0);
        $net = $proforma['net_amount'] ?? null;
        if ($net !== null && $net !== '') {
            return (float) $net;
        }
        return $sub + $gst;
    }

    public function index()
    {
        return view('payments/index', ['active' => 'payments']);
    }

    public function list()
    {
        $db = db_connect();
        $builder = $db->table('proforma_invoices pi')
            ->select([
                'pi.id',
                'pi.proforma_number',
                'pi.client_id',
                'pi.total_amount',
                'pi.total_gst',
                'pi.net_amount',
                'c.contact_person as customer_name',
                'c.name as company_name',
                'COALESCE(SUM(pp.amount), 0) as total_paid',
            ])
            ->join('clients c', 'c.id = pi.client_id', 'inner')
            ->join('proforma_payments pp', 'pp.proforma_id = pi.id', 'left')
            ->groupBy('pi.id')
            ->orderBy('pi.id', 'DESC');

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$r) {
            $invoiceTotal = $this->computeInvoiceTotal($r);
            $paid = (float) ($r['total_paid'] ?? 0);
            $remaining = $invoiceTotal - $paid;
            if ($remaining < 0) {
                $remaining = 0.0;
            }

            $status = 'Unpaid';
            if ($paid > 0 && $remaining > 0) {
                $status = 'Partially Paid';
            }
            if ($remaining <= 0 && $paid > 0) {
                $status = 'Fully Paid';
            }

            $r['invoice_total'] = number_format($invoiceTotal, 2, '.', '');
            $r['total_paid'] = number_format($paid, 2, '.', '');
            $r['remaining_balance'] = number_format($remaining, 2, '.', '');
            $r['payment_status'] = $status;
        }
        unset($r);

        return $this->response->setJSON(['data' => $rows]);
    }

    public function invoiceOptions()
    {
        $rows = (new ProformaModel())
            ->select('proforma_invoices.id, proforma_invoices.proforma_number, proforma_invoices.client_id, proforma_invoices.billing_to, proforma_invoices.total_amount, proforma_invoices.total_gst, proforma_invoices.net_amount, clients.contact_person as customer_name, clients.name as company_name')
            ->join('clients', 'clients.id = proforma_invoices.client_id')
            ->orderBy('proforma_invoices.id', 'DESC')
            ->findAll();

        foreach ($rows as &$r) {
            $r['invoice_total'] = number_format($this->computeInvoiceTotal($r), 2, '.', '');
        }
        unset($r);

        return $this->response->setJSON(['data' => $rows]);
    }

    public function customers()
    {
        $db = db_connect();
        // Select only columns that exist in the base schema (avoid migration-dependent columns).
        // Use GROUP BY on all selected columns to be compatible with ONLY_FULL_GROUP_BY.
        $rows = $db->table('proforma_invoices pi')
            ->select('c.id as client_id, c.contact_person, c.name')
            ->join('clients c', 'c.id = pi.client_id', 'inner')
            ->groupBy(['c.id', 'c.contact_person', 'c.name'])
            ->orderBy('c.contact_person', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$r) {
            $customer = trim((string) ($r['contact_person'] ?? ''));
            $company = trim((string) ($r['name'] ?? ''));
            $label = $customer !== '' ? $customer : ($company !== '' ? $company : ('Client #' . (string) ($r['client_id'] ?? '')));
            $r['label'] = $label;
        }
        unset($r);

        return $this->response->setJSON(['data' => $rows]);
    }

    public function invoicesByCustomer(int $clientId)
    {
        if ($clientId <= 0) {
            return $this->response->setJSON(['data' => []]);
        }

        $db = db_connect();
        $rows = $db->table('proforma_invoices pi')
            ->select([
                'pi.id',
                'pi.proforma_number',
                'pi.client_id',
                'pi.proforma_date',
                'pi.billing_to',
                'pi.total_amount',
                'pi.total_gst',
                'pi.net_amount',
                'COALESCE(SUM(pp.amount), 0) as total_paid',
            ])
            ->join('proforma_payments pp', 'pp.proforma_id = pi.id', 'left')
            ->where('pi.client_id', $clientId)
            ->groupBy(['pi.id', 'pi.proforma_number', 'pi.client_id', 'pi.proforma_date', 'pi.billing_to', 'pi.total_amount', 'pi.total_gst', 'pi.net_amount'])
            ->orderBy('pi.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$r) {
            $invoiceTotal = $this->computeInvoiceTotal($r);
            $paid = (float) ($r['total_paid'] ?? 0);
            $remaining = $invoiceTotal - $paid;
            if ($remaining < 0) {
                $remaining = 0.0;
            }

            $status = 'Unpaid';
            if ($paid > 0 && $remaining > 0) {
                $status = 'Partially Paid';
            }
            if ($remaining <= 0 && $paid > 0) {
                $status = 'Fully Paid';
            }

            $r['invoice_total'] = number_format($invoiceTotal, 2, '.', '');
            $r['total_paid'] = number_format($paid, 2, '.', '');
            $r['remaining_balance'] = number_format($remaining, 2, '.', '');
            $r['payment_status'] = $status;
        }
        unset($r);

        return $this->response->setJSON(['data' => $rows]);
    }

    public function invoice(int $proformaId)
    {
        $db = db_connect();

        $inv = $db->table('proforma_invoices pi')
            ->select('pi.id, pi.proforma_number, pi.client_id, pi.proforma_date, pi.billing_to, pi.total_amount, pi.total_gst, pi.net_amount, c.contact_person as customer_name, c.name as company_name')
            ->join('clients c', 'c.id = pi.client_id', 'inner')
            ->where('pi.id', $proformaId)
            ->get()
            ->getRowArray();

        if (! $inv) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Invoice not found.']);
        }

        $payments = (new ProformaPaymentModel())
            ->where('proforma_id', $proformaId)
            ->orderBy('payment_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $invoiceTotal = $this->computeInvoiceTotal($inv);
        $paid = 0.0;
        foreach ($payments as $p) {
            $paid += (float) ($p['amount'] ?? 0);
        }
        $remaining = $invoiceTotal - $paid;
        if ($remaining < 0) {
            $remaining = 0.0;
        }

        $status = 'Unpaid';
        if ($paid > 0 && $remaining > 0) {
            $status = 'Partially Paid';
        }
        if ($remaining <= 0 && $paid > 0) {
            $status = 'Fully Paid';
        }

        return $this->response->setJSON([
            'success' => true,
            'invoice' => [
                'id'              => (int) $inv['id'],
                'client_id'       => (int) ($inv['client_id'] ?? 0),
                'invoice_no'      => (string) ($inv['proforma_number'] ?? ''),
                'customer_name'   => (string) (($inv['customer_name'] ?? '') ?: ($inv['company_name'] ?? '')),
                'invoice_date'    => (string) (($inv['proforma_date'] ?? '') ?: ''),
                'due_date'        => (string) (($inv['billing_to'] ?? '') ?: ''),
                'invoice_total'   => number_format($invoiceTotal, 2, '.', ''),
                'total_paid'      => number_format($paid, 2, '.', ''),
                'remaining'       => number_format($remaining, 2, '.', ''),
                'payment_status'  => $status,
            ],
            'payments' => $payments,
        ]);
    }

    public function save()
    {
        try {
            $proformaId = (int) $this->request->getPost('proforma_id');
            $paymentDate = trim((string) $this->request->getPost('payment_date'));
            $mode = trim((string) $this->request->getPost('payment_mode'));
            $amount = (float) $this->request->getPost('amount');
            $ref = trim((string) $this->request->getPost('reference_number'));
            $remarks = trim((string) $this->request->getPost('remarks'));

            if ($proformaId <= 0) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invoice is required.']);
            }
            if ($paymentDate === '') {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Payment date is required.']);
            }
            if ($amount <= 0) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Amount must be greater than 0.']);
            }

            $proforma = (new ProformaModel())->find($proformaId);
            if (! $proforma) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Invoice not found.']);
            }

            $model = new ProformaPaymentModel();
            $id = $model->insert([
                'proforma_id'      => $proformaId,
                'client_id'        => (int) ($proforma['client_id'] ?? 0),
                'payment_date'     => $paymentDate,
                'payment_mode'     => $mode !== '' ? $mode : null,
                'amount'           => number_format($amount, 2, '.', ''),
                'reference_number' => $ref !== '' ? $ref : null,
                'remarks'          => $remarks !== '' ? $remarks : null,
            ], true);

            if (! $id) {
                $msg = $model->errors() ? implode(' ', $model->errors()) : 'Unable to save payment.';
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $msg]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment saved.',
                'id'      => (int) $id,
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function view(int $proformaId)
    {
        $db = db_connect();

        $inv = $db->table('proforma_invoices pi')
            ->select('pi.id, pi.proforma_number, pi.client_id, pi.proforma_date, pi.billing_to, pi.total_amount, pi.total_gst, pi.net_amount, c.contact_person as customer_name, c.name as company_name')
            ->join('clients c', 'c.id = pi.client_id', 'inner')
            ->where('pi.id', $proformaId)
            ->get()
            ->getRowArray();

        if (! $inv) {
            return redirect()->to('/payments')->with('error', 'Invoice not found.');
        }

        $payments = (new ProformaPaymentModel())
            ->where('proforma_id', $proformaId)
            ->orderBy('payment_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $invoiceTotal = $this->computeInvoiceTotal($inv);
        $paid = 0.0;
        foreach ($payments as $p) {
            $paid += (float) ($p['amount'] ?? 0);
        }
        $remaining = $invoiceTotal - $paid;
        if ($remaining < 0) {
            $remaining = 0.0;
        }

        $status = 'Unpaid';
        if ($paid > 0 && $remaining > 0) {
            $status = 'Partially Paid';
        }
        if ($remaining <= 0 && $paid > 0) {
            $status = 'Fully Paid';
        }

        return view('payments/view', [
            'active'        => 'payments',
            'invoice'       => [
                'id'             => (int) $inv['id'],
                'invoice_no'     => (string) ($inv['proforma_number'] ?? ''),
                'customer_name'  => (string) (($inv['customer_name'] ?? '') ?: ($inv['company_name'] ?? '')),
                'invoice_date'   => (string) (($inv['proforma_date'] ?? '') ?: ''),
                'due_date'       => (string) (($inv['billing_to'] ?? '') ?: ''),
                'invoice_total'  => number_format($invoiceTotal, 2, '.', ''),
                'total_paid'     => number_format($paid, 2, '.', ''),
                'remaining'      => number_format($remaining, 2, '.', ''),
                'payment_status' => $status,
            ],
            'payments'      => $payments,
        ]);
    }
}
