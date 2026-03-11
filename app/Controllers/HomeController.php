<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index()
    {
        if (session()->get('admin_id')) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('public/home', [
            'title' => 'Billing Management System',
        ]);
    }
}

