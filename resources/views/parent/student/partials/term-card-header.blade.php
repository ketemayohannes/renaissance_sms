{{--
    Term card header (name + Average/Rank/Conduct/Absence badges + collapse chevron), shared
    by the Report and Assessment tab loops in parent/student/grades.blade.php so the two tabs
    can never show different badge data for the same term.

    Expects (from the enclosing x-data="{ open: false }" card):
      $termName   — string
      $termRecord — StudentTermRecord|null
--}}
<button @click="open = !open" class="w-full flex flex-col xl:flex-row xl:items-center xl:justify-between px-6 py-5 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 text-left gap-4 select-none">
    <div class="flex items-center gap-3">
        <div class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></div>
        <span class="font-black text-slate-850 dark:text-slate-100 font-heading text-lg tracking-tight">{{ $termName }} Report</span>
    </div>

    <div class="flex flex-wrap items-center gap-3.5 xl:self-end">
        <!-- Average Badge -->
        @if($termRecord && $termRecord->average_score !== null)
            <div class="bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-300 border border-indigo-100/50 dark:border-indigo-900/30 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm text-xs">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span class="text-[9px] font-black uppercase tracking-wider hidden sm:inline opacity-70">Average</span>
                <span>{{ \App\Helpers\NumberFormatter::format($termRecord->average_score) }}%</span>
            </div>
        @endif

        <!-- Rank Badge -->
        @if($termRecord && $termRecord->rank !== null)
            <div class="bg-amber-50 dark:bg-amber-950/40 text-amber-650 dark:text-amber-300 border border-amber-100/50 dark:border-amber-900/30 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm text-xs">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.75M9 15.375V12M12 3v12.375m0-12.375a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"></path>
                </svg>
                <span class="text-[9px] font-black uppercase tracking-wider hidden sm:inline opacity-70">Rank</span>
                <span>#{{ $termRecord->rank }}<span class="text-[10px] font-bold opacity-60">/{{ $termRecord->rank_out_of }}</span></span>
            </div>
        @endif

        <!-- Conduct Badge -->
        @if($termRecord)
            <div class="bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-300 border border-emerald-100/50 dark:border-emerald-900/30 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm text-xs">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"></path>
                </svg>
                <span class="text-[9px] font-black uppercase tracking-wider hidden sm:inline opacity-70">Conduct</span>
                <span>{{ $termRecord->conduct_grade ?? 'A' }}</span>
            </div>
        @endif

        <!-- Absence Badge -->
        @if($termRecord)
            <div class="bg-rose-50 dark:bg-rose-950/40 text-rose-650 dark:text-rose-300 border border-rose-100/50 dark:border-rose-900/30 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm text-xs">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                </svg>
                <span class="text-[9px] font-black uppercase tracking-wider hidden sm:inline opacity-70">Absence</span>
                <span>
                    @if($termRecord->days_absent !== null)
                        {{ $termRecord->days_absent }} day{{ $termRecord->days_absent != 1 ? 's' : '' }}
                    @else
                        —
                    @endif
                </span>
            </div>
        @endif

        <div class="w-8 h-8 rounded-xl bg-slate-100/80 dark:bg-slate-800/85 flex items-center justify-center text-slate-500 dark:text-slate-400 shadow-sm border border-slate-200/50 dark:border-slate-700/50 hover:bg-indigo-50 dark:hover:bg-slate-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all">
            <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>
</button>
