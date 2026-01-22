{{-- Welcome Tour Component --}}
{{-- Include in layout: @include('components.welcome-tour') --}}

<div x-data="welcomeTour()" 
     x-show="showTour" 
     x-cloak
     class="fixed inset-0 z-[100]"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60"></div>
    
    {{-- Tour Step 1: Welcome --}}
    <div x-show="currentStep === 1" 
         x-transition
         class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8 text-center relative">
            <div class="w-20 h-20 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-200">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Selamat Datang di AgroWMS! 🎉</h2>
            <p class="text-gray-600 mb-6">
                Sistem Manajemen Gudang yang akan membantu Anda mengelola inventaris dengan mudah dan akurat. 
                Mari kita lihat fitur-fitur penting dalam 30 detik.
            </p>
            <div class="flex items-center justify-center gap-3">
                <button @click="skipTour()" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">
                    Lewati Tour
                </button>
                <button @click="nextStep()" class="px-6 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">
                    Mulai Tour →
                </button>
            </div>
        </div>
    </div>

    {{-- Tour Step 2: Sidebar Navigation --}}
    <div x-show="currentStep === 2" x-transition>
        {{-- Highlight box for sidebar --}}
        <div class="fixed top-0 left-0 w-64 h-screen bg-white/10 border-4 border-emerald-400 rounded-r-2xl pointer-events-none"></div>
        
        {{-- Tooltip --}}
        <div class="fixed top-1/3 left-72 max-w-sm bg-white rounded-xl shadow-2xl p-6">
            <div class="absolute -left-3 top-6 w-0 h-0 border-t-8 border-t-transparent border-b-8 border-b-transparent border-r-8 border-r-white"></div>
            <div class="flex items-start gap-3 mb-4">
                <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">2</span>
                <div>
                    <h3 class="font-bold text-gray-900">Navigasi Sidebar</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Akses semua menu dari sini: Master Data, Transaksi, Laporan, dan Pengaturan. 
                        Menu akan menyesuaikan dengan role Anda.
                    </p>
                </div>
            </div>
            <div class="flex justify-between items-center">
                <button @click="prevStep()" class="text-gray-500 hover:text-gray-700 text-sm">← Kembali</button>
                <button @click="nextStep()" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                    Lanjut →
                </button>
            </div>
        </div>
    </div>

    {{-- Tour Step 3: Business Rules --}}
    <div x-show="currentStep === 3" x-transition>
        {{-- Tooltip --}}
        <div class="fixed top-1/4 left-1/2 -translate-x-1/2 max-w-sm bg-white rounded-xl shadow-2xl p-6">
            <div class="flex items-start gap-3 mb-4">
                <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">3</span>
                <div>
                    <h3 class="font-bold text-gray-900">Business Rules</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Di menu <strong>Settings → Business Rules</strong>, Anda bisa mengatur kebijakan perusahaan:
                    </p>
                    <ul class="text-sm text-gray-600 mt-2 space-y-1">
                        <li>• Approval workflow (self-approve atau tidak)</li>
                        <li>• Stock limit mode (block vs warning)</li>
                        <li>• Unit konversi otomatis</li>
                    </ul>
                </div>
            </div>
            <div class="flex justify-between items-center">
                <button @click="prevStep()" class="text-gray-500 hover:text-gray-700 text-sm">← Kembali</button>
                <button @click="nextStep()" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                    Lanjut →
                </button>
            </div>
        </div>
    </div>

    {{-- Tour Step 4: Global Search --}}
    <div x-show="currentStep === 4" x-transition>
        {{-- Highlight the search area --}}
        <div class="fixed top-3 right-48 w-80 h-12 border-4 border-emerald-400 rounded-xl pointer-events-none"></div>
        
        {{-- Tooltip --}}
        <div class="fixed top-20 right-24 max-w-sm bg-white rounded-xl shadow-2xl p-6">
            <div class="absolute -top-3 right-32 w-0 h-0 border-l-8 border-l-transparent border-r-8 border-r-transparent border-b-8 border-b-white"></div>
            <div class="flex items-start gap-3 mb-4">
                <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">4</span>
                <div>
                    <h3 class="font-bold text-gray-900">Pencarian Global</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Cari apapun dengan cepat! Ketik nama produk, nomor SO, batch number, 
                        atau nama customer. Tekan <kbd class="px-1 py-0.5 bg-gray-100 rounded text-xs">Ctrl+K</kbd> untuk shortcut.
                    </p>
                </div>
            </div>
            <div class="flex justify-between items-center">
                <button @click="prevStep()" class="text-gray-500 hover:text-gray-700 text-sm">← Kembali</button>
                <button @click="finishTour()" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                    Selesai ✓
                </button>
            </div>
        </div>
    </div>

    {{-- Progress Indicator --}}
    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-2">
        <template x-for="step in [1, 2, 3, 4]" :key="step">
            <div class="w-2 h-2 rounded-full transition-all"
                 :class="currentStep >= step ? 'bg-emerald-400 w-4' : 'bg-white/50'"></div>
        </template>
    </div>
</div>

<script>
function welcomeTour() {
    return {
        showTour: false,
        currentStep: 1,
        
        init() {
            // Check if user has completed the tour
            const tourCompleted = localStorage.getItem('agrowms_tour_completed');
            const isNewUser = localStorage.getItem('agrowms_is_new_user');
            
            // Show tour for new users or those who haven't completed it
            if (!tourCompleted) {
                // Delay to let the page load first
                setTimeout(() => {
                    this.showTour = true;
                }, 1000);
            }
        },
        
        nextStep() {
            if (this.currentStep < 4) {
                this.currentStep++;
            }
        },
        
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        
        skipTour() {
            this.showTour = false;
            localStorage.setItem('agrowms_tour_completed', 'skipped');
        },
        
        finishTour() {
            this.showTour = false;
            localStorage.setItem('agrowms_tour_completed', 'true');
            
            // Show thank you toast
            if (typeof Alpine !== 'undefined') {
                // Could dispatch a toast notification here
            }
        },
        
        // Allow reopening the tour
        restartTour() {
            this.currentStep = 1;
            this.showTour = true;
        }
    }
}
</script>
