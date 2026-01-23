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
                <span class="font-medium">{{ __('Dashboard') }}</span>
            </a>

            {{-- Master Data Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Master Data') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('warehouses.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('warehouses.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🏭</span><span>{{ __('Warehouses') }}</span>
                    </a>
                    <a href="{{ route('categories.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('categories.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📂</span><span>{{ __('Categories') }}</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('suppliers.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🤝</span><span>{{ __('Suppliers') }}</span>
                    </a>
                    <a href="{{ route('products.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('products.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📦</span><span>{{ __('Products') }}</span>
                    </a>
                </div>
            </div>

            {{-- Purchasing Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Purchasing') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('purchase-orders.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('purchase-orders.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📋</span><span>{{ __('Purchase Orders') }}</span>
                    </a>
                    <a href="{{ route('stock-ins.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('stock-ins.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📥</span><span>{{ __('Stock In') }}</span>
                    </a>
                </div>
            </div>

            {{-- Sales Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Sales') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('customers.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('customers.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>👥</span><span>{{ __('Customers') }}</span>
                    </a>
                    <a href="{{ route('sales-orders.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('sales-orders.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🛒</span><span>{{ __('Sales Orders') }}</span>
                    </a>
                    <a href="{{ route('invoices.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('invoices.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🧾</span><span>{{ __('Invoices & Payments') }}</span>
                    </a>
                    <a href="{{ route('stock-outs.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('stock-outs.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📤</span><span>{{ __('Stock Out') }}</span>
                    </a>
                </div>
            </div>

            {{-- Warehouse Operations Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Warehouse') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('batches.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('batches.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📦</span><span>{{ __('Batch Inventory') }}</span>
                    </a>
                    <a href="{{ route('transfers.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('transfers.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>🔄</span><span>{{ __('Warehouse Transfers') }}</span>
                    </a>
                    @if (auth()->user()->isAdmin() || auth()->user()->isManager())
                        <a href="{{ route('stock-opnames.index') }}" @click="sidebarOpen = false"
                           class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('stock-opnames.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                            <span>📋</span><span>{{ __('Stock Opname') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Operations Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Operations') }}</p>
                <div class="mt-2 space-y-1">
                    @can('transaction.approve')
                        <a href="{{ route('approvals.index') }}" @click="sidebarOpen = false"
                           class="flex items-center justify-between gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('approvals.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                            <span class="flex items-center gap-3"><span>📋</span><span>{{ __('Approval Center') }}</span></span>
                            <span id="approval-badge" class="hidden px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold"></span>
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Reports Section --}}
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Reports') }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('reports.index') }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('reports.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                        <span>📊</span><span>{{ __('All Reports') }}</span>
                    </a>
                </div>
            </div>

            {{-- Administration (Permission-based) --}}
            @if (auth()->user()->hasPermissionTo('user.manage') || auth()->user()->hasPermissionTo('role.manage') || auth()->user()->isAdmin())
                <div class="pt-4 pb-8">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Administration') }}</p>
                    <div class="mt-2 space-y-1">
                        @can('user.manage')
                            <a href="{{ route('users.index') }}" @click="sidebarOpen = false"
                               class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                                <span>👤</span><span>{{ __('User Management') }}</span>
                            </a>
                        @endcan
                        @can('role.manage')
                            <a href="{{ route('roles.index') }}" @click="sidebarOpen = false"
                               class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('roles.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                                <span>🔐</span><span>{{ __('Role Management') }}</span>
                            </a>
                        @endcan
                        @if (auth()->user()->hasPermissionTo('user.manage') || auth()->user()->isAdmin())
                            <a href="{{ route('company.settings') }}" @click="sidebarOpen = false"
                               class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('company.settings*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                                <span>🏢</span><span>{{ __('Company Settings') }}</span>
                            </a>
                            <a href="{{ route('currencies.index') }}" @click="sidebarOpen = false"
                               class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('currencies.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                                <span>💱</span><span>{{ __('Currency Settings') }}</span>
                            </a>
                            <a href="{{ route('settings.index') }}" @click="sidebarOpen = false"
                               class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('settings.*') ? 'bg-emerald-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                                <span>⚙️</span><span>{{ __('Settings') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Super-Admin Platform --}}
            @if (auth()->user()->is_super_admin)
                <div class="pt-4 pb-8 border-t border-purple-100">
                    <p class="px-4 text-xs font-semibold text-purple-500 uppercase tracking-wider">{{ __('Platform Admin') }}</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('platform.companies.index') }}" @click="sidebarOpen = false"
                           class="flex items-center gap-3 py-3 px-4 rounded-lg transition touch-manipulation {{ request()->routeIs('platform.*') ? 'bg-purple-600 text-white' : 'hover:bg-purple-50 text-purple-700' }}">
                            <span>🏭</span><span>{{ __('All Companies') }}</span>
                        </a>
                    </div>
                </div>
            @endif
        </nav>
    </aside>
</div>

<script>
// Load approval badge count
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/approvals/counts')
        .then(r => r.json())
        .then(data => {
            if (data.total > 0) {
                const badge = document.getElementById('approval-badge');
                if (badge) {
                    badge.textContent = data.total;
                    badge.classList.remove('hidden');
                }
            }
        })
        .catch(() => {});
});
</script>
