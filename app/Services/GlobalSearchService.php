<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;

class GlobalSearchService
{
    /**
     * Search across multiple entities
     */
    public function search(int $companyId, string $query): array
    {
        $results = [];

        // Search sales orders
        $orders = SalesOrder::where('company_id', $companyId)
            ->where(function($q) use ($query) {
                $q->where('order_number', 'like', "%{$query}%")
                  ->orWhere('container_number', 'like', "%{$query}%")
                  ->orWhere('seal_number', 'like', "%{$query}%")
                  ->orWhere('vessel_name', 'like', "%{$query}%")
                  ->orWhere('document_awb_number', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        foreach ($orders as $order) {
            $results[] = [
                'type' => 'sales_order',
                'id' => $order->id,
                'title' => $order->order_number,
                'subtitle' => $order->customer?->name ?? 'Unknown Customer',
                'matched_field' => $this->getMatchedField($order, $query, [
                    'order_number', 'container_number', 'seal_number', 
                    'vessel_name', 'document_awb_number'
                ]),
                'url' => "/sales-orders/{$order->id}",
            ];
        }

        // Search batches
        $batches = Batch::whereHas('product', fn($q) => $q->where('company_id', $companyId))
            ->where('batch_number', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        foreach ($batches as $batch) {
            $results[] = [
                'type' => 'batch',
                'id' => $batch->id,
                'title' => $batch->batch_number,
                'subtitle' => $batch->product?->name ?? 'Unknown Product',
                'matched_field' => 'batch_number',
                'url' => "/batches/{$batch->id}",
            ];
        }

        // Search invoices (if table exists)
        if (DB::getSchemaBuilder()->hasTable('invoices')) {
            $invoices = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('invoice_number', 'like', "%{$query}%")
                ->limit(10)
                ->get();

            foreach ($invoices as $invoice) {
                $results[] = [
                    'type' => 'invoice',
                    'id' => $invoice->id,
                    'title' => $invoice->invoice_number,
                    'subtitle' => $invoice->is_proforma ? 'Proforma' : 'Invoice',
                    'matched_field' => 'invoice_number',
                    'url' => "/invoices/{$invoice->id}",
                ];
            }
        }

        return $results;
    }

    /**
     * Deep search for tracking numbers, AWB, etc.
     */
    public function deepSearch(int $companyId, string $trackingNumber): array
    {
        $results = [];

        // Search document AWB
        $orders = SalesOrder::where('company_id', $companyId)
            ->where('document_awb_number', $trackingNumber)
            ->get();

        foreach ($orders as $order) {
            $results[] = [
                'type' => 'document_courier',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'courier' => $order->document_courier_name,
                'awb' => $order->document_awb_number,
                'dispatched_at' => $order->document_dispatched_at,
            ];
        }

        // Search container numbers
        $containerOrders = SalesOrder::where('company_id', $companyId)
            ->where('container_number', $trackingNumber)
            ->get();

        foreach ($containerOrders as $order) {
            $results[] = [
                'type' => 'container',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'container_number' => $order->container_number,
                'seal_number' => $order->seal_number,
                'status' => $order->shipment_status,
            ];
        }

        return $results;
    }

    protected function getMatchedField($model, string $query, array $fields): ?string
    {
        $queryLower = strtolower($query);
        
        foreach ($fields as $field) {
            $value = strtolower($model->$field ?? '');
            if (str_contains($value, $queryLower)) {
                return $field;
            }
        }

        return null;
    }
}
