@extends('layouts.app')

@section('title', 'Landed Cost Engine - Knowledge Base')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 mb-6">
        <a href="{{ route('help.index') }}" class="hover:text-blue-600">Help Center</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900">Landed Cost Engine</span>
    </nav>

    <article class="prose max-w-none bg-white p-8 rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">The Profit Brain: Landed Cost Engine</h1>
        
        <p class="lead text-xl text-gray-600 mb-8">
            Calculate your True Profit by tracking hidden costs like Freight, Duty, and Insurance.
        </p>

        <div class="bg-green-50, border-l-4 border-green-500 p-4 mb-8">
            <p class="font-bold text-green-800">The Problem</p>
            <p class="text-green-700">If you buy a chair for $50 but pay $10 in shipping, your cost is $60. If you sell it for $70, your profit is $10, not $20.</p>
        </div>

        <h3>How it Works</h3>
        <p>AgroWMS automatically distributes your shipping bills across the items in the container, so your inventory value is accurate.</p>

        <h3>Step-by-Step Guide</h3>
        <ol>
            <li>
                <strong>Add Expenses:</strong> On the Inbound Shipment page, look for the "Landed Costs" box.
                Click "Add Expense".
            </li>
            <li>
                <strong>Enter Details:</strong>
                <ul>
                    <li>Type: "Ocean Freight", "Customs Duty", etc.</li>
                    <li>Amount: The value from your invoice (e.g., $1,500).</li>
                    <li>Allocation Method:
                        <ul>
                            <li><strong>By Value (Recommended):</strong> Expensive items absorb more cost.</li>
                            <li><strong>By Quantity:</strong> Costs are split evenly per unit.</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>
                <strong>Receive the Shipment:</strong> When you click "One-Click Receive", the system calculates the math.
            </li>
        </ol>

        <h3>The Result</h3>
        <p>Go to the created <strong>Stock In</strong>. You will see a column for <code>Allocated Cost</code>. Your <code>Final Cost</code> (and thus your COGS) now includes the freight.</p>
    </article>
</div>
@endsection
