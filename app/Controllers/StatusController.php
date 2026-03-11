<?php

namespace App\Controllers;

use App\Models\BillableItemModel;
use App\Models\ClientModel;
use App\Models\ProformaModel;
use Throwable;

class StatusController extends BaseController
{
    public function updateStatus()
    {
        $table = trim((string) $this->request->getPost('table_name'));
        $id = (int) $this->request->getPost('record_id');
        $status = trim((string) $this->request->getPost('status_value'));

        if ($id <= 0 || $table === '' || $status === '') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        try {
            switch ($table) {
                case 'clients': {
                    if (! in_array($status, [ClientModel::STATUS_ACTIVE, ClientModel::STATUS_INACTIVE], true)) {
                        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid status.']);
                    }
                    (new ClientModel())->update($id, ['status' => $status]);
                    break;
                }
                case 'billable_items': {
                    if (! in_array($status, [BillableItemModel::STATUS_PENDING, BillableItemModel::STATUS_BILLED], true)) {
                        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid status.']);
                    }
                    (new BillableItemModel())->update($id, ['status' => $status]);
                    break;
                }
                case 'proforma_invoices': {
                    if (! in_array($status, [ProformaModel::STATUS_DRAFT, ProformaModel::STATUS_POSTED], true)) {
                        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid status.']);
                    }
                    (new ProformaModel())->update($id, ['status' => $status]);
                    break;
                }
                default:
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Table not allowed.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Status updated successfully.']);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
