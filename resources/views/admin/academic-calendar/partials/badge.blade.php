@php
    $palette = [
        'blue' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'purple' => 'bg-purple-50 text-purple-700 ring-purple-200',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'red' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'teal' => 'bg-teal-50 text-teal-700 ring-teal-200',
        'gray' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
    $cfg = config("academic_calendar.categories.$category") ?? config('academic_calendar.categories.other');
@endphp
<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md ring-1 ring-inset text-[10px] font-black uppercase tracking-wider whitespace-nowrap {{ $palette[$cfg['color']] ?? $palette['gray'] }}">{{ $cfg['label'] }}</span>
