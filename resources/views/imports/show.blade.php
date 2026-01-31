@extends('layouts.app')

@section('title', 'Import Progress')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Import Status</h2>
            <span class="px-3 py-1 rounded-full text-sm font-semibold 
                {{ $job->status === 'completed' ? 'bg-green-100 text-green-800' : 
                   ($job->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                {{ ucfirst($job->status) }}
            </span>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-blue-700">Progress</span>
                <span class="text-sm font-medium text-blue-700" id="progress-text">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: 0%" id="progress-bar"></div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-6 mb-8 text-center">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Total Rows</p>
                <p class="text-2xl font-bold text-gray-800" id="total-rows">{{ number_format($job->total_rows) }}</p>
            </div>
            <div class="p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-green-600">Processed</p>
                <p class="text-2xl font-bold text-green-700" id="processed-rows">{{ number_format($job->processed_rows) }}</p>
            </div>
            <div class="p-4 bg-red-50 rounded-lg">
                <p class="text-sm text-red-600">Failed</p>
                <p class="text-2xl font-bold text-red-700" id="failed-rows">{{ number_format($job->failed_rows) }}</p>
            </div>
        </div>

        <!-- Completion Message -->
        <div id="completion-message" class="hidden text-center py-8 bg-green-50 rounded-lg border border-green-200 mb-6">
            <p class="text-green-800 font-bold text-lg mb-2">Import Completed Successfully!</p>
            <a href="{{ route('imports.index') }}" class="text-green-600 underline">Return to Import List</a>
        </div>

        <!-- Error Log -->
        <div class="border-t pt-6">
            <h3 class="font-bold text-gray-800 mb-4">Error Log</h3>
            <div class="bg-gray-900 text-gray-100 p-4 rounded-lg font-mono text-xs h-64 overflow-y-auto" id="error-log">
                @if($job->error_log)
                    @foreach($job->error_log as $log)
                        <div class="mb-1 text-red-300">
                            [{{ $log['time'] ?? 'N/A' }}] {{ is_string($log) ? $log : ($log['error'] ?? json_encode($log)) }}
                        </div>
                    @endforeach
                @else
                    <div class="text-gray-500 italic">No errors logged so far.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jobId = {{ $job->id }};
        const status = '{{ $job->status }}';

        if (status === 'processing' || status === 'pending') {
            startPolling(jobId);
        } else {
            updateUI(@json([
                'progress' => ($job->total_rows > 0) ? round(($job->processed_rows / $job->total_rows) * 100) : 0,
                'status' => $job->status
            ]));
        }
    });

    function startPolling(jobId) {
        const interval = setInterval(() => {
            fetch(`/imports/${jobId}/progress`)
                .then(response => response.json())
                .then(data => {
                    updateUI(data);

                    if (data.status === 'completed' || data.status === 'failed') {
                        clearInterval(interval);
                        if (data.status === 'completed') {
                            document.getElementById('completion-message').classList.remove('hidden');
                        }
                    }
                });
        }, 2000);
    }

    function updateUI(data) {
        document.getElementById('progress-bar').style.width = data.progress + '%';
        document.getElementById('progress-text').textContent = data.progress + '%';
        
        if (data.processed) document.getElementById('processed-rows').textContent = data.processed;
        if (data.failed) document.getElementById('failed-rows').textContent = data.failed;
    }
</script>
@endsection
