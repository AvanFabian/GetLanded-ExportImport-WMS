<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * CompanySettingsController
 * 
 * Manages company profile settings including logo, address, and tax info.
 * Only accessible by Owner role.
 */
class CompanySettingsController extends Controller
{
    /**
     * Show company settings form.
     */
    public function index()
    {
        $this->authorizeOwner();
        
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        return view('company.settings', compact('company'));
    }

    /**
     * Update company settings.
     */
    public function update(Request $request)
    {
        $this->authorizeOwner();
        
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'tax_registration_number' => 'nullable|string|max:100',
            'default_vat_percentage' => 'nullable|numeric|min:0|max:100',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_swift_code' => 'nullable|string|max:50',
            'invoice_terms' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('company-logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Remove logo key if present (we use logo_path)
        unset($validated['logo']);

        $company->update($validated);

        return back()->with('success', 'Company settings updated successfully.');
    }

    /**
     * Remove company logo.
     */
    public function removeLogo()
    {
        $this->authorizeOwner();
        
        $company = auth()->user()->company;
        
        if ($company && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $company->update(['logo_path' => null]);
        }

        return back()->with('success', 'Logo removed successfully.');
    }

    /**
     * Authorize that user is owner/admin.
     */
    protected function authorizeOwner(): void
    {
        $user = auth()->user();
        
        if (!$user->hasPermissionTo('user.manage') && !$user->is_super_admin) {
            abort(403, 'Only company owners can access settings.');
        }
    }
}
