<?php

namespace App\Filters;

use App\Libraries\Authz;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $permissionKey = '';
        if (is_array($arguments) && isset($arguments[0])) {
            $permissionKey = trim((string) $arguments[0]);
        }

        if ($permissionKey === '') {
            return;
        }

        $authz = new Authz(db_connect(), service('session'));
        if ($authz->can($permissionKey)) {
            return;
        }

        if ($request->isAJAX()) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        service('session')->setFlashdata('error', 'You do not have permission to access this page.');
        return redirect()->to(base_url('dashboard'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}

