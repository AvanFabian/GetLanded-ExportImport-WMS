@extends('layouts.app')

@section('title', 'Approval Center')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Approval Center</h1>
            <p class="text-gray-600 text-sm mt-1">Review and approve pending transactions</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg text-sm font-medium">
                {{ $counts['total'] }} Pending Approvals
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ activeTab: 'stock-ins' }" class="mb-6">
        <div class="flex gap-2 border-b">
            <button @click="activeTab = 'stock-ins'"
                    :class="activeTab === 'stock-ins' ? 'border-b-2 border-emerald-600 text-emerald-600' : 'text-gray-500'"
                    class="px-4 py-3 font-medium transition">
                Stock In
                @if($counts['stock_ins'] > 0)
                    <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs">{{ $counts['stock_ins'] }}</span>
                @endif
            </button>
            <button @click="activeTab = 'stock-outs'"
                    :class="activeTab === 'stock-outs' ? 'border-b-2 border-emerald-600 text-emerald-600' : 'text-gray-500'"
                    class="px-4 py-3 font-medium transition">
                Stock Out
                @if($counts['stock_outs'] > 0)
                    <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs">{{ $counts['stock_outs'] }}</span>
                @endif
            </button>
        </div>

        {{-- Stock Ins Tab --}}
        <div x-show="activeTab === 'stock-ins'" class="mt-4">
            @if($pendingStockIns->isEmpty())
                <div class="bg-white rounded-xl border p-8 text-center text-gray-500">
                    <span class="text-4xl">✨</span>
                    <p class="mt-2">No pending Stock In transactions</p>
                </div>
            @else
                <div class="bg-white rounded-xl border overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Transaction</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Warehouse</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Created By</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($pendingStockIns as $stockIn)
                            <tr class="hover:bg-gray-50" id="stock-in-{{ $stockIn->id }}">
                                <td class="px-4 py-4">
                                    <a href="{{ route('stock-ins.show', $stockIn) }}" class="font-semibold text-blue-600 hover:underline">
                                        {{ $stockIn->transaction_code ?? 'SI-' . str_pad($stockIn->id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-gray-600">{{ $stockIn->warehouse?->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-gray-600">{{ $stockIn->createdBy?->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-gray-600">{{ $stockIn->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="approveTransaction('stock-in', {{ $stockIn->id }})"
                                                class="px-3 py-1.5 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 transition">
                                            ✓ Approve
                                        </button>
                                        <button onclick="rejectTransaction('stock-in', {{ $stockIn->id }})"
                                                class="px-3 py-1.5 bg-red-50 text-red-600 text-sm rounded-lg hover:bg-red-100 transition">
                                            ✕ Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Stock Outs Tab --}}
        <div x-show="activeTab === 'stock-outs'" class="mt-4" style="display: none;">
            @if($pendingStockOuts->isEmpty())
                <div class="bg-white rounded-xl border p-8 text-center text-gray-500">
                    <span class="text-4xl">✨</span>
                    <p class="mt-2">No pending Stock Out transactions</p>
                </div>
            @else
                <div class="bg-white rounded-xl border overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Transaction</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Warehouse</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Created By</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($pendingStockOuts as $stockOut)
                            <tr class="hover:bg-gray-50" id="stock-out-{{ $stockOut->id }}">
                                <td class="px-4 py-4">
                                    <a href="{{ route('stock-outs.show', $stockOut) }}" class="font-semibold text-blue-600 hover:underline">
                                        {{ $stockOut->transaction_code ?? 'SO-' . str_pad($stockOut->id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-gray-600">{{ $stockOut->warehouse?->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-gray-600">{{ $stockOut->createdBy?->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-gray-600">{{ $stockOut->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="approveTransaction('stock-out', {{ $stockOut->id }})"
                                                class="px-3 py-1.5 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 transition">
                                            ✓ Approve
                                        </button>
                                        <button onclick="rejectTransaction('stock-out', {{ $stockOut->id }})"
                                                class="px-3 py-1.5 bg-red-50 text-red-600 text-sm rounded-lg hover:bg-red-100 transition">
                                            ✕ Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Reject Transaction</h3>
        <form id="rejectForm">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection *</label>
                <textarea id="rejectReason" name="reason" rows="3" required
                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500"
                          placeholder="Please provide a reason..."></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 border text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentRejectType = null;
let currentRejectId = null;

function approveTransaction(type, id) {
    if (!confirm('Approve this transaction?')) return;
    
    fetch(`/approvals/${type}/${id}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`${type}-${id}`).remove();
            alert('Transaction approved!');
            location.reload();
        } else {
            alert(data.error || 'Failed to approve');
        }
    });
}

function rejectTransaction(type, id) {
    currentRejectType = type;
    currentRejectId = id;
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
    document.getElementById('rejectReason').value = '';
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
}

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const reason = document.getElementById('rejectReason').value;
    
    fetch(`/approvals/${currentRejectType}/${currentRejectId}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ reason })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeRejectModal();
            document.getElementById(`${currentRejectType}-${currentRejectId}`).remove();
            alert('Transaction rejected!');
            location.reload();
        } else {
            alert(data.error || 'Failed to reject');
        }
    });
});
</script>
@endsection
