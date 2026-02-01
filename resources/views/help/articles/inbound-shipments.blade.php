@extends('layouts.app')

@section('title', 'Inbound Shipments & Digital Vault - Knowledge Base')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 mb-6">
        <a href="{{ route('help.index') }}" class="hover:text-blue-600">Help Center</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900">Inbound Shipments</span>
    </nav>

    <article class="prose max-w-none bg-white p-8 rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Mastering Inbound Shipments & The Digital Vault</h1>
        <p class="text-gray-600 mb-6">
            Efficiently managing incoming goods is the foundation of accurate inventory. **GetLanded** provides a structured workflow for recording, verifying, and shelving stock from suppliers or inter-warehouse transfers.
        </p>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8">
            <p class="font-bold text-blue-800">Why use this?</p>
            <p class="text-blue-700">Instead of receiving 10 separate Purchase Orders manually, you can group them into one "Container" (Shipment) and receive everything with a single click.</p>
        </div>

        <h3>1. Creating a Shipment</h3>
        <ol>
            <li>Go to <strong>Purchasing > Inbound Shipments</strong>.</li>
            <li>Click <strong>New Shipment</strong>.</li>
            <li>Select the <strong>Purchase Orders</strong> that are being loaded into this container.</li>
            <li>Enter the <strong>Carrier Name</strong> and <strong>Reference Number</strong> (e.g., Container Number).</li>
        </ol>

        <h3>2. The Digital Vault (Phase 3)</h3>
        <p>Every shipment comes with a secure file storage system. You should upload:</p>
        <ul>
            <li><strong>Bill of Lading (B/L):</strong> The legal title to the goods.</li>
            <li><strong>Packing List:</strong> Essential for customs clearing.</li>
            <li><strong>Commercial Invoice:</strong> Required for tax calculation.</li>
        </ul>
        <p>To upload, simply drag-and-drop files into the "Digital Vault" section on the Shipment page.</p>

        <h3>3. One-Click Receive</h3>
        <p>When the truck arrives at your warehouse:</p>
        <ol>
            <li>Open the Shipment in GetLanded.</li>
            <li>Verify the contents match the paper packing list.</li>
            <li>Click the green <strong>One-Click Receive</strong> button.</li>
        </ol>
        <p>The system will automatically create a <strong>Stock In</strong> record for all items, closing the purchase orders and updating your inventory instantly.</p>
    </article>
</div>
@endsection
