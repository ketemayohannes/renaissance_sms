<x-admin-layout>
    <x-slot name="header">Academic Calendar · Change History</x-slot>

    <div class="space-y-6">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Academic Calendar', 'url' => route('admin.academic-calendar.index')],
                ['label' => 'Change History', 'url' => '#'],
            ]" />
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mt-2">Change History</h1>
            <p class="text-sm text-slate-500">Every change to calendar events, weeks, school-day overrides and term dates, with the previous and new values.</p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-3xl border border-white shadow-xl shadow-slate-200/50 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50">
                        <th class="px-6 py-3">When</th>
                        <th class="px-6 py-3">Who</th>
                        <th class="px-6 py-3">Record</th>
                        <th class="px-6 py-3">Change</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                        <tr class="align-top">
                            <td class="px-6 py-3 whitespace-nowrap text-slate-600">{{ $log->created_at?->timezone('Africa/Addis_Ababa')->format('M j, Y H:i') }}</td>
                            <td class="px-6 py-3 text-slate-700 font-semibold">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                <div class="text-[10px] font-black uppercase text-slate-400">{{ $log->event }}</div>
                            </td>
                            <td class="px-6 py-3 text-xs text-slate-600">
                                @php $values = $log->event === 'deleted' ? ($log->old_values ?? []) : ($log->new_values ?? []); @endphp
                                <ul class="space-y-0.5">
                                    @foreach($values as $field => $value)
                                        @continue(in_array($field, ['created_at', 'updated_at', 'updated_by', 'created_by', 'id', 'academic_year_id'], true))
                                        <li><span class="font-semibold">{{ str_replace('_', ' ', $field) }}:</span>
                                            @if($log->event === 'updated')
                                                <span class="line-through text-slate-400">{{ is_scalar($log->old_values[$field] ?? null) ? Str::limit((string) $log->old_values[$field], 80) : '—' }}</span> →
                                            @endif
                                            {{ is_scalar($value) ? Str::limit((string) $value, 80) : json_encode($value) }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 font-semibold">No calendar changes recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</x-admin-layout>
