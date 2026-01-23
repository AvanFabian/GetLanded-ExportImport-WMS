<x-app-layout>
   <div class="p-4 md:p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
         <h1 class="text-2xl font-bold text-gray-800">{{ __('app.edit_user') }}</h1>
         <a href="{{ route('users.index') }}"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
            {{ __('app.back_to_users') }}
         </a>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-lg shadow-md p-6">
         <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <!-- Name -->
               <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.full_name') }} *</label>
                  <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                     class="w-full border-gray-300 rounded-lg @error('name') border-red-500 @enderror">
                  @error('name')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <!-- Email -->
               <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.email') }} *</label>
                  <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                     class="w-full border-gray-300 rounded-lg @error('email') border-red-500 @enderror">
                  @error('email')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <!-- Password -->
               <div>
                  <label for="password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.new_password') }}</label>
                  <input type="password" name="password" id="password"
                     class="w-full border-gray-300 rounded-lg @error('password') border-red-500 @enderror">
                  @error('password')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
                  <p class="text-sm text-gray-500 mt-1">{{ __('app.leave_blank_password') ?? 'Leave blank to keep current password' }}</p>
               </div>

               <!-- Password Confirmation -->
               <div>
                  <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.confirm_new_password') }}</label>
                  <input type="password" name="password_confirmation" id="password_confirmation"
                     class="w-full border-gray-300 rounded-lg">
               </div>

               <!-- Role -->
               <div>
                  <label for="role" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.role') }} *</label>
                  <select name="role" id="role" required
                     class="w-full border-gray-300 rounded-lg @error('role') border-red-500 @enderror">
                     <option value="">{{ __('app.select_option', ['item' => __('app.role')]) }}</option>
                     <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>{{ __('app.admin') }}</option>
                     <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>{{ __('app.manager') ?? 'Manager' }}
                     </option>
                     <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>{{ __('app.staff') }}</option>
                  </select>
                  @error('role')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <!-- Is Active -->
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.status') }}</label>
                  <div class="flex items-center">
                     <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-primary focus:ring-primary">
                     <label for="is_active" class="ml-2 text-sm text-gray-700">{{ __('app.active_status_label') }}</label>
                  </div>
               </div>
            </div>

            <!-- User Info -->
            <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
               <h4 class="text-sm font-semibold text-gray-800 mb-2">{{ __('app.user_information') ?? 'User Information' }}:</h4>
               <div class="text-sm text-gray-700 space-y-1">
                  <p><strong>{{ __('app.created_at') }}:</strong> {{ $user->created_at->format('d M Y H:i') }}</p>
                  <p><strong>{{ __('app.updated_at') ?? 'Last Updated' }}:</strong> {{ $user->updated_at->format('d M Y H:i') }}</p>
                  @if ($user->id === auth()->id())
                     <p class="text-warning"><strong>{{ __('app.note') }}:</strong> {{ __('app.editing_own_account') ?? 'You are editing your own account' }}</p>
                  @endif
               </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2 mt-6">
               <a href="{{ route('users.index') }}"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                  {{ __('app.cancel') }}
               </a>
               <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition">
                  {{ __('app.update_user') }}
               </button>
            </div>
         </form>
      </div>
   </div>
</x-app-layout>
