<?php

namespace App\Controllers;

use App\Models\CompanyInformationModel;

class CompanyInformationController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        if (! $db->tableExists('company_information')) {
            return view('masters/company_information/index', [
                'active' => 'company_information',
                'company' => [],
                'errors' => [],
                'migrationNeeded' => true,
            ]);
        }

        $company = (new CompanyInformationModel())->orderBy('id', 'ASC')->first();

        return view('masters/company_information/index', [
            'active' => 'company_information',
            'company' => $company ?: [],
            'errors' => session()->getFlashdata('errors') ?? [],
            'migrationNeeded' => false,
        ]);
    }

    public function save()
    {
        $db = db_connect();
        if (! $db->tableExists('company_information')) {
            return redirect()
                ->to(base_url('company-information'))
                ->with('error', 'Company Information table is missing. Please run the latest migration first.');
        }

        $model = new CompanyInformationModel();
        $existing = $model->orderBy('id', 'ASC')->first() ?: [];
        $id = (int) ($existing['id'] ?? 0);

        $payload = [
            'company_name' => trim((string) $this->request->getPost('company_name')),
            'address_line1' => trim((string) $this->request->getPost('address_line1')),
            'address_line2' => trim((string) $this->request->getPost('address_line2')),
            'city' => trim((string) $this->request->getPost('city')),
            'state' => trim((string) $this->request->getPost('state')),
            'pincode' => trim((string) $this->request->getPost('pincode')),
            'gstin_number' => strtoupper(trim((string) $this->request->getPost('gstin_number'))),
            'export_tax_reference' => trim((string) $this->request->getPost('export_tax_reference')),
            'email_id' => trim((string) $this->request->getPost('email_id')),
            'website' => trim((string) $this->request->getPost('website')),
            'phone_number' => trim((string) $this->request->getPost('phone_number')),
            'current_account_details' => trim((string) $this->request->getPost('current_account_details')),
            'paypal_account' => trim((string) $this->request->getPost('paypal_account')),
            'logo_path' => (string) ($existing['logo_path'] ?? ''),
        ];

        if (! $model->validate($payload)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $logo = $this->request->getFile('company_logo');
        if ($logo && $logo->isValid() && ! $logo->hasMoved()) {
            $ext = strtolower((string) $logo->getClientExtension());
            if (! in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
                return redirect()->back()->withInput()->with('errors', ['company_logo' => 'Company Logo must be a PNG or JPEG file.']);
            }
            if (($logo->getSize() ?? 0) > (1024 * 1024)) {
                return redirect()->back()->withInput()->with('errors', ['company_logo' => 'Company Logo must be 1MB or smaller.']);
            }

            $targetDir = rtrim(FCPATH, "\\/") . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'company';
            if (! is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }

            $newName = 'company-logo-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $logo->move($targetDir, $newName, true);
            $payload['logo_path'] = 'uploads/company/' . $newName;

            $oldLogo = trim((string) ($existing['logo_path'] ?? ''));
            if ($oldLogo !== '') {
                $oldFile = realpath(rtrim(FCPATH, "\\/") . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $oldLogo));
                $uploadsBase = realpath($targetDir);
                if ($oldFile && $uploadsBase && str_starts_with($oldFile, $uploadsBase) && is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }
        }

        if ($id > 0) {
            $payload['id'] = $id;
        }

        if (! $model->save($payload)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to(base_url('company-information'))->with('success', 'Company information saved.');
    }
}
