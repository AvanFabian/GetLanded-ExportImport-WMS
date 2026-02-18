@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Claim') }} #{{ $claim->id }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ ucfirst($claim->claim_type) }} {{ __('claim detail') }}</p>
         </div>
         <a href="{{ route('claims.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <!-- Claim Info -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <dl class="space-y-2">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Sales Order') }}</dt>
                  <dd class="text-sm font-medium"><a href="{{ route('sales-orders.show', $claim->salesOrder) }}" class="text-blue-600 hover:text-blue-900">{{ $claim->salesOrder->so_number }}</a></dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Customer') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $claim->salesOrder->customer->name ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Type') }}</dt>
                  <dd><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $claim->claim_type === 'damage' ? 'bg-red-100 text-red-800' : ($claim->claim_type === 'shortage' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800') }}">{{ ucfirst($claim->claim_type) }}</span></dd></div>
               @if($claim->insurance_policy_number)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Insurance Policy') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $claim->insurance_policy_number }}</dd></div>
               @endif
            </dl>
            <dl class="space-y-2">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Status') }}</dt>
                  <dd>
                     @php $colors = ['open' => 'yellow', 'submitted' => 'blue', 'settled' => 'green', 'rejected' => 'red']; @endphp
                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $colors[$claim->status] ?? 'gray' }}-100 text-{{ $colors[$claim->status] ?? 'gray' }}-800">{{ ucfirst($claim->status) }}</span>
                  </dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Claimed Amount') }}</dt>
                  <dd class="text-lg font-bold text-red-700">Rp {{ number_format($claim->claimed_amount, 0, ',', '.') }}</dd></div>
               @if($claim->settled_amount)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Settled Amount') }}</dt>
                     <dd class="text-lg font-bold text-emerald-700">Rp {{ number_format($claim->settled_amount, 0, ',', '.') }}</dd></div>
               @endif
            </dl>
         </div>
         <div class="mt-4 pt-4 border-t">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">{{ __('Description') }}</h3>
            <p class="text-sm text-gray-700">{{ $claim->description }}</p>
         </div>
      </div>

      <!-- Evidence -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Evidence') }} ({{ $claim->evidences->count() }})</h3>

         @if($claim->evidences->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
               @foreach($claim->evidences as $evidence)
                  <div class="border rounded-lg p-2">
                     @if(str_starts_with($evidence->file_type, 'image/'))
                        <img src="{{ Storage::url($evidence->file_path) }}" alt="Evidence" class="w-full h-32 object-cover rounded">
                     @else
                        <div class="w-full h-32 bg-gray-100 rounded flex items-center justify-center">
                           <span class="text-2xl">📄</span>
                        </div>
                     @endif
                     <a href="{{ Storage::url($evidence->file_path) }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-900 mt-1 block truncate">{{ __('View') }}</a>
                  </div>
               @endforeach
            </div>
         @endif

         @if(in_array($claim->status, ['open', 'submitted']))
            <form method="POST" action="{{ route('claims.upload-evidence', $claim) }}" enctype="multipart/form-data" class="flex gap-3 items-end">
               @csrf
               <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Upload Evidence') }}</label>
                  <input type="file" name="evidence" required accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
               </div>
               <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition text-sm">{{ __('Upload') }}</button>
            </form>
         @endif
      </div>

      <!-- Actions -->
      <div class="flex gap-3 justify-end">
         @if($claim->status === 'open')
            <form method="POST" action="{{ route('claims.submit', $claim) }}">
               @csrf
               <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">📤 {{ __('Submit Claim') }}</button>
            </form>
         @endif
         @if($claim->status === 'submitted')
            <form method="POST" action="{{ route('claims.settle', $claim) }}" class="flex gap-2 items-end">
               @csrf
               <div>
                  <label class="block text-xs text-gray-600 mb-1">{{ __('Settled Amt') }}</label>
                  <input type="number" step="0.01" name="settled_amount" value="{{ $claim->claimed_amount }}" required
                     class="w-40 px-3 py-2 border border-gray-300 rounded-lg text-sm">
               </div>
               <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition text-sm">✓ {{ __('Settle') }}</button>
            </form>
            <form method="POST" action="{{ route('claims.reject', $claim) }}">
               @csrf
               <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition text-sm"
                  onclick="return confirm('{{ __('Reject this claim?') }}')">✕ {{ __('Reject') }}</button>
            </form>
         @endif
      </div>
   </div>
@endsection
