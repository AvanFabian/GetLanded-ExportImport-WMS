@extends('layouts.app')

@section('title', 'Business Rules')

@section('content')
   <div class="max-w-4xl mx-auto p-4 sm:p-6" x-data="businessRulesForm()">
      <div class="flex items-center justify-between mb-6">
         <div>
            <h2 class="text-2xl font-bold text-gray-900">Business Rules</h2>
            <p class="text-sm text-gray-500 mt-1">Configure how your company operates within the system</p>
         </div>
         <a href="{{ route('settings.index') }}" class="text-primary hover:text-primary/80 text-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Settings
         </a>
      </div>

      <form method="POST" action="{{ route('settings.business-rules.update') }}" class="space-y-6">
         @csrf
         @method('PUT')

         @foreach($groups as $groupKey => $groupMeta)
            @if(isset($groupedPolicies[$groupKey]))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
               {{-- Group Header --}}
               <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                  <div class="flex items-center gap-3">
                     @if($groupMeta['icon'] === 'check-circle')
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                           <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                           </svg>
                        </div>
                     @elseif($groupMeta['icon'] === 'file-text')
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                           <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                           </svg>
                        </div>
                     @elseif($groupMeta['icon'] === 'package')
                        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                           <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                           </svg>
                        </div>
                     @elseif($groupMeta['icon'] === 'box')
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                           <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                           </svg>
                        </div>
                     @endif
                     <h3 class="text-lg font-semibold text-gray-900">{{ $groupMeta['label'] }}</h3>
                  </div>
               </div>

               {{-- Policy Items --}}
               <div class="divide-y divide-gray-100">
                  @foreach($groupedPolicies[$groupKey] as $policyKey => $policy)
                     <div class="px-6 py-5">
                        <div class="flex items-start justify-between gap-4">
                           <div class="flex-1">
                              <label for="{{ $policyKey }}" class="text-sm font-medium text-gray-900 cursor-pointer">
                                 {{ $policy['meta']['label'] }}
                              </label>
                              <p class="mt-1 text-sm text-gray-500">{{ $policy['meta']['description'] }}</p>
                           </div>
                           
                           @if($policy['meta']['type'] === 'boolean')
                              {{-- Toggle Switch --}}
                              <div class="flex-shrink-0">
                                 <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="{{ $policyKey }}" value="0">
                                    <input type="checkbox" 
                                           name="{{ $policyKey }}" 
                                           id="{{ $policyKey }}"
                                           value="1"
                                           {{ $policy['value'] ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                 </label>
                              </div>
                           @elseif($policy['meta']['type'] === 'enum')
                              {{-- Dropdown Selector --}}
                              <div class="flex-shrink-0 w-40">
                                 <select name="{{ $policyKey }}" 
                                         id="{{ $policyKey }}"
                                         class="w-full text-sm border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                    @foreach($policy['meta']['options'] as $option)
                                       <option value="{{ $option }}" {{ $policy['value'] === $option ? 'selected' : '' }}>
                                          {{ ucfirst($option) }}
                                       </option>
                                    @endforeach
                                 </select>
                              </div>
                           @endif
                        </div>
                     </div>
                  @endforeach
               </div>
            </div>
            @endif
         @endforeach

         {{-- Action Buttons --}}
         <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('settings.index') }}"
               class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
               Cancel
            </a>
            <button type="submit" 
                    class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-primary/90 transition font-medium flex items-center gap-2">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
               </svg>
               Save Changes
            </button>
         </div>
      </form>

      {{-- Info Card --}}
      <div class="mt-8 bg-blue-50 border border-blue-100 rounded-xl p-4">
         <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
               <h4 class="text-sm font-medium text-blue-900">Policy Changes</h4>
               <p class="mt-1 text-sm text-blue-700">All policy changes are logged in the security audit trail. Changes take effect immediately for all users in your company.</p>
            </div>
         </div>
      </div>
   </div>

   <script>
      function businessRulesForm() {
         return {
            // Any additional Alpine.js logic can go here
         }
      }
   </script>
@endsection
