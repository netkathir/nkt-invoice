<?php

namespace App\Controllers;

use App\Models\ClientModel;
use Throwable;

class ClientMasterController extends BaseController
{
    public function index()
    {
        return view('masters/client_master/list', ['active' => 'client_master']);
    }

    public function list()
    {
        $clients = (new ClientModel())
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->response->setJSON(['data' => $clients]);
    }

    public function save()
    {
        $clientModel = new ClientModel();

        $id = (int) $this->request->getPost('id');
        $payload = [
            'name'           => trim((string) $this->request->getPost('name')),
            'contact_person' => trim((string) $this->request->getPost('contact_person')),
            'email'          => trim((string) $this->request->getPost('email')),
            'phone'          => trim((string) $this->request->getPost('phone')),
            'address'        => trim((string) $this->request->getPost('address')),
            'billing_address'=> trim((string) $this->request->getPost('billing_address')),
            'city'           => trim((string) $this->request->getPost('city')),
            'state'          => trim((string) $this->request->getPost('state')),
            'country'        => trim((string) $this->request->getPost('country')),
            'postal_code'    => trim((string) $this->request->getPost('postal_code')),
        ];

        if ($id > 0) {
            $payload['id'] = $id;
        } else {
            $payload['status'] = ClientModel::STATUS_ACTIVE;
        }

        try {
            if (! $clientModel->save($payload)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please fix the validation errors.',
                    'errors'  => $clientModel->errors(),
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $id > 0 ? 'Client updated.' : 'Client created.',
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete()
    {
        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid client.']);
        }

        try {
            (new ClientModel())->delete($id);
            return $this->response->setJSON(['success' => true, 'message' => 'Client deleted.']);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
