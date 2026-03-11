<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//$routes->get('/', 'HomeController::index');
$routes->get('/', function () {
    return redirect()->to('/admin/login');
});

// Authentication (public)
$routes->get('admin/login', 'AuthController::login');
$routes->post('admin/login', 'AuthController::loginPost');
$routes->get('admin/logout', 'AuthController::logout');

// Protected application routes (admin only)
$routes->group('', ['filter' => 'adminauth'], static function (RouteCollection $routes): void {
    $routes->group('dashboard', static function (RouteCollection $routes): void {
        $routes->get('/', 'DashboardController::index');
        $routes->get('recent-billable-items', 'DashboardController::recentBillableItems');
    });

    $routes->group('masters', static function (RouteCollection $routes): void {
        $routes->group('client-master', static function (RouteCollection $routes): void {
            $routes->get('/', 'ClientMasterController::index');
            $routes->get('list', 'ClientMasterController::list');
            $routes->post('save', 'ClientMasterController::save');
            $routes->post('delete', 'ClientMasterController::delete');
        });
    });

    // Backward-compatible routes (old Clients module URLs)
    $routes->group('clients', static function (RouteCollection $routes): void {
        $routes->get('/', 'ClientMasterController::index');
        $routes->get('list', 'ClientMasterController::list');
        $routes->post('save', 'ClientMasterController::save');
        $routes->post('delete', 'ClientMasterController::delete');
    });

    $routes->group('billable-items', static function (RouteCollection $routes): void {
        $routes->get('/', 'BillableItemsController::index');
        $routes->get('list', 'BillableItemsController::list');
        $routes->post('save', 'BillableItemsController::save');
        $routes->post('update', 'BillableItemsController::update');
        $routes->post('delete', 'BillableItemsController::delete');
        $routes->post('generate-proforma', 'BillableItemsController::generateProforma');
        $routes->post('mark-billed', 'BillableItemsController::markBilled');
    });

    // Optional diagnostic route (safe for shared hosting).
    $routes->get('dbtest', 'Home::dbtest');

    // Generic status update endpoint (allowlisted tables only).
    $routes->post('update-status', 'StatusController::updateStatus');
});
