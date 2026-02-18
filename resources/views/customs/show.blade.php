@extends('layouts.app')

@section('content')
   <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $custom->declaration_number ?? __('Draft Declaration') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Customs Declaration Detail') }}</p>
         </div>
         <a href="{{ route('customs.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
         <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-3">{{ __('Declaration Info') }}</h3>
            <dl class="space-y-2">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Type') }}</dt>
                  <dd><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $custom->declaration_type === 'export' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">{{ ucfirst($custom->declaration_type) }}</span></dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Date') }}</dt><dd class="text-sm font-medium">{{ $custom->declaration_date->format('d M Y') }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Customs Office') }}</dt><dd class="text-sm font-medium">{{ $custom->customs_office ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('HS Code') }}</dt><dd class="text-sm font-mono font-medium">{{ $custom->hs_code ?? '-' }}</dd></div>
               @if($custom->outboundShipment)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Shipment') }}</dt>
                     <dd class="text-sm font-medium"><a href="{{ route('outbound-shipments.show', $custom->outboundShipment) }}" class="text-blue-600">{{ $custom->outboundShipment->shipment_number }}</a></dd></div>
               @endif
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Status') }}</dt>
                  <dd>@php $colors = ['draft'=>'gray','submitted'=>'blue','assessed'=>'yellow','paid'=>'indigo','cleared'=>'green','rejected'=>'red']; @endphp
                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $colors[$custom->status] ?? 'gray' }}-100 text-{{ $colors[$custom->status] ?? 'gray' }}-800">{{ \App\Models\CustomsDeclaration::STATUSES[$custom->status] ?? ucfirst($custom->status) }}</span>
                  </dd></div>
            </dl>
         </div>
         <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-3">{{ __('Duty & Tax') }}</h3>
            <dl class="space-y-3">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Declared Value') }}</dt><dd class="text-lg font-bold text-gray-900">{{ $custom->currency_code }} {{ number_format($custom->declared_value, 2) }}</dd></div>
               @if($custom->fta_scheme)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('FTA Scheme') }}</dt><dd class="text-sm font-medium text-blue-600">{{ $custom->fta_scheme }}</dd></div>
               @endif
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('BM / Duty') }} ({{ $custom->duty_rate }}%)</dt><dd class="text-sm font-medium">{{ number_format($custom->duty_amount, 2) }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('PPN / VAT') }} ({{ $custom->vat_rate }}%)</dt><dd class="text-sm font-medium">{{ number_format($custom->vat_amount, 2) }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('PPh 22') }} ({{ $custom->pph_rate ?? 0 }}%)</dt><dd class="text-sm font-medium">{{ number_format($custom->pph_amount ?? 0, 2) }}</dd></div>
               @if(($custom->anti_dumping_rate ?? 0) > 0)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('BMAD / Anti-dumping') }} ({{ $custom->anti_dumping_rate }}%)</dt><dd class="text-sm font-medium">{{ number_format($custom->anti_dumping_amount, 2) }}</dd></div>
               @endif
               @if($custom->excise_amount > 0)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Excise / Cukai') }}</dt><dd class="text-sm font-medium">{{ number_format($custom->excise_amount, 2) }}</dd></div>
               @endif
               <div class="flex justify-between border-t pt-2"><dt class="text-sm font-bold text-gray-900">{{ __('Total Tax') }}</dt><dd class="text-xl font-bold text-red-600">{{ $custom->currency_code }} {{ number_format($custom->total_tax, 2) }}</dd></div>
            </dl>
         </div>
      </div>

      <!-- Status Update -->
      @if(!in_array($custom->status, ['cleared', 'rejected']))
         <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Update Status') }}</h3>
            <form method="POST" action="{{ route('customs.update-status', $custom) }}" class="flex gap-4 items-end">
               @csrf
               <div class="flex-1">
                  <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                     @foreach(\App\Models\CustomsDeclaration::STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ $custom->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                     @endforeach
                  </select>
               </div>
               <div>
                  <input type="text" name="declaration_number" value="{{ $custom->declaration_number }}" placeholder="{{ __('Declaration #') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">{{ __('Update') }}</button>
            </form>
         </div>
      @endif

      <!-- Declaration Items -->
      @if($custom->items->count() > 0)
         <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b"><h3 class="text-lg font-bold text-gray-900">{{ __('Declaration Items') }}</h3></div>
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Product') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Description') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('HS Code') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Qty') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Unit Value') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Total Value') }}</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-gray-200">
                  @foreach($custom->items as $item)
                     <tr>
                        <td class="px-6 py-4 text-sm">{{ $item->product->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->description }}</td>
                        <td class="px-6 py-4 text-sm font-mono">{{ $item->hs_code ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-right">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 text-sm text-right">{{ number_format($item->unit_value, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-bold">{{ number_format($item->total_value, 2) }}</td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      @endif

      @if($custom->notes)
         <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">{{ __('Notes') }}</h3>
            <p class="text-sm text-gray-700">{{ $custom->notes }}</p>
         </div>
      @endif
   </div>
@endsection
