@if($items->isEmpty())
    <div class="text-center text-gray-500 py-10">
        <svg class="h-12 w-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        <p class="mt-2">Trash is empty.</p>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $label => $key)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $label }}</th>
                    @endforeach
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($items as $item)
                    <tr>
                        @foreach($columns as $key)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ data_get($item, $key) }}
                            </td>
                        @endforeach
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->deleted_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <form method="POST" action="{{ route('trash.restore', [$type, $item->id]) }}" onsubmit="return confirm('Restore this item?');">
                                @csrf
                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 font-bold">Restore</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
