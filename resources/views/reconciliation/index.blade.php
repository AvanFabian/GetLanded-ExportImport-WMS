@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Payment Reconciliation') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Match outgoing payments with bank statements') }}</p>
         </div>
      </div>

      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">{{ __('Unreconciled Payments') }}</h3>
            <span class="text-sm text-gray-500">{{ $unreconciledPayments->total() }} pending</span>
         </div>
         
         @if($unreconciledPayments->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date / Due') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Supplier') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Reference') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount Paid') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-gray-200">
                  @foreach($unreconciledPayments as $payment)
                  <tr x-data="{ open: false }">
                     <td class="px-6 py-4 text-sm text-gray-900">
                        <div>{{ $payment->created_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">Due: {{ $payment->due_date?->format('d M Y') ?? '-' }}</div>
                     </td>
                     <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $payment->supplier->name ?? '-' }}</td>
                     <td class="px-6 py-4 text-sm text-gray-600">
                        <div>{{ $payment->payment_method ? ucwords(str_replace('_', ' ', $payment->payment_method)) : 'Bank Transfer' }}</div>
                        @if($payment->bank_reference)
                           <div class="text-xs text-gray-500">Ref: {{ $payment->bank_reference }}</div>
                        @endif
                     </td>
                     <td class="px-6 py-4 text-sm text-right font-bold text-emerald-700">
                        {{ $payment->currency_code ?? 'IDR' }} {{ number_format($payment->amount_paid, 2) }}
                     </td>
                     <td class="px-6 py-4 text-right">
                        <button @click="open = !open" class="text-blue-600 hover:text-blue-800 text-sm font-medium">{{ __('Reconcile') }}</button>
                        
                        <!-- Inline Reconcile Form -->
                        <div x-show="open" class="absolute right-10 mt-2 w-72 bg-white rounded-lg shadow-xl border p-4 z-10" @click.away="open = false" style="display: none;">
                           <form action="{{ route('reconciliation.update', $payment) }}" method="POST">
                              @csrf
                              @method('PATCH')
                              <div class="mb-3 text-left">
                                 <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Reconciled Date') }}</label>
                                 <input type="date" name="reconciled_at" value="{{ date('Y-m-d') }}" required class="w-full px-2 py-1 border rounded text-sm">
                              </div>
                              <div class="mb-3 text-left">
                                 <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Bank Statement Ref') }}</label>
                                 <input type="text" name="bank_statement_ref" placeholder="e.g. STMT-001" class="w-full px-2 py-1 border rounded text-sm">
                              </div>
                              <div class="flex justify-end gap-2">
                                 <button type="button" @click="open = false" class="px-3 py-1 text-xs bg-gray-200 rounded">{{ __('Cancel') }}</button>
                                 <button type="submit" class="px-3 py-1 text-xs bg-emerald-600 text-white rounded">{{ __('Confirm') }}</button>
                              </div>
                           </form>
                        </div>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
            <div class="px-6 py-4 border-t">
               {{ $unreconciledPayments->links() }}
            </div>
         @else
            <div class="p-12 text-center text-gray-500">
               <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
               <h3 class="text-lg font-medium text-gray-900">{{ __('All caught up!') }}</h3>
               <p>{{ __('No unreconciled payments found.') }}</p>
            </div>
         @endif
      </div>
   </div>
@endsection
