<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Audit Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <x-breadcrumb :items="[
            ['label' => 'System Audit Logs', 'url' => '#']
        ]" />

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">System Audit Logs</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metadata</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($logs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->user->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $log->event === 'created' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $log->event === 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $log->event === 'deleted' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $log->event }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($log->event === 'updated')
                                            <div class="space-y-1">
                                                @foreach($log->new_values as $key => $value)
                                                    @if($key !== 'updated_at')
                                                        <div>
                                                            <span class="font-medium">{{ $key }}:</span>
                                                            <span class="text-red-600 line-through">{{ $log->old_values[$key] ?? 'null' }}</span>
                                                            <span class="text-green-600">→ {{ $value }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @elseif($log->event === 'created')
                                            <span class="text-xs italic text-gray-400">Initial data populated</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="text-xs">IP: {{ $log->ip_address }}</div>
                                        <div class="text-xs truncate max-w-xs" title="{{ $log->user_agent }}">{{ $log->user_agent }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
