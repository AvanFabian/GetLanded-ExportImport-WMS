@extends('layouts.app')

@section('title', __('app.suppliers'))

@section('content')
   <div x-data="{ showImportModal: false }" class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold mb-4">{{ __('app.suppliers') }}</h2>
         <div class="flex gap-2">
            <button @click="showImportModal = true" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
               <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
               Import
            </button>
            <a href="{{ route('suppliers.create') }}"
               class="px-3 py-2 bg-primary text-white rounded">{{ __('app.add_supplier') }}</a>
         </div>
      </div>

      <form method="GET" class="mb-4">
         <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="{{ __('app.search') }}"
            class="border rounded px-2 py-1" />
         <button class="ml-2 px-3 py-1 bg-secondary text-white rounded">{{ __('app.search') }}</button>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <table class="min-w-full">
            <thead class="bg-gray-50">
               <tr>
                  <th class="text-left p-3">{{ __('app.name') }}</th>
                  <th class="text-left p-3">{{ __('app.phone') }}</th>
                  <th class="text-left p-3">{{ __('app.email') }}</th>
                  <th class="text-left p-3">{{ __('app.action') }}</th>
               </tr>
            </thead>
            <tbody>
               @forelse($suppliers as $s)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3">{{ $s->name }}</td>
                     <td class="p-3">{{ $s->phone }}</td>
                     <td class="p-3">{{ $s->email }}</td>
                     <td class="p-3">
                        <a href="{{ route('suppliers.edit', $s) }}" class="text-blue-600 mr-2">{{ __('app.edit') }}</a>
                        <form action="{{ route('suppliers.destroy', $s) }}" method="POST" class="inline-block"
                           onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                           @csrf
                           @method('DELETE')
                           <button class="text-red-600">{{ __('app.delete') }}</button>
                        </form>
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="4" class="p-12">
                        <div class="text-center">
                           <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                           </svg>
                           <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('app.no_suppliers') }}</h3>
                           <p class="mt-1 text-sm text-gray-500">
                              {{ __('app.get_started', ['item' => __('app.supplier')]) }}</p>
                           <div class="mt-6">
                              <a href="{{ route('suppliers.create') }}"
                                 class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                 <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M12 4v16m8-8H4" />
                                 </svg>
                                 {{ __('app.add_supplier') }}
                              </a>
                           </div>
                        </div>
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>

      <div class="mt-4">{{ $suppliers->links() }}</div>

      <!-- Import Modal -->
      <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
         <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
               <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                  <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
               </div>

               <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

               <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                  <form action="{{ route('suppliers.import') }}" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                 <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                 </svg>
                              </div>
                              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                 <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Import Suppliers
                                 </h3>
                                 <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                       Upload your XLSX or CSV file to import suppliers in bulk.
                                    </p>
                                    <div class="mt-4">
                                       <label class="block text-sm font-medium text-gray-700">File</label>
                                       <input type="file" name="file" accept=".csv, .xlsx, .xls" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                 </div>
                              </div>
                        </div>
                     </div>
                     <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                              Import
                        </button>
                        <button type="button" @click="showImportModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                              Cancel
                        </button>
                     </div>
                  </form>
               </div>
         </div>
      </div>
   </div>
@endsection
