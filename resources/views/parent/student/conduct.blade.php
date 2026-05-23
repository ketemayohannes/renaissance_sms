<x-parent-layout header="{{ $student->full_name }}'s Conduct & Behavior Report">
    <div class="space-y-6">
        <!-- Explanatory note -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="font-heading font-bold text-slate-800 dark:text-slate-100 mb-2">Behavior Monitoring</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Here you can view formal incident logs and conduct reports filed by school staff. We encourage positive reinforcement and collaboration between parents and teachers to resolve disciplinary issues.
            </p>
        </div>

        <!-- Conduct Reports list -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading mb-6">Disciplinary Incident Logs</h3>
            
            @if($student->disciplinaryRecords->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h4 class="text-base font-semibold text-slate-700 dark:text-slate-300">Excellent Conduct Record!</h4>
                    <p class="text-slate-500 text-sm mt-1">There are no disciplinary or conduct incidents logged for this student.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($student->disciplinaryRecords as $record)
                        <div class="p-6 bg-slate-50 dark:bg-slate-850/40 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/40 transition-colors border border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-start justify-between gap-6">
                            <div class="space-y-3 flex-1">
                                <!-- Severity & Date Header -->
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold font-mono text-slate-400">{{ $record->incident_date->format('M d, Y') }}</span>
                                    
                                    @php
                                        $severityColors = [
                                            'minor' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-450 border border-blue-100 dark:border-blue-900',
                                            'moderate' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-450 border border-amber-100 dark:border-amber-900',
                                            'major' => 'bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-450 border border-orange-100 dark:border-orange-900',
                                            'critical' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-450 border border-rose-100 dark:border-rose-900',
                                        ];
                                        $severity = strtolower($record->severity);
                                        $sevClass = $severityColors[$severity] ?? 'bg-slate-50 text-slate-600 border border-slate-100';
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $sevClass }}">
                                        {{ $record->severity }}
                                    </span>

                                    @if($record->status === 'resolved')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-450">
                                            Resolved
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-450">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Incident info -->
                                <div>
                                    <h4 class="font-bold text-base text-slate-800 dark:text-slate-100 leading-snug">{{ $record->incident_type }}</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">{{ $record->description }}</p>
                                </div>

                                <!-- Resolution notes -->
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

                            <!-- Actions Taken -->
                            <div class="md:text-right flex-shrink-0 min-w-[150px]">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Action Imposed</span>
                                <span class="text-base font-bold text-slate-850 dark:text-slate-200 mt-1 block">
                                    {{ $record->action_taken ?: 'Under Investigation' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-parent-layout>