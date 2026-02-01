<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['creator', 'updater']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $customers = $query->latest()->paginate(10)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name',
            'address' => 'required|string',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load(['salesOrders.items.product', 'creator', 'updater']);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name,' . $customer->id,
            'address' => 'required|string',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['updated_by'] = Auth::id();

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has sales orders
        if ($customer->salesOrders()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Pelanggan tidak dapat dihapus karena memiliki riwayat pesanan penjualan.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\CustomersImport, $request->file('file'));
            return back()->with('success', 'Customers imported successfully!');
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
