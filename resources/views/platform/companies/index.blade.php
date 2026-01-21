@extends('layouts.app')

@section('title', 'Platform - Companies')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-medium">SUPER-ADMIN</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Platform Companies</h1>
            <p class="text-gray-600 text-sm mt-1">Manage all tenants on the platform</p>
        </div>
        <a href="{{ route('platform.companies.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Company
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Companies Table --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Company</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Users</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($companies as $company)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            @if($company->logo_path)
                                <img src="{{ Storage::url($company->logo_path) }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($company->name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-800">{{ $company->name }}</p>
                                <p class="text-xs text-gray-500">{{ $company->email ?? '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 font-mono text-sm text-gray-600">{{ $company->code }}</td>
                    <td class="px-4 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-semibold text-sm">
                            {{ $company->users_count }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        @if($company->is_active)
                            <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Active</span>
                        @else
                            <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Suspended</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('platform.companies.show', $company) }}"
                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <form action="{{ route('platform.companies.toggle-active', $company) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="p-2 {{ $company->is_active ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition"
                                        title="{{ $company->is_active ? 'Suspend' : 'Activate' }}"
                                        onclick="return confirm('{{ $company->is_active ? 'Suspend this company? Users will be locked out.' : 'Activate this company?' }}')">
                                    @if($company->is_active)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-500">
                        No companies found. Create the first one!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $companies->links() }}
    </div>
</div>
@endsection
