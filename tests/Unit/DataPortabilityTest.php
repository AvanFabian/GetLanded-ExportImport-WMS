<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ImportService;
use Illuminate\Support\Facades\Storage;

class DataPortabilityTest extends TestCase
{
    protected ImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->importService = new ImportService();
    }

    /**
     * Test bulk import validation - column mapping suggestions
     */
    public function test_bulk_import_validation(): void
    {
        // Test that header matching works correctly
        // Use headers that match unambiguously (address before email_address to avoid partial match issues)
        $headers = ['name', 'email', 'phone', 'street_address'];
        
        $suggestions = $this->importService->suggestMappings($headers, 'customers');

        // Should suggest appropriate mappings via partial matching
        $this->assertEquals('name', $suggestions['name']);
        $this->assertEquals('email', $suggestions['email']);
        $this->assertEquals('phone', $suggestions['phone']);
        $this->assertEquals('street_address', $suggestions['address']);
    }

    /**
     * Test column mapping with simple headers
     */
    public function test_simple_header_matching(): void
    {
        // Test exact matching
        $headers = ['name', 'email', 'phone', 'address'];
        $suggestions = $this->importService->suggestMappings($headers, 'customers');
        
        $this->assertEquals('name', $suggestions['name']);
        $this->assertEquals('email', $suggestions['email']);
        $this->assertEquals('phone', $suggestions['phone']);
        $this->assertEquals('address', $suggestions['address']);
    }

    /**
     * Test product import mapping
     */
    public function test_product_import_mapping(): void
    {
        $headers = ['sku', 'name', 'description', 'unit'];
        
        $suggestions = $this->importService->suggestMappings($headers, 'products');

        $this->assertEquals('sku', $suggestions['sku']);
        $this->assertEquals('name', $suggestions['name']);
        $this->assertEquals('description', $suggestions['description']);
        $this->assertEquals('unit', $suggestions['unit']);
    }

    /**
     * Test no matching headers returns null
     */
    public function test_unmatched_headers_return_null(): void
    {
        $headers = ['foo', 'bar', 'baz'];
        
        $suggestions = $this->importService->suggestMappings($headers, 'customers');

        $this->assertNull($suggestions['name']);
        $this->assertNull($suggestions['email']);
    }
}
