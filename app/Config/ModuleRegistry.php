<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ModuleRegistry extends BaseConfig
{
    /**
     * "Forms" shown in Permissions (Forms) list.
     * Each entry becomes/updates a permissions row with a non-dot `key`,
     * and the display name stored in `module` for the Form Name column.
     *
     * @var list<array{key:string,name:string,group:string}>
     */
    public array $forms = [
        ['key' => 'users',            'name' => 'Users',            'group' => 'SYSTEM ADMIN'],
        ['key' => 'roles',            'name' => 'Roles',            'group' => 'SYSTEM ADMIN'],
        ['key' => 'permissions',      'name' => 'Permissions',      'group' => 'SYSTEM ADMIN'],
        ['key' => 'role-permissions', 'name' => 'Role Permissions', 'group' => 'SYSTEM ADMIN'],

        ['key' => 'client-master',    'name' => 'Client Masters',   'group' => 'MASTERS'],

        ['key' => 'billable-items',   'name' => 'Billable Items',   'group' => 'BILLABLE ITEMS'],
    ];

    /**
     * Permission matrix used by Role Permissions edit page.
     * Keys correspond to `permissions.key` values (dot keys).
     *
     * @var array<string, array<string, array{read:list<string>,write:list<string>,delete:list<string>}>>
     */
    public array $permissionMatrix = [
        'SYSTEM ADMIN' => [
            'Users' => [
                'read'   => ['users.view'],
                'write'  => ['users.create', 'users.edit'],
                'delete' => ['users.delete'],
            ],
            'Roles' => [
                'read'   => ['roles.view'],
                'write'  => ['roles.create', 'roles.edit'],
                'delete' => ['roles.delete'],
            ],
            'Permissions' => [
                'read'   => ['permissions.view'],
                'write'  => ['permissions.create', 'permissions.edit'],
                'delete' => ['permissions.delete'],
            ],
            'Role Permissions' => [
                'read'   => ['roles.assign_perms', 'role_permissions.view'],
                'write'  => ['role_permissions.edit'],
                'delete' => ['role_permissions.delete'],
            ],
        ],
        'MASTERS' => [
            'Client Masters' => [
                'read'   => ['client_masters.view'],
                'write'  => ['client_masters.create', 'client_masters.edit'],
                'delete' => ['client_masters.delete'],
            ],
        ],
        'BILLABLE ITEMS' => [
            'Billable Items' => [
                'read'   => ['billable_items.view'],
                'write'  => ['billable_items.create', 'billable_items.edit'],
                'delete' => ['billable_items.delete'],
            ],
        ],
    ];
}

