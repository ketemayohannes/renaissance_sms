{{--
    Term card header (name + Active badge + Average/Rank + collapse chevron), shared by the
    Report and Assessment tab loops in student/grades/index.blade.php so the two tabs can
    never show different badge data for the same term.

    Expects (from the enclosing x-data="{ expanded: ... }" card):
      $termName   — string
      $termRecord — StudentTermRecord|null
      $activeTerm — Term|null (from the controller)

    Average/Rank are gated behind "has this term ended" so a student can't see a live,
    in-progress ranking. Yearly is a virtual term (its StudentTermRecord->term_id is the
    string 'yearly', which never resolves to a real Term row via the relation), so it is
    always treated as ended once it has data — there's no "in progress" yearly result.
--}}
@php
    $termHasEnded = $termName === 'Yearly'
        || ($termRecord && $termRecord->term && $termRecord->term->end_date && now()->startOfDay()->greaterThanOrEqualTo($termRecord->term->end_date->startOfDay()));
@endphp
<div class="flex items-center justify-between cursor-pointer select-none pb-4 transition-all duration-300 group"
     :class="expanded ? 'border-b border-slate-100 dark:border-slate-800' : ''"
     @click="expanded = !expanded">
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">{{ $termName }}</h3>
                @if(isset($activeTerm) && $activeTerm && $activeTerm->name === $termName)
                    <span class="px-2 py-0.5 bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-500/30 rounded-md text-[8px] font-black uppercase tracking-widest">Active</span>
                @endif
            </div>
            <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Unified Grade Matrix</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        @if($termHasEnded && $termRecord && $termRecord->average_score !== null)
            <div class="bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/30 rounded-2xl px-4 py-2 text-center">
                <span class="block text-[8px] font-black text-indigo-400 dark:text-indigo-500 uppercase tracking-widest leading-none mb-1">Average</span>
                <span class="text-base font-black text-indigo-600 dark:text-indigo-300">{{ \App\Helpers\NumberFormatter::format($termRecord->average_score) }}%</span>
            </div>
        @endif
        @if($termHasEnded && $termRecord && $termRecord->rank !== null)
            <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/30 rounded-2xl px-4 py-2 text-center">
                <span class="block text-[8px] font-black text-amber-400 dark:text-amber-500 uppercase tracking-widest leading-none mb-1">Rank</span>
                <span class="text-base font-black text-amber-600 dark:text-amber-300">{{ $termRecord->rank }}<span class="text-[10px] font-bold opacity-60">/ {{ $termRecord->rank_out_of }}</span></span>
            </div>
        @endif

        <!-- Collapsible Chevron Icon -->
        <div class="w-10 h-10 rounded-xl bg-slate-100/50 dark:bg-slate-800/50 flex items-center justify-center text-slate-500 dark:text-slate-400 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950/50 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-all duration-300 shadow-sm border border-slate-200/50 dark:border-slate-700/50">
            <svg class="w-5 h-5 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
</div>
