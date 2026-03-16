<?php

namespace App\Controllers;

use App\Models\DailyExpenseModel;
use Throwable;

class DailyExpenseController extends BaseController
{
    public function receipt(int $id)
    {
        $model = new DailyExpenseModel();
        $row = $model->find($id);
        if (! $row) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $rel = trim((string) ($row['receipt_path'] ?? ''));
        if ($rel === '') {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        // Receipt paths are stored like: uploads/receipts/YYYYMM/filename.ext (relative to WRITEPATH)
        $rel = str_replace(['\\', "\0"], ['/', ''], $rel);
        if (! str_starts_with($rel, 'uploads/receipts/')) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $full = rtrim(WRITEPATH, "\\/") . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        $real = realpath($full);
        $base = realpath(rtrim(WRITEPATH, "\\/") . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'receipts');
        if (! $real || ! $base || strncmp($real, $base, strlen($base)) !== 0 || ! is_file($real)) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };

        $filename = basename($real);
        $inline = trim((string) ($this->request->getGet('download') ?? '')) !== '1';

        $this->response->setHeader('Content-Type', $mime);
        $this->response->setHeader(
            'Content-Disposition',
            ($inline ? 'inline' : 'attachment') . '; filename="' . addslashes($filename) . '"'
        );

        return $this->response->setBody((string) file_get_contents($real));
    }

    public function index()
    {
        return view('day_book/daily_expense_form', ['active' => 'day_book_form']);
    }

    public function create()
    {
        return view('day_book/daily_expense_create', ['active' => 'day_book_form']);
    }

    public function edit(int $id)
    {
        $row = (new DailyExpenseModel())->find($id);
        if (! $row) {
            return redirect()->to('/day-book/daily-expense-form')->with('error', 'Expense not found.');
        }

        return view('day_book/daily_expense_edit', [
            'active'  => 'day_book_form',
            'expense' => $row,
        ]);
    }

