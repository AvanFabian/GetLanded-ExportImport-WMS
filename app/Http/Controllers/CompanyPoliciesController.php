<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyPoliciesController extends Controller
{
    /**
     * Available policies with their metadata
     */
    protected array $policies = [
        'require_approval_workflow' => [
            'type' => 'boolean',
            'label' => 'Require Approval Workflow',
            'description' => 'When enabled, transactions require approval from a different user. When disabled, creators can self-approve.',
            'group' => 'workflow',
            'default' => true,
        ],
        'invoice_sequence_logic' => [
            'type' => 'enum',
            'options' => ['strict', 'flexible'],
            'label' => 'Invoice Generation Mode',
            'description' => 'Strict: Invoice can only be generated after stock-out. Flexible: Invoice can be generated at any stage.',
            'group' => 'documents',
            'default' => 'strict',
        ],
        'uom_conversion_enabled' => [
            'type' => 'boolean',
            'label' => 'Enable Unit Conversions',
            'description' => 'Allow defining and using unit of measurement conversions (e.g., 1 Bag = 50 KG).',
            'group' => 'products',
            'default' => true,
        ],
        'auto_void_on_rejection' => [
            'type' => 'boolean',
            'label' => 'Auto-Void on Rejection',
            'description' => 'Automatically void related documents when a transaction is rejected.',
            'group' => 'workflow',
            'default' => false,
        ],
        'stock_limit_mode' => [
            'type' => 'enum',
            'options' => ['block', 'warning'],
            'label' => 'Stock Limit Mode',
            'description' => 'Block: Prevent orders exceeding available stock. Warning: Allow with warning (pre-selling).',
            'group' => 'inventory',
            'default' => 'block',
        ],
    ];

    public function index()
    {
        $company = Company::find(auth()->user()->company_id);

        // Get current policy values
        $currentPolicies = [];
        foreach ($this->policies as $key => $meta) {
            $currentPolicies[$key] = [
                'value' => $company?->$key ?? $meta['default'],
                'meta' => $meta,
            ];
        }

        // Group policies
        $groupedPolicies = collect($currentPolicies)->groupBy(fn($p) => $p['meta']['group']);

        return Inertia::render('Settings/BusinessRules', [
            'policies' => $currentPolicies,
            'groupedPolicies' => $groupedPolicies,
            'groups' => [
                'workflow' => ['label' => 'Workflow & Approvals', 'icon' => 'check-circle'],
                'documents' => ['label' => 'Document Generation', 'icon' => 'file-text'],
                'products' => ['label' => 'Products & Units', 'icon' => 'package'],
                'inventory' => ['label' => 'Inventory Management', 'icon' => 'box'],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'require_approval_workflow' => 'nullable|boolean',
            'invoice_sequence_logic' => 'nullable|in:strict,flexible',
            'uom_conversion_enabled' => 'nullable|boolean',
            'auto_void_on_rejection' => 'nullable|boolean',
            'stock_limit_mode' => 'nullable|in:block,warning',
        ]);

        $company = Company::find(auth()->user()->company_id);

        // Update only provided fields
        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $company->$key = $value;
            }
        }

        $company->save();

        // Log policy change
        \App\Models\SecurityLog::create([
            'company_id' => $company->id,
            'user_id' => auth()->id(),
            'event' => 'policy_changed',
            'severity' => 'info',
            'details' => [
                'changes' => $validated,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Business rules updated successfully.');
    }

    /**
     * Get current policies as JSON (for AJAX/API)
     */
    public function current()
    {
        $company = Company::find(auth()->user()->company_id);

        $policies = [];
        foreach ($this->policies as $key => $meta) {
            $policies[$key] = $company?->$key ?? $meta['default'];
        }

        return response()->json([
            'policies' => $policies,
        ]);
    }
}
