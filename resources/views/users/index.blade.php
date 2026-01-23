@extends('layouts.app')

@section('title', __('app.user_management'))

@section('content')
   <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
         <h1 class="text-2xl font-bold text-gray-800">{{ __('app.user_management') }}</h1>
         <a href="{{ route('users.create') }}"
            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition">
            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
               </path>
            </svg>
            {{ __('app.add_user') }}
         </a>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.search') }}</label>
               <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search_placeholder') ?? 'Name or email...' }}"
                  class="w-full border-gray-300 rounded-lg">
            </div>

            <div>
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.role') }}</label>
               <select name="role" class="w-full border-gray-300 rounded-lg">
                  <option value="">{{ __('app.all_roles') ?? 'All Roles' }}</option>
                  <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __('app.admin') }}</option>
                  <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>{{ __('app.manager') ?? 'Manager' }}</option>
                  <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>{{ __('app.staff') }}</option>
               </select>
            </div>

            <div>
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.status') }}</label>
               <select name="status" class="w-full border-gray-300 rounded-lg">
                  <option value="">{{ __('app.all_statuses') }}</option>
                  <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                  <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('app.inactive') }}</option>
               </select>
            </div>

            <div class="flex items-end gap-2">
               <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition">
                  {{ __('app.filter') }}
               </button>
               <a href="{{ route('users.index') }}"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                  {{ __('app.reset') }}
               </a>
            </div>
         </form>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.user') }}
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.email') }}
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.role') }}
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.status') }}
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.joined_date') }}
                     </th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.actions') }}</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  @forelse($users as $user)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                           <div class="flex items-center">
                              <div class="h-10 w-10 flex-shrink-0">
                                 <div
                                    class="h-10 w-10 rounded-full bg-primary text-white flex items-center justify-center font-semibold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                 </div>
                              </div>
                              <div class="ml-4">
                                 <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                              </div>
                           </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                           {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                           @if ($user->role === 'admin')
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                 {{ __('app.admin') }}
                              </span>
                           @elseif($user->role === 'manager')
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                 {{ __('app.manager') ?? 'Manager' }}
                              </span>
                           @else
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                 {{ __('app.staff') }}
                              </span>
                           @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                           @if ($user->is_active)
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                 {{ __('app.active') }}
                              </span>
                           @else
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                 {{ __('app.inactive') }}
                              </span>
                           @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                           {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                           <a href="{{ route('users.edit', $user->id) }}" class="text-primary hover:text-blue-900 mr-3">
                              {{ __('app.edit') }}
                           </a>
                           @if ($user->id !== auth()->id())
                              <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block"
                                 onsubmit="return confirm('{{ __('app.confirm_delete_user') ?? 'Are you sure?' }}')">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit" class="text-danger hover:text-red-900">
                                    {{ __('app.delete') }}
                                 </button>
                              </form>
                           @endif
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="6" class="px-6 py-12">
                           <div class="text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">No users</h3>
                              <p class="mt-1 text-sm text-gray-500">Get started by creating a new user account.</p>
                              <div class="mt-6">
                                 <a href="{{ route('users.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                       stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add User
                                 </a>
                              </div>
                           </div>
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>

         <!-- Pagination -->
         <div class="px-6 py-4 bg-gray-50">
            {{ $users->links() }}
         </div>
      </div>
   </div>
@endsection
