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
$routes->get('admin/forgot-password', 'PasswordResetController::request');
$routes->post('admin/forgot-password', 'PasswordResetController::requestPost');
$routes->get('reset-password/(:segment)', 'PasswordResetController::reset/$1');
$routes->post('reset-password/(:segment)', 'PasswordResetController::resetPost/$1');
$routes->get('admin/logout', 'AuthController::logout');

// Protected application routes (admin only)
$routes->group('', ['filter' => 'adminauth'], static function (RouteCollection $routes): void {
    $routes->group('dashboard', static function (RouteCollection $routes): void {
        $routes->get('/', 'DashboardController::index');
        $routes->get('metrics', 'DashboardController::metrics');
        $routes->get('pending-list', 'DashboardController::pendingList');
        $routes->get('recent-billed-list', 'DashboardController::recentBilledList');
        $routes->get('client-billing-summary', 'DashboardController::clientBillingSummary');
    });

    // Access Control (Roles & Permissions)
    $routes->group('roles', ['filter' => 'rbac'], static function (RouteCollection $routes): void {
        $routes->get('/', 'RolesController::index', ['filter' => 'perm:roles.view']);
        $routes->get('list', 'RolesController::list', ['filter' => 'perm:roles.view']);
        $routes->post('save', 'RolesController::save');
        $routes->post('delete', 'RolesController::delete');
        $routes->get('(:num)/permissions', 'RolesController::permissions/$1', ['filter' => 'perm:roles.assign_perms']);
        $routes->post('(:num)/permissions', 'RolesController::savePermissions/$1', ['filter' => 'perm:roles.assign_perms']);
    });

    $routes->group('users', ['filter' => 'rbac'], static function (RouteCollection $routes): void {
        $routes->get('/', 'UsersController::index', ['filter' => 'perm:users.view']);
        $routes->get('list', 'UsersController::list', ['filter' => 'perm:users.view']);
        $routes->get('(:num)', 'UsersController::get/$1', ['filter' => 'perm:users.view']);
        $routes->post('save', 'UsersController::save');
        $routes->post('delete', 'UsersController::delete', ['filter' => 'perm:users.delete']);
    });

    $routes->group('permissions', ['filter' => 'rbac'], static function (RouteCollection $routes): void {
        $routes->get('/', 'PermissionsController::index', ['filter' => 'perm:permissions.view']);
        $routes->get('list', 'PermissionsController::list', ['filter' => 'perm:permissions.view']);
        $routes->post('save', 'PermissionsController::save');
        $routes->post('delete', 'PermissionsController::delete');
    });

    $routes->group('role-permissions', ['filter' => 'rbac'], static function (RouteCollection $routes): void {
        $routes->get('/', 'RolePermissionsController::index', ['filter' => 'perm:roles.assign_perms']);
        $routes->get('list', 'RolePermissionsController::list', ['filter' => 'perm:roles.assign_perms']);
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

    // Proforma Invoices
    $routes->get('proforma', 'ProformaController::index');
    $routes->get('proforma/list', 'ProformaController::list');
    $routes->get('proforma/create', 'ProformaController::create');
    $routes->get('proforma/edit/(:num)', 'ProformaController::edit/$1');
    $routes->get('proforma/show/(:num)', 'ProformaController::show/$1');
    $routes->get('proforma/print/(:num)', 'ProformaController::print/$1');
    $routes->get('proforma/pdf/(:num)', 'ProformaController::pdf/$1');
    $routes->get('proforma/edit-items', 'ProformaController::editItems');
    $routes->get('proforma/pending-items', 'ProformaController::pendingItems');
    $routes->get('proforma/getPendingItems/(:num)', 'ProformaController::getPendingItems/$1');
    $routes->post('proforma/save', 'ProformaController::save');
    $routes->post('proforma/update', 'ProformaController::update');
    $routes->post('proforma/delete', 'ProformaController::delete');

    // Payments (Invoices)
    $routes->get('payments', 'PaymentsController::index');
    $routes->get('payments/list', 'PaymentsController::list');
    $routes->get('payments/invoice-options', 'PaymentsController::invoiceOptions');
    $routes->get('payments/customers', 'PaymentsController::customers');
    $routes->get('payments/invoices-by-customer/(:num)', 'PaymentsController::invoicesByCustomer/$1');
    $routes->get('payments/invoice/(:num)', 'PaymentsController::invoice/$1');
    $routes->get('payments/view/(:num)', 'PaymentsController::view/$1');
    $routes->post('payments/save', 'PaymentsController::save');

    // Payment Report (Invoices)
    $routes->get('payment-report', 'PaymentReportController::index');
    $routes->get('payment-report/list', 'PaymentReportController::list');
    $routes->get('payment-report/download', 'PaymentReportController::download');

    // Day Book
    $routes->get('day-book/daily-expense-form', 'DailyExpenseController::index');
    $routes->get('day-book/daily-expense-form/create', 'DailyExpenseController::create');
    $routes->get('day-book/daily-expense-form/edit/(:num)', 'DailyExpenseController::edit/$1');
    $routes->get('day-book/daily-expense-form/receipt/(:num)', 'DailyExpenseController::receipt/$1');
    $routes->get('day-book/daily-expense-form/list', 'DailyExpenseController::list');
    $routes->post('day-book/daily-expense-form/save', 'DailyExpenseController::save');
    $routes->post('day-book/daily-expense-form/store', 'DailyExpenseController::store');
    $routes->post('day-book/daily-expense-form/update/(:num)', 'DailyExpenseController::update/$1');
    $routes->post('day-book/daily-expense-form/delete', 'DailyExpenseController::delete');

    $routes->get('day-book/daily-expense-report', 'DailyExpenseReportController::index');
    $routes->get('day-book/daily-expense-report/categories', 'DailyExpenseReportController::categories');
    $routes->get('day-book/daily-expense-report/data', 'DailyExpenseReportController::data');
    $routes->get('day-book/daily-expense-report/export-csv', 'DailyExpenseReportController::exportCsv');
    $routes->get('day-book/daily-expense-report/export-pdf', 'DailyExpenseReportController::exportPdf');

    // Optional diagnostic route (safe for shared hosting).
    $routes->get('dbtest', 'Home::dbtest');

    // Generic status update endpoint (allowlisted tables only).
    $routes->post('update-status', 'StatusController::updateStatus');
});
