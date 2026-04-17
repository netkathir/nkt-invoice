<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class BmsTaxResolverTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('bms');
    }

    public function testResolvesSplitGstForSameStateClient(): void
    {
        $company = [
            'state' => 'Tamil Nadu',
            'gstin_number' => '33ABCDE1234F1Z5',
        ];
        $client = [
            'country' => 'India',
            'billing_state' => 'Tamil Nadu',
            'gst_no' => '33AAAAA1111A1Z1',
        ];

        $this->assertSame('CGST_SGST', bms_resolve_gst_mode($client, $company, 'IGST'));
    }

    public function testResolvesIgstForDifferentStateClient(): void
    {
        $company = [
            'state' => 'Tamil Nadu',
            'gstin_number' => '33ABCDE1234F1Z5',
        ];
        $client = [
            'country' => 'India',
            'billing_state' => 'Karnataka',
            'gst_no' => '29AAAAA1111A1Z1',
        ];

        $this->assertSame('IGST', bms_resolve_gst_mode($client, $company, 'CGST_SGST'));
    }

    public function testFallsBackToStoredModeWhenLocationDataIsIncomplete(): void
    {
        $company = [
            'state' => '',
            'gstin_number' => '',
        ];
        $client = [
            'country' => 'India',
            'billing_state' => '',
            'gst_no' => '',
        ];

        $this->assertSame('IGST', bms_resolve_gst_mode($client, $company, 'IGST'));
    }
}
