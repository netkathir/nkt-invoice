<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class RbacReadyFilter implements FilterInterface
{
    /**
     * @return list<string>
     */
    private function missingTables(): array
    {
        $db = db_connect();

        $required = ['admins', 'roles', 'permissions', 'admin_roles', 'role_permissions'];
        $missing = [];
        foreach ($required as $t) {
            if (! $db->tableExists($t)) {
                $missing[] = $t;
            }
        }

        return $missing;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            $missing = $this->missingTables();
        } catch (Throwable $e) {
            return;
        }

        if ($missing === []) {
            return;
        }

        $message = 'Access control tables are missing (' . implode(', ', $missing) . '). Run: php spark migrate --force';

        if ($request->isAJAX()) {
            $res = service('response');

            // DataTables GET endpoints should return 200 with an empty dataset to avoid "Ajax error" warnings.
            if (strtolower($request->getMethod()) === 'get') {
                return $res->setJSON([
                    'success' => false,
                    'message' => $message,
                    'data'    => [],
                ]);
            }

            return $res->setStatusCode(503)->setJSON([
                'success' => false,
                'message' => $message,
            ]);
        }

        service('session')->setFlashdata('error', $message);
        return redirect()->to(base_url('dashboard'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}

