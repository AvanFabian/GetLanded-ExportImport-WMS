<?php

namespace App\Services;

use App\Models\UomConversion;
use Illuminate\Support\Collection;

class UomConversionService
{
    /**
     * Convert quantity from one unit to another
     *
     * @param float $quantity The quantity to convert
     * @param string $fromUnit The source unit
     * @param string $toUnit The target unit
     * @param int|null $productId Optional product ID for product-specific conversions
     * @param int|null $companyId Company ID (uses current user's company if null)
     * @return array ['quantity' => float, 'converted' => bool, 'precision_warning' => bool]
     */
    public function convert(
        float $quantity,
        string $fromUnit,
        string $toUnit,
        ?int $productId = null,
        ?int $companyId = null
    ): array {
        // Same unit, no conversion needed
        if (strtolower($fromUnit) === strtolower($toUnit)) {
            return [
                'quantity' => $quantity,
                'converted' => false,
                'precision_warning' => false,
            ];
        }

        $companyId = $companyId ?? auth()->user()?->company_id;

        // Find direct conversion
        $conversion = $this->findConversion($fromUnit, $toUnit, $productId, $companyId);

        if ($conversion) {
            $result = $quantity * $conversion->conversion_factor;
            return [
                'quantity' => $this->roundWithPrecision($result),
                'converted' => true,
                'precision_warning' => $this->hasPrecisionLoss($result),
            ];
        }

        // Try reverse conversion
        $reverseConversion = $this->findConversion($toUnit, $fromUnit, $productId, $companyId);

        if ($reverseConversion) {
            $result = $quantity / $reverseConversion->conversion_factor;
            return [
                'quantity' => $this->roundWithPrecision($result),
                'converted' => true,
                'precision_warning' => $this->hasPrecisionLoss($result),
            ];
        }

        // Try chain conversion through base unit (e.g., Bag -> KG -> MT)
        $chainResult = $this->tryChainConversion($quantity, $fromUnit, $toUnit, $productId, $companyId);
        if ($chainResult !== null) {
            return [
                'quantity' => $this->roundWithPrecision($chainResult),
                'converted' => true,
                'precision_warning' => $this->hasPrecisionLoss($chainResult),
            ];
        }

        // No conversion found
        throw new \InvalidArgumentException("No conversion found from {$fromUnit} to {$toUnit}");
    }

    /**
     * Get all available units for a product (including global company conversions)
     */
    public function getAvailableUnits(?int $productId = null, ?int $companyId = null): Collection
    {
        $companyId = $companyId ?? auth()->user()?->company_id;

        $conversions = UomConversion::where('company_id', $companyId)
            ->active()
            ->forProduct($productId)
            ->get();

        $units = collect();
        
        foreach ($conversions as $conversion) {
            $units->push($conversion->from_unit);
            $units->push($conversion->to_unit);
        }

        return $units->unique()->values();
    }

    /**
     * Format quantity with alternate unit display
     * e.g., "1,000 KG (20 Bags)" or "50 MT (1,000 Bags)"
     */
    public function formatWithAlternate(
        float $quantity,
        string $baseUnit,
        string $alternateUnit,
        ?int $productId = null
    ): string {
        $baseFormatted = number_format($quantity, 2) . ' ' . $baseUnit;

        try {
            $converted = $this->convert($quantity, $baseUnit, $alternateUnit, $productId);
            $altFormatted = number_format($converted['quantity'], 2) . ' ' . $alternateUnit;
            return "{$baseFormatted} ({$altFormatted})";
        } catch (\InvalidArgumentException $e) {
            return $baseFormatted;
        }
    }

    /**
     * Get the default display unit for a product
     */
    public function getDefaultUnit(?int $productId, ?int $companyId = null): ?string
    {
        $companyId = $companyId ?? auth()->user()?->company_id;

        $default = UomConversion::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('is_default', true)
            ->active()
            ->first();

        return $default?->to_unit;
    }

    /**
     * Create or update a conversion
     */
    public function setConversion(
        string $fromUnit,
        string $toUnit,
        float $factor,
        ?int $productId = null,
        ?int $companyId = null
    ): UomConversion {
        $companyId = $companyId ?? auth()->user()?->company_id;

        return UomConversion::updateOrCreate(
            [
                'company_id' => $companyId,
                'product_id' => $productId,
                'from_unit' => strtoupper($fromUnit),
                'to_unit' => strtoupper($toUnit),
            ],
            [
                'conversion_factor' => $factor,
                'is_active' => true,
            ]
        );
    }

    /**
     * Get common unit conversions (pre-defined)
     */
    public static function getCommonConversions(): array
    {
        return [
            ['from' => 'MT', 'to' => 'KG', 'factor' => 1000, 'name' => 'Metric Ton to Kilogram'],
            ['from' => 'BAG', 'to' => 'KG', 'factor' => 50, 'name' => 'Bag (50kg) to Kilogram'],
            ['from' => 'CARTON', 'to' => 'PCS', 'factor' => 12, 'name' => 'Carton to Pieces'],
            ['from' => 'PALLET', 'to' => 'CARTON', 'factor' => 48, 'name' => 'Pallet to Cartons'],
            ['from' => 'CONTAINER', 'to' => 'MT', 'factor' => 25, 'name' => '20ft Container to MT'],
        ];
    }

    /**
     * Find a specific conversion
     */
    protected function findConversion(
        string $fromUnit,
        string $toUnit,
        ?int $productId,
        ?int $companyId
    ): ?UomConversion {
        return UomConversion::where('company_id', $companyId)
            ->where('from_unit', strtoupper($fromUnit))
            ->where('to_unit', strtoupper($toUnit))
            ->forProduct($productId)
            ->active()
            ->orderByRaw('product_id IS NULL') // Prefer product-specific
            ->first();
    }

    /**
     * Try to find a chain conversion through intermediate units
     */
    protected function tryChainConversion(
        float $quantity,
        string $fromUnit,
        string $toUnit,
        ?int $productId,
        ?int $companyId
    ): ?float {
        // Get all conversions for this product/company
        $conversions = UomConversion::where('company_id', $companyId)
            ->forProduct($productId)
            ->active()
            ->get();

        // Find conversions from $fromUnit
        $fromConversions = $conversions->filter(fn($c) => 
            strtoupper($c->from_unit) === strtoupper($fromUnit)
        );

        foreach ($fromConversions as $first) {
            // Try to find second hop
            $intermediateUnit = $first->to_unit;
            
            // Look for conversion from intermediate to target
            $secondHop = $conversions->first(fn($c) => 
                strtoupper($c->from_unit) === strtoupper($intermediateUnit) &&
                strtoupper($c->to_unit) === strtoupper($toUnit)
            );

            if ($secondHop) {
                return $quantity * $first->conversion_factor * $secondHop->conversion_factor;
            }
        }

        return null;
    }

    /**
     * Round with high precision to avoid floating point errors
     */
    protected function roundWithPrecision(float $value, int $decimals = 6): float
    {
        return round($value, $decimals);
    }

    /**
     * Check if significant precision was lost
     */
    protected function hasPrecisionLoss(float $value): bool
    {
        // Check if there are significant digits beyond 6 decimal places
        $rounded = round($value, 6);
        return abs($value - $rounded) > 0.0000001;
    }
}
