@extends('layouts.app')

@section('title', 'Unit Conversions')

@section('content')
   <div class="max-w-5xl mx-auto p-4 sm:p-6" x-data="uomManager()">
      {{-- Header --}}
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
         <div>
            <h2 class="text-2xl font-bold text-gray-900">Unit of Measure Conversions</h2>
            <p class="text-sm text-gray-500 mt-1">Define how different units convert to each other</p>
         </div>
         <div class="flex gap-2">
            <a href="{{ route('settings.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900 text-sm flex items-center gap-1">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
               </svg>
               Back
            </a>
            <button @click="showAddModal = true" 
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition text-sm font-medium flex items-center gap-2">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
               </svg>
               Add Conversion
            </button>
         </div>
      </div>

      {{-- Quick Add Section --}}
      <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl border border-emerald-100 p-5 mb-6">
         <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
               <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
               </svg>
            </div>
            <div>
               <h3 class="font-semibold text-gray-900">Quick Add Common Conversions</h3>
               <p class="text-sm text-gray-600 mt-0.5">Click to instantly add popular unit conversions</p>
            </div>
         </div>
         <div class="flex flex-wrap gap-2">
            @foreach($commonConversions as $common)
               <form action="{{ route('settings.uom-conversions.add-common') }}" method="POST" class="inline">
                  @csrf
                  <input type="hidden" name="name" value="{{ $common['name'] }}">
                  <button type="submit" 
                          class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-emerald-200 rounded-lg text-sm text-gray-700 hover:border-emerald-400 hover:bg-emerald-50 transition group">
                     <span class="font-medium text-emerald-700">1 {{ $common['from'] }}</span>
                     <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                     </svg>
                     <span class="font-medium text-emerald-700">{{ $common['factor'] }} {{ $common['to'] }}</span>
                  </button>
               </form>
            @endforeach
         </div>
      </div>

      {{-- Existing Conversions Table --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
         <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-900">Your Conversions</h3>
         </div>
         
         @if($conversions->count() > 0)
         <div class="overflow-x-auto">
            <table class="w-full">
               <thead>
                  <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                     <th class="px-6 py-3">From Unit</th>
                     <th class="px-6 py-3">To Unit</th>
                     <th class="px-6 py-3">Factor</th>
                     <th class="px-6 py-3">Product</th>
                     <th class="px-6 py-3">Status</th>
                     <th class="px-6 py-3 text-right">Actions</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-gray-100">
                  @foreach($conversions as $conversion)
                  <tr class="hover:bg-gray-50 transition">
                     <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-blue-100 text-blue-800 text-sm font-medium">
                           {{ $conversion->from_unit }}
                        </span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-purple-100 text-purple-800 text-sm font-medium">
                           {{ $conversion->to_unit }}
                        </span>
                     </td>
                     <td class="px-6 py-4 font-mono text-sm">
                        1 {{ $conversion->from_unit }} = <span class="font-bold text-gray-900">{{ number_format($conversion->conversion_factor, 4) }}</span> {{ $conversion->to_unit }}
                     </td>
                     <td class="px-6 py-4 text-sm text-gray-600">
                        @if($conversion->product)
                           <span class="text-gray-900">{{ $conversion->product->name }}</span>
                        @else
                           <span class="text-gray-400 italic">Global</span>
                        @endif
                     </td>
                     <td class="px-6 py-4">
                        @if($conversion->is_active)
                           <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                              Active
                           </span>
                        @else
                           <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                              Inactive
                           </span>
                        @endif
                     </td>
                     <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                           <button @click="editConversion({{ $conversion->id }}, {{ $conversion->conversion_factor }})" 
                                   class="p-1.5 text-gray-400 hover:text-blue-600 transition">
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                              </svg>
                           </button>
                           <form action="{{ route('settings.uom-conversions.destroy', $conversion) }}" method="POST" 
                                 onsubmit="return confirm('Delete this conversion?');">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 transition">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                 </svg>
                              </button>
                           </form>
                        </div>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         @else
         <div class="px-6 py-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
            </svg>
            <h3 class="text-gray-900 font-medium mb-1">No conversions defined</h3>
            <p class="text-gray-500 text-sm mb-4">Start by adding a conversion or use the quick-add buttons above</p>
            <button @click="showAddModal = true" class="text-primary hover:text-primary/80 text-sm font-medium">
               + Add your first conversion
            </button>
         </div>
         @endif
      </div>

      {{-- Conversion Calculator --}}
      <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
         <h3 class="font-semibold text-gray-900 mb-4">Conversion Calculator</h3>
         <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
               <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
               <input type="number" x-model="calcQty" step="any" 
                      class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
            </div>
            <div>
               <label class="block text-sm font-medium text-gray-700 mb-1">From Unit</label>
               <input type="text" x-model="calcFrom" placeholder="e.g. BAG"
                      class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary uppercase">
            </div>
            <div>
               <label class="block text-sm font-medium text-gray-700 mb-1">To Unit</label>
               <input type="text" x-model="calcTo" placeholder="e.g. KG"
                      class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary uppercase">
            </div>
            <div>
               <button @click="calculate()" 
                       class="w-full px-4 py-2.5 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium">
                  Convert
               </button>
            </div>
         </div>
         <div x-show="calcResult" x-cloak class="mt-4 p-4 bg-emerald-50 rounded-lg border border-emerald-100">
            <p class="text-emerald-800 font-medium" x-text="calcResult"></p>
         </div>
         <div x-show="calcError" x-cloak class="mt-4 p-4 bg-red-50 rounded-lg border border-red-100">
            <p class="text-red-700 text-sm" x-text="calcError"></p>
         </div>
      </div>

      {{-- Add Conversion Modal --}}
      <div x-show="showAddModal" x-cloak
           class="fixed inset-0 z-50 overflow-y-auto" 
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0"
           x-transition:enter-end="opacity-100"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="opacity-100"
           x-transition:leave-end="opacity-0">
         <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" @click="showAddModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
               <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New Conversion</h3>
               <form action="{{ route('settings.uom-conversions.store') }}" method="POST">
                  @csrf
                  <div class="space-y-4">
                     <div class="grid grid-cols-2 gap-4">
                        <div>
                           <label class="block text-sm font-medium text-gray-700 mb-1">From Unit *</label>
                           <input type="text" name="from_unit" required 
                                  placeholder="e.g. BAG"
                                  class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary uppercase">
                        </div>
                        <div>
                           <label class="block text-sm font-medium text-gray-700 mb-1">To Unit *</label>
                           <input type="text" name="to_unit" required 
                                  placeholder="e.g. KG"
                                  class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary uppercase">
                        </div>
                     </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conversion Factor *</label>
                        <input type="number" name="conversion_factor" required step="any" min="0.0000000001"
                               placeholder="e.g. 50"
                               class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        <p class="text-xs text-gray-500 mt-1">1 [From Unit] = [Factor] [To Unit]</p>
                     </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product (Optional)</label>
                        <select name="product_id" class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                           <option value="">Global (all products)</option>
                           @foreach($products as $product)
                              <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code }})</option>
                           @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Leave empty for global conversion</p>
                     </div>
                     <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="is_default" value="1"
                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <label for="is_default" class="ml-2 text-sm text-gray-700">Set as default conversion for these units</label>
                     </div>
                  </div>
                  <div class="flex justify-end gap-3 mt-6">
                     <button type="button" @click="showAddModal = false" 
                             class="px-4 py-2 text-gray-700 hover:text-gray-900 transition">
                        Cancel
                     </button>
                     <button type="submit" 
                             class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition font-medium">
                        Add Conversion
                     </button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>

   <script>
      function uomManager() {
         return {
            showAddModal: false,
            calcQty: 1,
            calcFrom: '',
            calcTo: '',
            calcResult: '',
            calcError: '',
            
            editConversion(id, factor) {
               const newFactor = prompt('Enter new conversion factor:', factor);
               if (newFactor !== null && newFactor !== '') {
                  const form = document.createElement('form');
                  form.method = 'POST';
                  form.action = `/settings/uom-conversions/${id}`;
                  form.innerHTML = `
                     @csrf
                     @method('PUT')
                     <input type="hidden" name="conversion_factor" value="${newFactor}">
                  `;
                  document.body.appendChild(form);
                  form.submit();
               }
            },
            
            async calculate() {
               this.calcResult = '';
               this.calcError = '';
               
               if (!this.calcQty || !this.calcFrom || !this.calcTo) {
                  this.calcError = 'Please fill in all fields';
                  return;
               }
               
               try {
                  const response = await fetch('/api/uom/convert', {
                     method: 'POST',
                     headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                     },
                     body: JSON.stringify({
                        quantity: this.calcQty,
                        from_unit: this.calcFrom.toUpperCase(),
                        to_unit: this.calcTo.toUpperCase(),
                     })
                  });
                  
                  const data = await response.json();
                  
                  if (data.error) {
                     this.calcError = data.error;
                  } else {
                     this.calcResult = `${this.calcQty} ${this.calcFrom.toUpperCase()} = ${data.result.toFixed(4)} ${this.calcTo.toUpperCase()}`;
                  }
               } catch (e) {
                  this.calcError = 'Conversion failed. Check if the conversion exists.';
               }
            }
         }
      }
   </script>
@endsection
