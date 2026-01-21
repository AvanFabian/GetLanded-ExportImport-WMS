@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('roles.index') }}" class="text-emerald-600 hover:text-emerald-700 text-sm flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Roles
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Edit Role: {{ $role->display_name }}</h1>
        <p class="text-gray-600 text-sm mt-1">Modify permissions for this custom role</p>
    </div>

    {{-- Alerts --}}
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('roles.update', $role) }}" method="POST" id="roleForm">
        @csrf
        @method('PUT')

        {{-- Basic Info Card --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Role Information</h2>
            
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role Code</label>
                    <input type="text" value="{{ $role->name }}" disabled
                           class="w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-600 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">Role code cannot be changed</p>
                </div>

                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Display Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="display_name" id="display_name" 
                           value="{{ old('display_name', $role->display_name) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('display_name') border-red-500 @enderror"
                           required>
                    @error('display_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="2"
                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $role->description) }}</textarea>
            </div>
        </div>

        {{-- Permissions Matrix --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Permissions</h2>
                    <p class="text-sm text-gray-500">Select what this role can do</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="selectAll()" 
                            class="px-3 py-1.5 text-sm bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition">
                        Select All
                    </button>
                    <button type="button" onclick="deselectAll()"
                            class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        Clear All
                    </button>
                </div>
            </div>

            @error('permissions')
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    {{ $message }}
                </div>
            @enderror

            {{-- Permission Categories --}}
            <div class="space-y-4">
                @foreach($permissions as $group => $data)
                <div class="border rounded-xl overflow-hidden hover:border-emerald-300 transition">
                    <div class="bg-gray-50 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $data['icon'] }}</span>
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $data['label'] }}</h3>
                                <p class="text-xs text-gray-500">{{ $data['description'] }}</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer touch-manipulation">
                            <input type="checkbox" class="group-toggle rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                   data-group="{{ $group }}"
                                   onchange="toggleGroup('{{ $group }}', this.checked)">
                            <span class="text-sm text-gray-600">Select All</span>
                        </label>
                    </div>
                    <div class="p-4 grid gap-3 sm:grid-cols-2">
                        @foreach($data['permissions'] as $permission)
                        <label class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-emerald-50 transition touch-manipulation">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                   class="permission-checkbox mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                   data-group="{{ $group }}"
                                   {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $permission->display_name }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $permission->name }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <a href="{{ route('roles.index') }}"
               class="px-6 py-3 text-center border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-semibold">
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
// Initialize group toggle states on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.group-toggle').forEach(toggle => {
        const group = toggle.dataset.group;
        const groupCheckboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
        const allChecked = Array.from(groupCheckboxes).every(c => c.checked);
        toggle.checked = allChecked;
    });
});

function toggleGroup(group, checked) {
    document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`).forEach(cb => {
        cb.checked = checked;
    });
}

function selectAll() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
    document.querySelectorAll('.group-toggle').forEach(cb => cb.checked = true);
}

function deselectAll() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.group-toggle').forEach(cb => cb.checked = false);
}

// Update group toggle when individual permissions change
document.querySelectorAll('.permission-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        const group = this.dataset.group;
        const groupCheckboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
        const groupToggle = document.querySelector(`.group-toggle[data-group="${group}"]`);
        const allChecked = Array.from(groupCheckboxes).every(c => c.checked);
        if (groupToggle) groupToggle.checked = allChecked;
    });
});
</script>
@endsection
