<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class BarcodeController extends Controller
{
    /**
     * Generate barcode image for a product
     */
    public function generateBarcode($productId)
    {
        $product = Product::findOrFail($productId);

        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($product->code, $generator::TYPE_CODE_128);

        return response($barcode)
            ->header('Content-Type', 'image/png');
    }

    /**
     * Generate QR code for a product
     */
    public function generateQrCode($productId)
    {
        $product = Product::with(['category', 'warehouses'])->findOrFail($productId);

        // Product data in JSON format
        $data = json_encode([
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'category' => $product->category->name ?? '',
            'warehouse' => $product->warehouses->first()?->name ?? '',
            'price' => $product->selling_price,
        ]);

        // Return SVG format (doesn't require Imagick)
        return response(QrCode::size(300)->generate($data))
            ->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Display product label with barcode and QR code
     */
    public function showLabel($productId)
    {
        $product = Product::with(['category', 'warehouses'])->findOrFail($productId);

        return view('barcodes.label', compact('product'));
    }

    /**
     * Print product labels (PDF)
     */
    public function printLabels(Request $request)
    {
        $productIds = $request->input('product_ids', []);

        if (empty($productIds)) {
            return redirect()->back()->with('error', 'Please select products to print labels');
        }

        $products = Product::with(['category', 'warehouses'])
            ->whereIn('id', $productIds)
            ->get();

        $pdf = Pdf::loadView('barcodes.print', compact('products'))
            ->setPaper('a4');

        return $pdf->download('product-labels-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Scan barcode and return product info
     */
    public function scan(Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json(['error' => 'No barcode provided'], 400);
        }

        $product = Product::with(['category', 'warehouses'])
            ->where('code', $code)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Get first warehouse for backward compatibility
        $firstWarehouse = $product->warehouses->first();

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'category' => $product->category->name ?? '',
                'warehouse_id' => $firstWarehouse?->id,
                'warehouse' => $firstWarehouse?->name ?? '',
                'stock' => $product->total_stock,
                'unit' => $product->unit,
                'purchase_price' => $product->purchase_price,
                'selling_price' => $product->selling_price,
                'rack_location' => $firstWarehouse?->pivot?->rack_location ?? '',
            ]
        ]);
    }
}
