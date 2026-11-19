@forelse($logs as $log)
    <div class="py-2 border-b border-slate-100 last:border-0 text-xs">
        <div class="flex justify-between gap-2">
            <span class="font-black uppercase text-slate-600">{{ $log->event }}</span>
            <span class="text-slate-400" title="{{ $log->created_at }}">{{ $log->created_at?->diffForHumans() }}</span>
        </div>
        <div class="text-slate-500">{{ $log->user?->name ?? 'System' }}</div>
        @if($log->event === 'updated' && is_array($log->new_values))
            <ul class="mt-1 space-y-0.5">
                @foreach($log->new_values as $field => $value)
                    @continue(in_array($field, ['updated_at', 'updated_by'], true))
                    <li class="text-slate-600"><span class="font-semibold">{{ str_replace('_', ' ', $field) }}:</span>
                        <span class="line-through text-slate-400">{{ is_scalar($log->old_values[$field] ?? null) ? Str::limit((string) $log->old_values[$field], 60) : '—' }}</span>
                        → {{ is_scalar($value) ? Str::limit((string) $value, 60) : json_encode($value) }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@empty
    <p class="text-xs text-slate-400">No changes recorded yet.</p>
@endforelse
