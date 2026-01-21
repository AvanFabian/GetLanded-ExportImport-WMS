@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Role Management</h1>
            <p class="text-gray-600 text-sm mt-1">Define custom roles and assign permissions for your team</p>
        </div>
        <a href="{{ route('roles.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition touch-manipulation">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Custom Role
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Roles Table --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Members</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($roles as $role)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br 
                                    {{ $role->is_system ? 'from-blue-500 to-blue-600' : 'from-emerald-500 to-emerald-600' }}
                                    flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($role->display_name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $role->display_name }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $role->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($role->is_system)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    🔒 System
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    ✏️ Custom
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-semibold text-sm">
                                {{ $role->users_count }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="text-sm text-gray-600">{{ $role->permissions()->count() }} permissions</span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(!$role->is_system)
                                    <a href="{{ route('roles.edit', $role) }}"
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition touch-manipulation"
                                       title="Edit Role">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @if($role->users_count == 0)
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Delete this role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition touch-manipulation"
                                                    title="Delete Role">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">Protected</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-500">
                            <p class="text-lg mb-2">No custom roles yet</p>
                            <p class="text-sm">Create your first custom role to define specific permissions for your team.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Help Text --}}
    <div class="mt-6 p-4 bg-blue-50 border border-blue-100 rounded-lg">
        <div class="flex gap-3">
            <span class="text-blue-500 text-xl">💡</span>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">About Roles</p>
                <ul class="space-y-1 text-blue-700">
                    <li>• <strong>System roles</strong> (Admin, Manager, Staff, Viewer) are pre-defined and cannot be modified.</li>
                    <li>• <strong>Custom roles</strong> are specific to your company and can be tailored to your workflow.</li>
                    <li>• Users can have multiple roles; permissions from all roles are combined.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
