@extends('layouts.app')

@section('title', 'Company Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Company Settings</h1>
        <p class="text-gray-600 text-sm mt-1">Manage your company profile, branding, and tax information</p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('company.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Logo Section --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span>🎨</span> Company Branding
            </h2>
            
            <div class="flex flex-col sm:flex-row gap-6 items-start">
                {{-- Current Logo Preview --}}
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-xl flex items-center justify-center bg-gray-50 overflow-hidden" id="logoPreview">
                        @if($company->logo_path)
                            <img src="{{ Storage::url($company->logo_path) }}" alt="Company Logo" class="w-full h-full object-contain">
                        @else
                            <div class="text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs">No Logo</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Logo</label>
                    <input type="file" name="logo" id="logoInput" accept="image/png,image/jpeg,image/svg+xml"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="mt-2 text-xs text-gray-500">PNG, JPG, or SVG. Max 2MB. Recommended: 400x400px</p>
                    
                    @if($company->logo_path)
                        <button type="button" onclick="removeLogo()" class="mt-2 text-sm text-red-600 hover:text-red-700">
                            Remove current logo
                        </button>
                    @endif
                    
                    @error('logo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span>🏢</span> Company Information
            </h2>
            
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500" required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="sm:col-span-2">
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                    <input type="url" name="website" id="website" value="{{ old('website', $company->website) }}"
                           placeholder="https://"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span>📍</span> Address
            </h2>
            
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                    <textarea name="address" id="address" rows="2"
                              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">{{ old('address', $company->address) }}</textarea>
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $company->city) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
                    <input type="text" name="state" id="state" value="{{ old('state', $company->state) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                    <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $company->postal_code) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input type="text" name="country" id="country" value="{{ old('country', $company->country ?? 'Indonesia') }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        {{-- Tax Info --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span>📋</span> Tax & Legal Information
            </h2>
            
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-1">Tax ID / NPWP</label>
                    <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id', $company->tax_id) }}"
                           placeholder="e.g. 01.234.567.8-901.234"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="tax_registration_number" class="block text-sm font-medium text-gray-700 mb-1">Tax Registration No.</label>
                    <input type="text" name="tax_registration_number" id="tax_registration_number" 
                           value="{{ old('tax_registration_number', $company->tax_registration_number) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="default_vat_percentage" class="block text-sm font-medium text-gray-700 mb-1">Default VAT %</label>
                    <div class="relative">
                        <input type="number" name="default_vat_percentage" id="default_vat_percentage" 
                               value="{{ old('default_vat_percentage', $company->default_vat_percentage ?? 11) }}"
                               step="0.01" min="0" max="100"
                               class="w-full border rounded-lg px-3 py-2 pr-8 focus:ring-2 focus:ring-emerald-500">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">%</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Applied to invoices and sales orders</p>
                </div>
            </div>
        </div>

        {{-- Bank Details --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span>🏦</span> Bank Details
            </h2>
            <p class="text-sm text-gray-500 mb-4">These details will appear in the footer of your invoices and commercial documents.</p>
            
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" 
                           value="{{ old('bank_name', $company->bank_name) }}"
                           placeholder="e.g. Bank Central Asia"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="bank_account_number" class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                    <input type="text" name="bank_account_number" id="bank_account_number" 
                           value="{{ old('bank_account_number', $company->bank_account_number) }}"
                           placeholder="e.g. 1234567890"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="bank_swift_code" class="block text-sm font-medium text-gray-700 mb-1">SWIFT/BIC Code</label>
                    <input type="text" name="bank_swift_code" id="bank_swift_code" 
                           value="{{ old('bank_swift_code', $company->bank_swift_code) }}"
                           placeholder="e.g. CENAIDJA"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        {{-- Invoice Terms & Conditions --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span>📄</span> Invoice Terms & Conditions
            </h2>
            <p class="text-sm text-gray-500 mb-4">Custom legal disclaimer that will appear at the bottom of your invoices.</p>
            
            <div>
                <label for="invoice_terms" class="block text-sm font-medium text-gray-700 mb-1">Terms & Conditions</label>
                <textarea name="invoice_terms" id="invoice_terms" rows="4"
                          placeholder="e.g. Payment is due within 30 days. Goods remain the property of the seller until full payment is received..."
                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">{{ old('invoice_terms', $company->invoice_terms) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">This text will appear in the footer of all Commercial Invoices.</p>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-semibold">
                Save Company Settings
            </button>
        </div>
    </form>
</div>

<script>
// Logo preview
document.getElementById('logoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').innerHTML = `<img src="${e.target.result}" alt="Logo Preview" class="w-full h-full object-contain">`;
        };
        reader.readAsDataURL(file);
    }
});

function removeLogo() {
    if (confirm('Remove the company logo?')) {
        fetch('{{ route("company.settings.remove-logo") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(() => window.location.reload());
    }
}
</script>
@endsection
