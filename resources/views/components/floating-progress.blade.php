{{-- Floating Progress Bar — Global Background Job Monitor --}}
{{-- Injected into layouts/app.blade.php for all authenticated users --}}
<div id="floating-progress-container"
     x-data="floatingProgress()"
     x-init="startPolling()"
     x-show="jobs.length > 0 || completedJobs.length > 0"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-6 right-6 z-[60] w-80 max-h-[calc(100vh-120px)] flex flex-col gap-2"
     style="display: none;">

    {{-- Minimized Pill Button --}}
    <template x-if="minimized && (jobs.length > 0 || completedJobs.length > 0)">
        <button @click="minimized = false"
                class="self-end flex items-center gap-2 px-4 py-2.5 bg-white/95 backdrop-blur-lg border border-gray-200 rounded-full shadow-xl hover:shadow-2xl transition-all group">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
            </span>
            <span class="text-sm font-semibold text-gray-700 group-hover:text-gray-900" x-text="jobs.length + ' active'"></span>
            <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
    </template>

    {{-- Expanded Panel --}}
    <template x-if="!minimized">
        <div class="bg-white/95 backdrop-blur-xl border border-gray-200/80 rounded-2xl shadow-2xl overflow-hidden">
            {{-- Panel Header --}}
            <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75" x-show="jobs.length > 0"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                    </span>
                    <h3 class="text-sm font-semibold tracking-wide">Background Tasks</h3>
                </div>
                <button @click="minimized = true" class="p-1 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            {{-- Active Jobs --}}
            <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                <template x-for="job in jobs" :key="job.id">
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-base" x-text="job.icon"></span>
                                <span class="text-sm font-medium text-gray-800 capitalize" x-text="job.type + ' Import'"></span>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                  :class="job.status === 'processing' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'"
                                  x-text="job.status === 'processing' ? 'Processing' : 'Queued'">
                            </span>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="w-full bg-gray-100 rounded-full h-2 mb-1.5 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-700 ease-out"
                                 :class="job.status === 'processing' ? 'bg-gradient-to-r from-blue-500 to-emerald-500' : 'bg-amber-400'"
                                 :style="'width: ' + job.progress + '%'">
                            </div>
                        </div>

                        {{-- Stats Row --}}
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span x-text="formatNumber(job.processed_rows) + ' / ' + formatNumber(job.total_rows) + ' rows'"></span>
                            <span class="font-semibold" :class="job.progress >= 100 ? 'text-emerald-600' : 'text-gray-700'" x-text="job.progress + '%'"></span>
                        </div>

                        {{-- Failed Rows Warning --}}
                        <template x-if="job.failed_rows > 0">
                            <div class="mt-1 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <span x-text="job.failed_rows + ' rows failed'"></span>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Completed Jobs (brief flash) --}}
                <template x-for="job in completedJobs" :key="'done-' + job.id">
                    <div class="px-4 py-3 bg-emerald-50/80">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-emerald-700 capitalize" x-text="job.type + ' import completed!'"></span>
                        </div>
                        <p class="text-xs text-emerald-600 mt-0.5 ml-7" x-text="formatNumber(job.processed_rows) + ' rows processed'"></p>
                    </div>
                </template>

                {{-- Empty State (shouldn't show, but safety net) --}}
                <template x-if="jobs.length === 0 && completedJobs.length === 0">
                    <div class="px-4 py-6 text-center text-sm text-gray-400">
                        No active tasks
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

<script>
function floatingProgress() {
    return {
        jobs: [],
        completedJobs: [],
        minimized: false,
        previousJobIds: new Set(),
        pollInterval: null,

        startPolling() {
            this.fetchJobs();
            this.pollInterval = setInterval(() => this.fetchJobs(), 3000);
        },

        async fetchJobs() {
            try {
                const response = await fetch('/api/active-jobs', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                if (!response.ok) return;

                const data = await response.json();

                // Detect completed jobs (were active before, now gone)
                const currentIds = new Set(data.map(j => j.id));
                this.previousJobIds.forEach(async id => {
                    if (!currentIds.has(id)) {
                        // Job was active, now gone. Fetch final stats so we don't say "0 rows processed".
                        try {
                            const progRes = await fetch(`/imports/${id}/progress`, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            if (progRes.ok) {
                                const progData = await progRes.json();
                                const prev = this.jobs.find(j => j.id === id) || { id: id, type: 'products' }; // Fallback type
                                
                                this.completedJobs.push({
                                    ...prev, 
                                    status: progData.status || 'completed', 
                                    progress: 100,
                                    processed_rows: progData.processed || 0,
                                    total_rows: progData.total || 0,
                                    failed_rows: progData.failed || 0
                                });
                                
                                // Auto-remove after 5 seconds
                                setTimeout(() => {
                                    this.completedJobs = this.completedJobs.filter(j => j.id !== id);
                                }, 5000);
                            }
                        } catch (e) {
                            // Silently ignore
                        }
                    }
                });

                // Map and enrich jobs
                this.jobs = data.map(job => ({
                    ...job,
                    progress: job.total_rows > 0 ? Math.round((job.processed_rows / job.total_rows) * 100) : 0,
                    icon: this.getIcon(job.type),
                }));

                this.previousJobIds = currentIds;
            } catch (e) {
                // Silently ignore — don't crash the UI for a monitor widget
            }
        },

        getIcon(type) {
            const icons = {
                'products': '📦',
                'customers': '👥',
                'suppliers': '🤝',
                'stock': '📥',
            };
            return icons[type] || '📋';
        },

        formatNumber(num) {
            if (!num) return '0';
            return new Intl.NumberFormat().format(num);
        },

        destroy() {
            if (this.pollInterval) clearInterval(this.pollInterval);
        }
    };
}
</script>
