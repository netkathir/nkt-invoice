<?php

use App\Libraries\ClientTableSchema;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ClientTableSchemaTest extends CIUnitTestCase
{
    public function testFilterExistingColumnsDropsMissingBillingFields(): void
    {
        $schema = new ClientTableSchema(null, [
            'id',
            'name',
            'contact_person',
            'email',
            'phone',
            'address',
            'billing_address',
            'city',
            'state',
            'country',
            'postal_code',
            'status',
        ]);

        $filtered = $schema->filterExistingColumns([
            'name'                => 'John Smith',
            'contact_person'      => 'Emily Clark',
            'billing_address'     => 'Parivar Char Rasta',
            'billing_city'        => 'Vadodara',
            'billing_state'       => 'Tamil Nadu',
            'billing_country'     => 'India',
            'billing_postal_code' => '390019',
            'city'                => 'Vadodara',
        ]);

        $this->assertSame([
            'name'           => 'John Smith',
            'contact_person' => 'Emily Clark',
            'billing_address' => 'Parivar Char Rasta',
            'city'           => 'Vadodara',
        ], $filtered);
    }

    public function testOptionalSelectFallsBackToNullAliasForMissingColumn(): void
    {
        $schema = new ClientTableSchema(null, [
            'id',
            'name',
            'contact_person',
            'billing_address',
            'city',
            'state',
            'country',
            'postal_code',
        ]);

        $this->assertSame('NULL AS billing_city', $schema->optionalSelect('clients', 'billing_city'));
        $this->assertSame('clients.city', $schema->optionalSelect('clients', 'city'));
    }
}
