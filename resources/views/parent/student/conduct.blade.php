<x-parent-layout header="{{ $student->full_name }}'s Conduct & Behavior Report">
    <div class="space-y-6 max-w-6xl mx-auto">

        <!-- Premium Banner -->
        <div class="relative bg-gradient-to-r from-violet-600 via-indigo-600 to-indigo-700 rounded-3xl p-5 sm:p-6 lg:p-8 text-white overflow-hidden shadow-lg shadow-indigo-100 dark:shadow-none">
            <div class="absolute -right-10 -top-10 w-32 sm:w-40 h-32 sm:h-40 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -right-20 -bottom-20 w-48 sm:w-60 h-48 sm:h-60 bg-indigo-500/20 rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <h2 class="text-lg sm:text-2xl font-bold font-heading tracking-tight">Behavior Monitoring</h2>
                <p class="text-indigo-100 text-xs sm:text-sm mt-1">Formal incident logs, conduct reports, and disciplinary records</p>
            </div>
        </div>

        <!-- Info Note -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 sm:p-6 shadow-sm">
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Here you can view formal incident logs and conduct reports filed by school staff. We encourage positive reinforcement and collaboration between parents and teachers to resolve disciplinary issues.
            </p>
        </div>

        <!-- Conduct Reports list -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 sm:p-6 shadow-sm">
            <h3 class="font-bold text-base sm:text-lg text-slate-800 dark:text-slate-100 font-heading mb-4 sm:mb-6">Disciplinary Incident Logs</h3>
            
            @if($student->disciplinaryRecords->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h4 class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-300">Excellent Conduct Record!</h4>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">There are no disciplinary or conduct incidents logged for this student.</p>
                </div>
            @else
                <div class="space-y-4 sm:space-y-6">
                    @foreach($student->disciplinaryRecords as $record)
                        <div class="p-4 sm:p-6 bg-slate-50 dark:bg-slate-850/40 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/40 transition-colors border border-slate-100 dark:border-slate-800">
                            {{-- Header: Severity & Date Badges --}}
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-3">
                                <span class="text-[10px] sm:text-xs font-bold font-mono text-slate-400">{{ $record->incident_date->format('M d, Y') }}</span>
                                
                                @php
                                    $tierColors = [
                                        'minor'    => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-450 border border-blue-100 dark:border-blue-900',
                                        'moderate' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-450 border border-amber-100 dark:border-amber-900',
                                        'critical' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-450 border border-rose-100 dark:border-rose-900',
                                    ];
                                    $tierClass = $tierColors[$record->tier] ?? 'bg-slate-50 text-slate-600 border border-slate-100';
                                @endphp
                                <span class="px-1.5 sm:px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-bold uppercase tracking-wider {{ $tierClass }}">
                                    {{ ucfirst($record->tier) }}
                                </span>

                                @if($record->status === 'resolved')
                                    <span class="px-1.5 sm:px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-450">
                                        Resolved
                                    </span>
                                @else
                                    <span class="px-1.5 sm:px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-450">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Body: Incident Details & Action --}}
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-3 sm:gap-6">
                                <div class="space-y-2.5 sm:space-y-3 flex-1 min-w-0">
                                    <div>
                                        <h4 class="font-bold text-sm sm:text-base text-slate-800 dark:text-slate-100 leading-snug">{{ $record->infraction_name }}</h4>
                                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">{{ $record->description }}</p>
                                    </div>

                                    @if($record->resolution_notes)
                                        <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 text-xs">
                                            <span class="font-bold text-slate-700 dark:text-slate-350 block mb-1">Resolution Details</span>
                                            <p class="text-slate-550 dark:text-slate-400 italic">{{ $record->resolution_notes }}</p>
                                            @if($record->resolution_date)
                                                <span class="block text-slate-400 font-mono mt-1">Resolved on: {{ $record->resolution_date->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Action Taken --}}
                                <div class="md:text-right flex-shrink-0 pt-2 sm:pt-0 border-t md:border-t-0 border-slate-100 dark:border-slate-800 md:min-w-[150px]">
                                    <span class="block text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest">Action Imposed</span>
                                    <span class="text-sm sm:text-base font-bold text-slate-850 dark:text-slate-200 mt-0.5 sm:mt-1 block">
                                        {{ $record->action_taken ?: 'Under Investigation' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-parent-layout>