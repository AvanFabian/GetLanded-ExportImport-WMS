<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $suppliers = Supplier::when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'q'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'contact_person' => 'nullable|string',
        ]);

        Supplier::create($data);

        return redirect()->route('suppliers.index')->with('status', 'Supplier created');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'contact_person' => 'nullable|string',
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('status', 'Supplier updated');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('status', 'Supplier deleted');
    }

    public function bulkDestroy(Request $request)
    {
        if ($request->has('delete_all') && $request->delete_all == '1') {
            $q = $request->input('q');

            $query = Supplier::query();

            // Re-apply filters
            $query->when($q, fn($qBuilder) => $qBuilder->where('name', 'like', "%{$q}%"));

            $count = $query->count();
            $query->delete();

            return back()->with('status', "All {$count} suppliers selected have been deleted.");
        }

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:suppliers,id',
        ]);

        $count = count($validated['ids']);
        Supplier::whereIn('id', $validated['ids'])->delete();

        return back()->with('status', "{$count} suppliers deleted successfully");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SuppliersImport, $request->file('file'));
            return back()->with('success', 'Suppliers imported successfully!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $messages = [];
            foreach ($failures as $failure) {
                $messages[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return back()->with('error', 'Import Validation Failed: ' . implode(' | ', array_slice($messages, 0, 5)));
        } catch (\Exception $e) {
            return back()->with('error', 'Import Failed: ' . $e->getMessage());
        }
    }
}
