{{-- Mobile-Optimized Sidebar --}}
{{-- Hidden on mobile by default, toggleable via hamburger menu --}}

<div x-data="{ sidebarOpen: false }" @sidebar-toggle.window="sidebarOpen = !sidebarOpen">
    {{-- Mobile Overlay --}}
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black bg-opacity-50 md:hidden"
         style="display: none;"></div>

    {{-- Sidebar Panel --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r shadow-xl transform transition-transform duration-200 ease-in-out md:relative md:translate-x-0 md:shadow-none md:z-auto overflow-y-auto">
        
        {{-- Mobile Header with Close --}}
        <div class="flex items-center justify-between p-4 border-b md:hidden bg-gradient-to-r from-emerald-700 to-emerald-800 text-white">
            <span class="font-bold text-lg">📦 AgroWMS</span>
            <button @click="sidebarOpen = false" class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition touch-manipulation">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="p-4 space-y-1">
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               @click="sidebarOpen = false"
               class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="font-medium">{{ __('app.dashboard') }}</span>
            </a>

            {{-- Master Data Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('app.master_data') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('warehouses.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('warehouses.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🏭</span><span>{{ __('app.warehouses') }}</span>
                    </a>
                    <a href="{{ route('categories.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('categories.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📂</span><span>{{ __('app.categories') }}</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('suppliers.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🤝</span><span>{{ __('app.suppliers') }}</span>
                    </a>
                    <a href="{{ route('products.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('products.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📦</span><span>{{ __('app.products') }}</span>
                    </a>
                </div>
            </div>

            {{-- Purchasing Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('app.purchasing') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('purchase-orders.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('purchase-orders.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📋</span><span>{{ __('app.purchase_orders') }}</span>
                    </a>
                    <a href="{{ route('stock-ins.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('stock-ins.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📥</span><span>{{ __('app.stock_in') }}</span>
                    </a>
                </div>
            </div>

            {{-- Sales Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('app.sales') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('customers.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('customers.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>👥</span><span>{{ __('app.customers') }}</span>
                    </a>
                    <a href="{{ route('sales-orders.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('sales-orders.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🛒</span><span>{{ __('app.sales_orders') }}</span>
                    </a>
                    <a href="{{ route('invoices.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('invoices.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🧾</span><span>{{ __('app.invoices_payments') }}</span>
                    </a>
                    <a href="{{ route('stock-outs.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('stock-outs.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📤</span><span>{{ __('app.stock_out') }}</span>
                    </a>
                </div>
            </div>

            {{-- Warehouse Operations Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('app.warehouse') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('batches.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('batches.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📦</span><span>Batch Inventory</span>
                    </a>
                    <a href="{{ route('transfers.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('transfers.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🔄</span><span>{{ __('app.warehouse_transfers') }}</span>
                    </a>
                    @if (auth()->user()->isAdmin() || auth()->user()->isManager())
                        <a href="{{ route('stock-opnames.index') }}" @click="sidebarOpen = false"
                           class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('stock-opnames.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                            <span>📋</span><span>{{ __('app.stock_opname') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Reports Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('app.reports') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('reports.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('reports.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📊</span><span>{{ __('app.all_reports') }}</span>
                    </a>
                </div>
            </div>

            {{-- Administration (Admin Only) --}}
            @if (auth()->user()->isAdmin())
                <div class="pt-4 pb-8">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('app.administration') }}</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('users.index') }}" @click="sidebarOpen = false"
                           class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                            <span>👤</span><span>{{ __('app.user_management') }}</span>
                        </a>
                        <a href="{{ route('currencies.index') }}" @click="sidebarOpen = false"
                           class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('currencies.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                            <span>💱</span><span>Currency Settings</span>
                        </a>
                        <a href="{{ route('settings.index') }}" @click="sidebarOpen = false"
                           class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('settings.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                            <span>⚙️</span><span>{{ __('app.settings') }}</span>
                        </a>
                    </div>
                </div>
            @endif
        </nav>
    </aside>
</div>
