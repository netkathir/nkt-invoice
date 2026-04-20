<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class ClientTableSchema
{
    /**
     * @var array<string, array<string, int|string|bool|null>>
     */
    private const BILLING_LOCATION_COLUMNS = [
        'billing_city' => [
            'type'       => 'VARCHAR',
            'constraint' => 100,
            'null'       => true,
            'after'      => 'billing_address',
        ],
        'billing_state' => [
            'type'       => 'VARCHAR',
            'constraint' => 100,
            'null'       => true,
            'after'      => 'billing_city',
        ],
        'billing_country' => [
            'type'       => 'VARCHAR',
            'constraint' => 100,
            'null'       => true,
            'after'      => 'billing_state',
        ],
        'billing_postal_code' => [
            'type'       => 'VARCHAR',
            'constraint' => 20,
            'null'       => true,
            'after'      => 'billing_country',
        ],
    ];

    private ?BaseConnection $db;

    /**
     * @var list<string>|null
     */
    private ?array $fields;

    /**
     * @param list<string>|null $fields
     */
    public function __construct(?BaseConnection $db = null, ?array $fields = null)
    {
        $this->db     = $db;
        $this->fields = $fields;
    }

    public function ensureBillingLocationColumns(): void
    {
        $db = $this->db();
        if (! $db->tableExists('clients')) {
            $this->fields = [];
            return;
        }

        $missing = [];
        foreach (self::BILLING_LOCATION_COLUMNS as $column => $definition) {
            if (! $db->fieldExists($column, 'clients')) {
                $missing[$column] = $definition;
            }
        }

        if ($missing === []) {
            $this->fields = null;
            return;
        }

        Database::forge()->addColumn('clients', $missing);
        $this->fields = null;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function filterExistingColumns(array $payload): array
    {
        $available = array_flip($this->getFields());

        return array_filter(
            $payload,
            static fn (string $column): bool => isset($available[$column]),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function optionalSelect(string $tableAlias, string $column, ?string $alias = null): string
    {
        $alias ??= $column;

        if ($this->hasField($column)) {
            return $tableAlias . '.' . $column . ($alias !== $column ? ' AS ' . $alias : '');
        }

        return 'NULL AS ' . $alias;
    }

    public function hasField(string $column): bool
    {
        return in_array($column, $this->getFields(), true);
    }

    /**
     * @return list<string>
     */
    public function getFields(): array
    {
        if ($this->fields !== null) {
            return $this->fields;
        }

        $db = $this->db();
        if (! $db->tableExists('clients')) {
            $this->fields = [];
            return $this->fields;
        }

        /** @var list<string> $fields */
        $fields = $db->getFieldNames('clients');
        $this->fields = $fields;

        return $this->fields;
    }

    private function db(): BaseConnection
    {
        if ($this->db === null) {
            $this->db = db_connect();
        }

        return $this->db;
    }
}
