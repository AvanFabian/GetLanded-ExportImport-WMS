<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\Invoice;

class FlexibleDocumentService
{
    /**
     * Check if invoice can be generated for an order based on policy
     */
    public function canGenerateInvoice(SalesOrder $order): array
    {
        $company = Company::find($order->company_id);
        $policy = $company?->invoice_sequence_logic ?? 'strict';

        if ($policy === 'flexible') {
            // Flexible mode: Allow invoice at any status except cancelled
            if ($order->status === 'cancelled') {
                return [
                    'allowed' => false,
                    'reason' => 'Cannot generate invoice for cancelled orders',
                    'policy' => 'flexible',
                ];
            }

            return [
                'allowed' => true,
                'reason' => 'Invoice generation allowed (flexible policy)',
                'policy' => 'flexible',
                'warning' => $this->getFlexibleWarning($order),
            ];
        }

        // Strict mode: Require stock-out completion first
        $validStatuses = ['shipped', 'delivered'];
        
        if (!in_array($order->status, $validStatuses)) {
            return [
                'allowed' => false,
                'reason' => 'Invoice can only be generated after stock-out is completed. Current status: ' . $order->status,
                'policy' => 'strict',
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'policy' => 'strict',
        ];
    }

    /**
     * Get warning message for flexible invoice generation
     */
    protected function getFlexibleWarning(SalesOrder $order): ?string
    {
        $warnings = [];

        if ($order->status === 'draft') {
            $warnings[] = 'Order is still in draft status';
        }

        if ($order->status === 'pending') {
            $warnings[] = 'Order is pending confirmation';
        }

        if (!$order->stock_out_id) {
            $warnings[] = 'No stock-out has been generated';
        }

        if (empty($warnings)) {
            return null;
        }

        return 'Warning: ' . implode('. ', $warnings);
    }

    /**
     * Get document generation options based on policy
     */
    public function getDocumentOptions(SalesOrder $order): array
    {
        $company = Company::find($order->company_id);
        $policy = $company?->invoice_sequence_logic ?? 'strict';

        $options = [];

        // Proforma Invoice - always available
        $options['proforma'] = [
            'available' => true,
            'name' => 'Proforma Invoice',
        ];

        // Commercial Invoice
        $invoiceCheck = $this->canGenerateInvoice($order);
        $options['commercial_invoice'] = [
            'available' => $invoiceCheck['allowed'],
            'name' => 'Commercial Invoice',
            'reason' => $invoiceCheck['reason'] ?? null,
            'warning' => $invoiceCheck['warning'] ?? null,
        ];

        // Packing List
        $options['packing_list'] = [
            'available' => $policy === 'flexible' || in_array($order->status, ['confirmed', 'shipped', 'delivered']),
            'name' => 'Packing List',
        ];

        // Delivery Order
        $options['delivery_order'] = [
            'available' => in_array($order->status, ['confirmed', 'shipped', 'delivered']),
            'name' => 'Delivery Order',
        ];

        return $options;
    }

    /**
     * Check if order auto-void should happen on rejection
     */
    public function shouldAutoVoid(SalesOrder $order): bool
    {
        $company = Company::find($order->company_id);
        return $company?->auto_void_on_rejection ?? false;
    }
}
