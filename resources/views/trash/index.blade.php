<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trash Bin') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'products' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Tabs Headers -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-t-lg mb-0 border-b border-gray-200">
                 <div class="flex">
                     <button @click="tab = 'products'" :class="tab === 'products' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">Products ({{ $products->count() }})</button>
                     <button @click="tab = 'orders'" :class="tab === 'orders' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">Orders ({{ $orders->count() }})</button>
                     <button @click="tab = 'batches'" :class="tab === 'batches' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">Batches ({{ $batches->count() }})</button>
                     <button @click="tab = 'customers'" :class="tab === 'customers' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">Customers ({{ $customers->count() }})</button>
                     <button @click="tab = 'suppliers'" :class="tab === 'suppliers' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">Suppliers ({{ $suppliers->count() }})</button>
                 </div>
            </div>

            <!-- Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-b-lg p-6 min-h-[400px]">
                
                <!-- Products -->
                <div x-show="tab === 'products'">
                    @include('trash.partials.table', ['items' => $products, 'type' => 'product', 'columns' => ['Name' => 'name', 'Code' => 'code']])
                </div>

                <!-- Orders -->
                <div x-show="tab === 'orders'" style="display: none;">
                    @include('trash.partials.table', ['items' => $orders, 'type' => 'order', 'columns' => ['SO Number' => 'so_number', 'Date' => 'order_date']])
                </div>

                <!-- Batches -->
                <div x-show="tab === 'batches'" style="display: none;">
                    @include('trash.partials.table', ['items' => $batches, 'type' => 'batch', 'columns' => ['Batch #' => 'batch_number', 'Product' => 'product.name']])
                </div>

                <!-- Customers -->
                <div x-show="tab === 'customers'" style="display: none;">
                    @include('trash.partials.table', ['items' => $customers, 'type' => 'customer', 'columns' => ['Name' => 'name', 'Email' => 'email']])
                </div>

                <!-- Suppliers -->
                <div x-show="tab === 'suppliers'" style="display: none;">
                    @include('trash.partials.table', ['items' => $suppliers, 'type' => 'supplier', 'columns' => ['Name' => 'name', 'Phone' => 'phone']])
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