    public function list()
    {
        $rows = (new DailyExpenseModel())
            ->orderBy('expense_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->response->setJSON(['data' => $rows]);
    }

    private function genExpenseCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $rand = '';
        for ($i = 0; $i < 8; $i++) {
            $rand .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return 'PC-' . $rand;
    }

    public function save()
    {
        try {
            $id = (int) $this->request->getPost('id');
            $expenseDate = trim((string) $this->request->getPost('expense_date'));
            $category = trim((string) $this->request->getPost('category'));
            $description = trim((string) $this->request->getPost('description'));
            $remarks = trim((string) $this->request->getPost('remarks'));
            $amountRaw = (string) $this->request->getPost('amount');
            $amount = (float) str_replace(',', '', $amountRaw);
            $method = trim((string) $this->request->getPost('payment_method'));
            $paidTo = trim((string) $this->request->getPost('paid_to'));

            if ($expenseDate === '') {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Date is required.']);
            }
            if ($amount <= 0) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Amount must be greater than 0.']);
            }
            if ($method === '') {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Payment method is required.']);
            }

            $model = new DailyExpenseModel();

            $payload = [
                'expense_date'    => $expenseDate,
                'category'        => $category !== '' ? $category : null,
                'description'     => $description !== '' ? $description : null,
                'remarks'         => $remarks !== '' ? $remarks : null,
                'amount'          => number_format($amount, 2, '.', ''),
                'payment_method'  => $method !== '' ? $method : null,
                'paid_to'         => $paidTo !== '' ? $paidTo : null,
            ];

            if ($id > 0) {
                $row = $model->find($id);
                if (! $row) {
                    return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Expense not found.']);
                }
                $model->update($id, $payload);
                return $this->response->setJSON(['success' => true, 'message' => 'Expense updated.']);
            }

            // Create
            $code = $this->genExpenseCode();
            // Ensure uniqueness (best-effort)
            for ($tries = 0; $tries < 5; $tries++) {
                if (! $model->where('expense_code', $code)->first()) {
                    break;
                }
                $code = $this->genExpenseCode();
            }
            $payload['expense_code'] = $code;

            $newId = $model->insert($payload, true);
            if (! $newId) {
                $msg = $model->errors() ? implode(' ', $model->errors()) : 'Unable to save expense.';
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $msg]);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Expense saved.', 'id' => (int) $newId]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $expenseCode = trim((string) $this->request->getPost('expense_code'));
            $expenseDate = trim((string) $this->request->getPost('expense_date'));
            $category = trim((string) $this->request->getPost('category'));
            $description = trim((string) $this->request->getPost('description'));
            $remarks = trim((string) $this->request->getPost('remarks'));
            $amountRaw = (string) $this->request->getPost('amount');
            $amount = (float) str_replace(',', '', $amountRaw);
            $method = trim((string) $this->request->getPost('payment_method'));
            $paidTo = trim((string) $this->request->getPost('paid_to'));

            if ($expenseDate === '') {
                return redirect()->back()->withInput()->with('error', 'Date is required.');
            }
            if ($category === '') {
                return redirect()->back()->withInput()->with('error', 'Expense category is required.');
            }
            if ($amount <= 0) {
                return redirect()->back()->withInput()->with('error', 'Amount must be greater than 0.');
            }
            if ($method === '') {
                return redirect()->back()->withInput()->with('error', 'Payment method is required.');
            }
            if ($paidTo === '') {
                return redirect()->back()->withInput()->with('error', 'Paid To is required.');
            }

            $model = new DailyExpenseModel();

            if ($expenseCode === '') {
                $expenseCode = $this->genExpenseCode();
                for ($tries = 0; $tries < 5; $tries++) {
                    if (! $model->where('expense_code', $expenseCode)->first()) {
                        break;
                    }
                    $expenseCode = $this->genExpenseCode();
                }
            } else {
                if ($model->where('expense_code', $expenseCode)->first()) {
                    return redirect()->back()->withInput()->with('error', 'Expense ID already exists.');
                }
            }

            $receiptPath = null;
            $file = $this->request->getFile('receipt');
            if ($file && $file->isValid() && ! $file->hasMoved()) {
                $ext = strtolower((string) $file->getClientExtension());
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (! in_array($ext, $allowed, true)) {
                    return redirect()->back()->withInput()->with('error', 'Receipt must be PDF/JPG/PNG.');
                }
                if (($file->getSize() ?? 0) > (5 * 1024 * 1024)) {
                    return redirect()->back()->withInput()->with('error', 'Receipt max size is 5MB.');
                }

                $subdir = date('Ym');
                $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'receipts' . DIRECTORY_SEPARATOR . $subdir;
                if (! is_dir($targetDir)) {
                    @mkdir($targetDir, 0775, true);
                }
                $newName = $expenseCode . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                $file->move($targetDir, $newName);
                $receiptPath = 'uploads/receipts/' . $subdir . '/' . $newName;
            }

            $id = $model->insert([
                'expense_code'    => $expenseCode,
                'expense_date'    => $expenseDate,
                'category'        => $category,
                'description'     => $description !== '' ? $description : null,
                'remarks'         => $remarks !== '' ? $remarks : null,
                'amount'          => number_format($amount, 2, '.', ''),
                'payment_method'  => $method,
                'paid_to'         => $paidTo,
                'receipt_path'    => $receiptPath,
            ], true);

            if (! $id) {
                $msg = $model->errors() ? implode(' ', $model->errors()) : 'Unable to save expense.';
                return redirect()->back()->withInput()->with('error', $msg);
            }

            return redirect()->to('/day-book/daily-expense-form')->with('success', 'Expense saved.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(int $id)
    {
        try {
            $model = new DailyExpenseModel();
            $existing = $model->find($id);
            if (! $existing) {
                return redirect()->to('/day-book/daily-expense-form')->with('error', 'Expense not found.');
            }

            $expenseCodeInput = trim((string) $this->request->getPost('expense_code'));
            $expenseCodeInput = $expenseCodeInput !== '' ? $expenseCodeInput : (string) ($existing['expense_code'] ?? '');
            if ($expenseCodeInput === '') {
                $expenseCodeInput = 'PC-' . $id;
            }
            if (strlen($expenseCodeInput) > 30) {
                return redirect()->back()->withInput()->with('error', 'Expense ID must be 30 characters or less.');
            }
            $dup = $model->where('expense_code', $expenseCodeInput)->where('id !=', $id)->first();
            if ($dup) {
                return redirect()->back()->withInput()->with('error', 'Expense ID already exists.');
            }

            $expenseDate = trim((string) $this->request->getPost('expense_date'));
            $category = trim((string) $this->request->getPost('category'));
            $description = trim((string) $this->request->getPost('description'));
            $remarks = trim((string) $this->request->getPost('remarks'));
            $amountRaw = (string) $this->request->getPost('amount');
            $amount = (float) str_replace(',', '', $amountRaw);
            $method = trim((string) $this->request->getPost('payment_method'));
            $paidTo = trim((string) $this->request->getPost('paid_to'));

            if ($expenseDate === '') {
                return redirect()->back()->withInput()->with('error', 'Date is required.');
            }
            if ($category === '') {
                return redirect()->back()->withInput()->with('error', 'Expense category is required.');
            }
            if ($amount <= 0) {
                return redirect()->back()->withInput()->with('error', 'Amount must be greater than 0.');
            }
            if ($method === '') {
                return redirect()->back()->withInput()->with('error', 'Payment method is required.');
            }
            if ($paidTo === '') {
                return redirect()->back()->withInput()->with('error', 'Paid To is required.');
            }

            $receiptPath = (string) (($existing['receipt_path'] ?? '') ?: '');
            $file = $this->request->getFile('receipt');
            if ($file && $file->isValid() && ! $file->hasMoved()) {
                $ext = strtolower((string) $file->getClientExtension());
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (! in_array($ext, $allowed, true)) {
                    return redirect()->back()->withInput()->with('error', 'Receipt must be PDF/JPG/PNG.');
                }
                if (($file->getSize() ?? 0) > (5 * 1024 * 1024)) {
                    return redirect()->back()->withInput()->with('error', 'Receipt max size is 5MB.');
                }

                $subdir = date('Ym');
                $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'receipts' . DIRECTORY_SEPARATOR . $subdir;
                if (! is_dir($targetDir)) {
                    @mkdir($targetDir, 0775, true);
                }
                $newName = $expenseCodeInput . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                $file->move($targetDir, $newName);
                $receiptPath = 'uploads/receipts/' . $subdir . '/' . $newName;
            }

            $model->update($id, [
                'expense_code'    => $expenseCodeInput,
                'expense_date'    => $expenseDate,
                'category'        => $category,
                'description'     => $description !== '' ? $description : null,
                'remarks'         => $remarks !== '' ? $remarks : null,
                'amount'          => number_format($amount, 2, '.', ''),
                'payment_method'  => $method,
                'paid_to'         => $paidTo,
                'receipt_path'    => $receiptPath !== '' ? $receiptPath : null,
            ]);

            return redirect()->to('/day-book/daily-expense-form')->with('success', 'Expense updated.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $id = (int) $this->request->getPost('id');
            if ($id <= 0) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid expense.']);
            }
            $model = new DailyExpenseModel();
            $row = $model->find($id);
            if (! $row) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Expense not found.']);
            }
            $model->delete($id);
            return $this->response->setJSON(['success' => true, 'message' => 'Expense deleted.']);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
