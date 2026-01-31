<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ImportService;

class ImportLogicTest extends TestCase
{
    public function test_clean_weight_converts_pounds_to_kg()
    {
        $service = new ImportService();
        // 10 lbs * 0.453592 = 4.53592
        $this->assertEquals(4.53592, $service->cleanWeight('10 lbs'));
        $this->assertEquals(4.53592, $service->cleanWeight('10 pounds'));
        
        // 100 oz * 0.0283495 = 2.83495
        $this->assertEquals(2.83495, $service->cleanWeight('100 oz'));
    }

    public function test_clean_currency_removes_symbols()
    {
        $service = new ImportService();
        $this->assertEquals(1200, $service->cleanCurrency('$ 1,200.00'));
        $this->assertEquals(18000000, $service->cleanCurrency('Rp. 18.000.000'));
        $this->assertEquals(500.50, $service->cleanCurrency('500.50'));
    }

    public function test_clean_unit_normalizes_strings()
    {
        $service = new ImportService();
        $this->assertEquals('pcs', $service->cleanUnit('Pieces'));
        $this->assertEquals('pcs', $service->cleanUnit('Buah'));
        $this->assertEquals('pcs', $service->cleanUnit('Unit'));
        $this->assertEquals('kg', $service->cleanUnit('Kilograms'));
        $this->assertEquals('m', $service->cleanUnit('Meter'));
    }

    public function test_fuzzy_matching_is_strict()
    {
        $service = new ImportService();
        $aliases = ['name', 'product name'];
        
        // Should match "Product Name" (Contains valid word)
        $this->assertEquals('Product Name', $service->findBestMatch($aliases, ['Product Name', 'Other']));

        // Should NOT match "Filename" (Contains "name" but no word boundary)
        $this->assertNull($service->findBestMatch($aliases, ['Filename', 'Other']));

        // Should match "Name" (Exact)
        $this->assertEquals('Name', $service->findBestMatch($aliases, ['Name', 'Other']));
    }
}
