<?php

namespace App\Enums;

/**
 * Incoterms Enum
 * 
 * International Commercial Terms for trade compliance.
 */
enum Incoterms: string
{
    case EXW = 'EXW';  // Ex Works
    case FCA = 'FCA';  // Free Carrier
    case CPT = 'CPT';  // Carriage Paid To
    case CIP = 'CIP';  // Carriage and Insurance Paid To
    case DAP = 'DAP';  // Delivered at Place
    case DPU = 'DPU';  // Delivered at Place Unloaded
    case DDP = 'DDP';  // Delivered Duty Paid
    case FAS = 'FAS';  // Free Alongside Ship
    case FOB = 'FOB';  // Free on Board
    case CFR = 'CFR';  // Cost and Freight
    case CIF = 'CIF';  // Cost, Insurance, and Freight

    /**
     * Get full description.
     */
    public function description(): string
    {
        return match($this) {
            self::EXW => 'Ex Works - Buyer bears all costs',
            self::FCA => 'Free Carrier - Delivered to carrier',
            self::CPT => 'Carriage Paid To - Freight prepaid',
            self::CIP => 'Carriage and Insurance Paid To',
            self::DAP => 'Delivered at Place - Named destination',
            self::DPU => 'Delivered at Place Unloaded',
            self::DDP => 'Delivered Duty Paid - All costs on seller',
            self::FAS => 'Free Alongside Ship',
            self::FOB => 'Free on Board - Risk transfers at ship',
            self::CFR => 'Cost and Freight',
            self::CIF => 'Cost, Insurance, and Freight',
        };
    }

    /**
     * Check if this is a sea/inland waterway term.
     */
    public function isSeaTerm(): bool
    {
        return in_array($this, [self::FAS, self::FOB, self::CFR, self::CIF]);
    }
}
