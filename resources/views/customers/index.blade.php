@extends('layouts.app')

@section('content')
   <div x-data="{ showImportModal: false }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-6 flex justify-between items-center">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('app.customers') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('app.customer_management') }}</p>
         </div>
         <div class="flex gap-2">
            <button @click="showImportModal = true" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
               Import
            </button>
            <a href="{{ route('customers.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
               </svg>
               {{ __('app.add_customer') }}
            </a>
         </div>
      </div>

      <!-- Success/Error Messages -->
      @if (session('success'))
         <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
         </div>
      @endif

      @if (session('error'))
         <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
         </div>
      @endif

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form action="{{ route('customers.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
               <label for="search"
                  class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.search_customer') }}</label>
               <input type="text" id="search" name="search" value="{{ request('search') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="{{ __('app.search_name_phone_email') }}">
            </div>

            <div>
               <label for="status" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.status') }}</label>
               <select id="status" name="status"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="">{{ __('app.all_statuses') }}</option>
                  <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('app.active') }}
                  </option>
                  <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                     {{ __('app.inactive') }}</option>
               </select>
            </div>

            <div class="flex items-end gap-2">
               <button type="submit"
                  class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-150">
                  {{ __('app.filter') }}
               </button>
               <a href="{{ route('customers.index') }}"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-150">
                  {{ __('app.reset') }}
               </a>
            </div>
         </form>
      </div>

      <!-- Customers Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.name') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.contact') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.npwp') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.status') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.created_date') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.actions') }}</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  @forelse($customers as $customer)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                           <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                           @if ($customer->address)
                              <div class="text-sm text-gray-500">{{ Str::limit($customer->address, 50) }}</div>
                           @endif
                        </td>
                        <td class="px-6 py-4">
                           @if ($customer->phone)
                              <div class="text-sm text-gray-900">{{ $customer->phone }}</div>
                           @endif
                           @if ($customer->email)
                              <div class="text-sm text-gray-500">{{ $customer->email }}</div>
                           @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           {{ $customer->tax_id ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                           @if ($customer->is_active)
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                 {{ __('app.active') }}
                              </span>
                           @else
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                 {{ __('app.inactive') }}
                              </span>
                           @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                           {{ $customer->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-medium">
                           <div class="flex justify-center gap-2">
                              <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-900"
                                 title="{{ __('app.view') }}">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                 </svg>
                              </a>
                              <a href="{{ route('customers.edit', $customer) }}"
                                 class="text-yellow-600 hover:text-yellow-900" title="{{ __('app.edit') }}">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                 </svg>
                              </a>
                              <button onclick="confirmDelete({{ $customer->id }})" class="text-red-600 hover:text-red-900"
                                 title="{{ __('app.delete') }}">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                 </svg>
                              </button>
                              <form id="delete-form-{{ $customer->id }}"
                                 action="{{ route('customers.destroy', $customer) }}" method="POST" class="hidden">
                                 @csrf
                                 @method('DELETE')
                              </form>
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                           {{ __('app.no_customer_data') }}
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>

         <!-- Pagination -->
         @if ($customers->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
               {{ $customers->links() }}
            </div>
         @endif
      </div>

      <!-- Import Modal -->
      <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
         <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
               <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                  <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
               </div>

               <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

               <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                  <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
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
                                    Import Customers
                                 </h3>
                                 <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                       Upload your XLSX or CSV file to import customers in bulk.
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
   </div>

   <script>
      function confirmDelete(id) {
         if (confirm('{{ __('app.confirm_delete_customer') }}')) {
            document.getElementById('delete-form-' + id).submit();
         }
      }
   </script>
@endsection
