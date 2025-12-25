<x-admin-layout>
    <x-slot name="header">Status History: {{ $student->full_name }}</x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => $student->full_name, 'url' => route('admin.students.show', $student)],
            ['label' => 'Status History', 'url' => '#']
        ]" />
        
        <div class="card overflow-hidden">
            <div class="p-6">
                    @if($history->isEmpty())
                        <p class="text-gray-500 italic">No status history found for this student hideously.</p>
                    @else
                        <div class="relative">
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                            <ul class="space-y-6">
                                @foreach($history as $record)
                                    <li class="relative pl-10">
                                        <div class="absolute left-2 top-1.5 w-4 h-4 rounded-full border-2 border-white shadow 
                                            {{ $record->new_status === 'active' ? 'bg-green-500' : ($record->new_status === 'withdrawn' || $record->new_status === 'dropped_out' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-4 border">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <span class="text-sm text-gray-500">{{ $record->old_status ? ucfirst(str_replace('_', ' ', $record->old_status)) : 'Initial' }}</span>
                                                    <span class="mx-2">→</span>
                                                    <span class="font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $record->new_status)) }}</span>
                                                </div>
                                                <span class="text-xs text-gray-400">{{ $record->created_at->format('M j, Y H:i') }}</span>
                                            </div>
                                            @if($record->reason)
                                                <p class="text-sm text-gray-600 mb-1"><strong>Reason:</strong> {{ \App\Models\StudentStatusHistory::withdrawalReasons()[$record->reason] ?? $record->reason }}</p>
                                            @endif
                                            @if($record->notes)
                                                <p class="text-sm text-gray-500 italic">{{ $record->notes }}</p>
                                            @endif
                                            <p class="text-xs text-gray-400 mt-2">Changed by: {{ $record->changer->name ?? 'System' }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        </div>
</x-admin-layout>
