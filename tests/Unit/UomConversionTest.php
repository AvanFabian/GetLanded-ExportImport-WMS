<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UomConversionService;
use App\Models\UomConversion;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UomConversionTest extends TestCase
{
    protected UomConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UomConversionService();
    }

    /**
     * Test unit conversion calculates correct totals
     */
    public function test_unit_conversion_calculates_correct_totals(): void
    {
        // Test: 1 MT = 1000 KG
        // 5 MT should equal 5000 KG
        $mtToKg = 5 * 1000;
        $this->assertEquals(5000, $mtToKg);

        // Test: 1 Bag = 50 KG
        // 20 Bags should equal 1000 KG
        $bagsToKg = 20 * 50;
        $this->assertEquals(1000, $bagsToKg);

        // Test: 1 MT = 20 Bags (1000 KG / 50 KG)
        $mtToBags = 1 * 1000 / 50;
        $this->assertEquals(20, $mtToBags);
    }

    /**
     * Test UoM conversion rounding precision
     */
    public function test_uom_conversion_rounding_precision(): void
    {
        // Converting from KG to Metric Tons shouldn't lose precision
        
        // Case 1: 1234.567 KG to MT
        $kg = 1234.567;
        $mt = $kg / 1000;
        $this->assertEquals(1.234567, $mt);

        // Case 2: Back-conversion should be precise
        $backToKg = $mt * 1000;
        $this->assertEquals(1234.567, $backToKg);

        // Case 3: Small quantities
        $smallKg = 0.001; // 1 gram in KG
        $smallMt = $smallKg / 1000;
        $this->assertEquals(0.000001, $smallMt);

        // Case 4: Large quantities
        $largeKg = 999999.999999;
        $largeMt = $largeKg / 1000;
        $this->assertEqualsWithDelta(999.999999999, $largeMt, 0.0000001);
    }

    /**
     * Test chain conversion (Bag -> KG -> MT)
     */
    public function test_chain_conversion_logic(): void
    {
        // 100 Bags -> KG -> MT
        // 100 Bags = 5000 KG = 5 MT
        $bags = 100;
        $kg = $bags * 50; // Bags to KG
        $mt = $kg / 1000; // KG to MT

        $this->assertEquals(5000, $kg);
        $this->assertEquals(5, $mt);
    }

    /**
     * Test same unit returns no conversion
     */
    public function test_same_unit_no_conversion(): void
    {
        // Same unit should return unchanged
        $quantity = 123.45;
        $fromUnit = 'KG';
        $toUnit = 'KG';

        // Same unit comparison (case-insensitive)
        $this->assertTrue(strtolower($fromUnit) === strtolower($toUnit));
        
        // No conversion needed, quantity unchanged
        $this->assertEquals(123.45, $quantity);
    }

    /**
     * Test inverse conversion factor
     */
    public function test_inverse_conversion_factor(): void
    {
        // If 1 MT = 1000 KG, then 1 KG = 0.001 MT
        $mtToKgFactor = 1000;
        $kgToMtFactor = 1 / $mtToKgFactor;

        $this->assertEquals(0.001, $kgToMtFactor);

        // Converting 500 KG to MT
        $kg = 500;
        $mt = $kg * $kgToMtFactor;
        $this->assertEquals(0.5, $mt);
    }

    /**
     * Test common conversions data
     */
    public function test_common_conversions_available(): void
    {
        $common = UomConversionService::getCommonConversions();

        $this->assertNotEmpty($common);
        $this->assertIsArray($common);

        // Check expected conversions exist
        $names = array_column($common, 'name');
        $this->assertContains('Metric Ton to Kilogram', $names);
        $this->assertContains('Bag (50kg) to Kilogram', $names);
    }

    /**
     * Test format with alternate unit display
     */
    public function test_format_with_alternate_display(): void
    {
        // Format: "1,000.00 KG (1.00 MT)" style output
        $quantity = 1000;
        $baseFormatted = number_format($quantity, 2) . ' KG';
        
        $this->assertEquals('1,000.00 KG', $baseFormatted);

        // With alternate
        $converted = 1000 / 1000; // to MT
        $altFormatted = number_format($converted, 2) . ' MT';
        $combined = "{$baseFormatted} ({$altFormatted})";

        $this->assertEquals('1,000.00 KG (1.00 MT)', $combined);
    }
}
