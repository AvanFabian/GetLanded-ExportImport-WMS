<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * SuperAdmin CompanyController
 * 
 * Platform-level company management.
 * Bypasses TenantScope to manage all tenants.
 */
class CompanyController extends Controller
{
    /**
     * List all companies in the platform.
     */
    public function index()
    {
        $companies = Company::withCount('users')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('platform.companies.index', compact('companies'));
    }

    /**
     * Show create company form.
     */
    public function create()
    {
        return view('platform.companies.create');
    }

    /**
     * Store a new company.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['uuid'] = Str::uuid();
        $validated['is_active'] = $validated['is_active'] ?? true;

        $company = Company::create($validated);

        return redirect()
            ->route('platform.companies.index')
            ->with('success', "Company '{$company->name}' created successfully.");
    }

    /**
     * Show company details.
     */
    public function show(Company $company)
    {
        $company->loadCount('users');
        
        return view('platform.companies.show', compact('company'));
    }

    /**
     * Toggle company active status.
     */
    public function toggleActive(Request $request, Company $company)
    {
        $company->update([
            'is_active' => !$company->is_active,
        ]);

        $status = $company->is_active ? 'activated' : 'suspended';

        return back()->with('success', "Company '{$company->name}' has been {$status}.");
    }
}
