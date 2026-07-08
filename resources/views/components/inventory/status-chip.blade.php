@props(['status'])
@php
    $map = [
        'pending' => ['Pending', 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-100/50 dark:border-amber-900/30'],
        'pending_gm' => ['Awaiting GM', 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-100/50 dark:border-sky-900/30'],
        'approved' => ['Approved', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30'],
        'fulfilled' => ['Fulfilled', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30'],
        'rejected' => ['Rejected', 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-100/50 dark:border-rose-900/30'],
        'declined' => ['Declined', 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-100/50 dark:border-rose-900/30'],
        'principal_declined' => ['Declined (Principal)', 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-100/50 dark:border-rose-900/30'],
        'cancelled' => ['Cancelled', 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border-slate-200/50 dark:border-slate-700/30'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-500 border-slate-200/50'];
@endphp
<span class="inline-flex px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $classes }}">{{ $label }}</span>
