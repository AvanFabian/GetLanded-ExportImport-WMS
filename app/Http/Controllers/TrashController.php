<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Supplier;

class TrashController extends Controller
{
    public function index()
    {
        $products = Product::onlyTrashed()->latest('deleted_at')->get();
        $orders = SalesOrder::onlyTrashed()->latest('deleted_at')->get();
        $batches = Batch::onlyTrashed()->with('product')->latest('deleted_at')->get();
        $customers = Customer::onlyTrashed()->latest('deleted_at')->get();
        $suppliers = Supplier::onlyTrashed()->latest('deleted_at')->get();

        return view('trash.index', compact('products', 'orders', 'batches', 'customers', 'suppliers'));
    }

    public function restore(Request $request, $type, $id)
    {
        $modelClass = $this->getModelClass($type);
        
        if (!$modelClass) {
            return back()->with('error', 'Invalid type.');
        }

        $item = $modelClass::onlyTrashed()->find($id);

        if ($item) {
            $item->restore();
            return back()->with('success', ucfirst($type) . ' restored successfully.');
        }

        return back()->with('error', 'Item not found.');
    }

    private function getModelClass($type)
    {
        return match ($type) {
            'product' => Product::class,
            'order' => SalesOrder::class,
            'batch' => Batch::class,
            'customer' => Customer::class,
            'supplier' => Supplier::class,
            default => null,
        };
    }
}
