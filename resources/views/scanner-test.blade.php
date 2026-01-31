@extends('layouts.app')

@section('title', 'Scanner Reliability Test')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8" x-data="scannerTest()">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Scanner & Focus Trap Test</h1>
            <p class="mt-1 text-sm text-gray-500">Verify hardware integration reliability.</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full" :class="focused ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></span>
                <span x-text="focused ? 'SCANNER READY' : 'FOCUS LOST'" class="font-mono font-bold text-sm"></span>
            </div>
        </div>
    </div>

    <!-- The Trap -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Physically Scan a Barcode Now
        </label>
        
        <!-- THE MAGIC INPUT -->
        <input type="text" 
               id="master_scanner" 
               data-scanner-input
               autocomplete="off"
               placeholder="Focus should always return here..."
               class="w-full text-2xl font-mono p-4 border-2 border-emerald-500 rounded-lg focus:ring-4 focus:ring-emerald-200 transition-all"
               @focus="focused = true"
               @blur="focused = false">
               
        <p class="mt-2 text-xs text-gray-500">
            * Try clicking the whitespace outside. The cursor should jump back immediately.
        </p>
    </div>

    <!-- Scan Log -->
    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 h-64 overflow-y-auto font-mono text-sm">
        <template x-for="(log, index) in logs" :key="index">
            <div class="flex items-center gap-3 py-1 border-b border-gray-100 last:border-0">
                <span class="text-gray-400" x-text="log.time"></span>
                <span class="font-bold text-emerald-600" x-text="'[' + log.code + ']'"></span>
                <span class="text-gray-600">Scanned Successfully</span>
            </div>
        </template>
        <div x-show="logs.length === 0" class="text-center text-gray-400 py-10">
            No scans detected yet.
        </div>
    </div>
</div>

<script>
    function scannerTest() {
        return {
            focused: false,
            logs: [],
            init() {
                // Listen for the custom event dispatched by scanner-focus.js
                document.getElementById('master_scanner').addEventListener('barcode-scanned', (e) => {
                    this.addLog(e.detail.code);
                    
                    // Simulate AJAX lookup
                    console.log('Lookup Code:', e.detail.code);
                });
            },
            addLog(code) {
                const now = new Date();
                this.logs.unshift({
                    time: now.toLocaleTimeString() + '.' + now.getMilliseconds(),
                    code: code
                });
            }
        }
    }
</script>
@endsection
