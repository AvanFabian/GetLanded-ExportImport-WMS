@extends('layouts.app')

@section('title', 'Profit & Loss Report')

@section('content')
<div class="max-w-7xl mx-auto">
    
    <!-- Title & Date Filter -->
    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Profit & Loss</h1>
            <p class="text-gray-500 mt-1">Gross Profit Analysis based on True Landed Cost</p>
        </div>
        
        <form method="GET" class="flex gap-3 items-end bg-white p-3 rounded-lg shadow-sm">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }}" class="rounded border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}" class="rounded border-gray-300 text-sm">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-bold text-sm">
                Filter
            </button>
        </form>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Revenue -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($revenue, 2) }}</p>
            <p class="text-xs text-green-600 mt-1 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                Sales
            </p>
        </div>

        <!-- COGS -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Cost of Goods Sold (COGS)</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($cogs, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Based on Historical WAC</p>
        </div>

        <!-- Gross Profit -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Gross Profit</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($grossProfit, 2) }}</p>
        </div>

        <!-- Margin % -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Gross Margin</p>
            <p class="text-3xl font-bold text-purple-600 mt-1">{{ number_format($marginPercentage, 1) }}%</p>
            <p class="text-xs text-gray-500 mt-1">Profit / Revenue</p>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <h3 class="font-bold text-lg text-gray-800 mb-4">Gross Profit Trend</h3>
        <div class="h-80 w-full">
            <canvas id="profitChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('profitChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Gross Profit',
                    data: @json($chartProfit),
                    backgroundColor: 'rgba(34, 197, 94, 0.6)', // Green-500
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Profit Amount'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            }
        });
    </script>

</div>
@endsection
