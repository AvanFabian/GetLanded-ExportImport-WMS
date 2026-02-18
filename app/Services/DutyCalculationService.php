<?php

namespace App\Services;

/**
 * Indonesian Customs Duty Calculation Service
 *
 * Implements the full BM/PPN/PPh/Anti-dumping calculation
 * per Indonesian customs regulation for import declarations.
 *
 * Formula:
 *   CIF Value    = Declared Value (user input)
 *   BM           = CIF × BM Rate%
 *   PPN          = (CIF + BM) × PPN Rate% (default 11%)
 *   PPh 22       = (CIF + BM) × PPh Rate% (2.5% with API-U, 7.5% without)
 *   Anti-dumping = CIF × AD Rate% (if applicable)
 *   Total Tax    = BM + PPN + PPh + AD
 */
class DutyCalculationService
{
    /**
     * Default PPN rate (Value Added Tax) - 11% per Indonesian regulation
     */
    const DEFAULT_PPN_RATE = 11;

    /**
     * Default PPh 22 Import rate with API-U (Angka Pengenal Impor Umum)
     */
    const PPH_RATE_WITH_API = 2.5;

    /**
     * PPh 22 Import rate without API-U
     */
    const PPH_RATE_WITHOUT_API = 7.5;

    /**
     * Calculate full duty breakdown for an import declaration.
     *
     * @param float $declaredValue  CIF value of goods
     * @param float $bmRate         Bea Masuk (import duty) rate in %
     * @param float|null $ppnRate   PPN rate in %, defaults to 11%
     * @param float|null $pphRate   PPh 22 rate in %, defaults to 2.5%
     * @param float $adRate         Anti-dumping rate in %, defaults to 0
     * @param float $exciseAmount   Excise/cukai amount (flat), defaults to 0
     *
     * @return array{
     *     declared_value: float,
     *     bm_rate: float,
     *     bm_amount: float,
     *     ppn_rate: float,
     *     ppn_amount: float,
     *     pph_rate: float,
     *     pph_amount: float,
     *     anti_dumping_rate: float,
     *     anti_dumping_amount: float,
     *     excise_amount: float,
     *     total_tax: float,
     *     landed_cost: float
     * }
     */
    public function calculate(
        float $declaredValue,
        float $bmRate = 0,
        ?float $ppnRate = null,
        ?float $pphRate = null,
        float $adRate = 0,
        float $exciseAmount = 0
    ): array {
        $ppnRate = $ppnRate ?? self::DEFAULT_PPN_RATE;
        $pphRate = $pphRate ?? self::PPH_RATE_WITH_API;

        // BM (Bea Masuk / Import Duty)
        $bmAmount = $declaredValue * ($bmRate / 100);

        // PPN (Pajak Pertambahan Nilai / VAT)
        // Tax base = CIF + BM
        $ppnBase = $declaredValue + $bmAmount;
        $ppnAmount = $ppnBase * ($ppnRate / 100);

        // PPh 22 (Pajak Penghasilan / Income Tax on Imports)
        // Tax base = CIF + BM (same as PPN)
        $pphAmount = $ppnBase * ($pphRate / 100);

        // Anti-dumping duty (BMAD)
        $adAmount = $declaredValue * ($adRate / 100);

        // Total tax payable
        $totalTax = $bmAmount + $ppnAmount + $pphAmount + $adAmount + $exciseAmount;

        return [
            'declared_value' => round($declaredValue, 2),
            'bm_rate' => $bmRate,
            'bm_amount' => round($bmAmount, 2),
            'ppn_rate' => $ppnRate,
            'ppn_amount' => round($ppnAmount, 2),
            'pph_rate' => $pphRate,
            'pph_amount' => round($pphAmount, 2),
            'anti_dumping_rate' => $adRate,
            'anti_dumping_amount' => round($adAmount, 2),
            'excise_amount' => round($exciseAmount, 2),
            'total_tax' => round($totalTax, 2),
            'landed_cost' => round($declaredValue + $totalTax, 2),
        ];
    }

    /**
     * Calculate duty for multiple items.
     *
     * @param array $items Array of ['value' => float, 'bm_rate' => float, ...]
     * @return array{items: array, totals: array}
     */
    public function calculateBatch(array $items, ?float $ppnRate = null, ?float $pphRate = null): array
    {
        $results = [];
        $totals = [
            'declared_value' => 0,
            'bm_amount' => 0,
            'ppn_amount' => 0,
            'pph_amount' => 0,
            'anti_dumping_amount' => 0,
            'excise_amount' => 0,
            'total_tax' => 0,
            'landed_cost' => 0,
        ];

        foreach ($items as $item) {
            $result = $this->calculate(
                declaredValue: $item['value'] ?? 0,
                bmRate: $item['bm_rate'] ?? 0,
                ppnRate: $ppnRate,
                pphRate: $pphRate,
                adRate: $item['anti_dumping_rate'] ?? 0,
                exciseAmount: $item['excise_amount'] ?? 0,
            );

            $results[] = $result;

            foreach ($totals as $key => &$total) {
                $total += $result[$key] ?? 0;
            }
        }

        return [
            'items' => $results,
            'totals' => array_map(fn ($v) => round($v, 2), $totals),
        ];
    }
}
