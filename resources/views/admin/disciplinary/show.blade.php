<x-admin-layout>
    <x-slot name="header">Behavior Trace Details</x-slot>

    <div class="space-y-8 pb-32">
        <!-- Header & Action Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Behavior Management', 'url' => route('admin.disciplinary.index')],
                    ['label' => 'Incident Details', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    @php
                        $severityColors = [
                            'minor' => 'bg-emerald-500',
                            'moderate' => 'bg-amber-500',
                            'major' => 'bg-orange-500',
                            'critical' => 'bg-rose-600',
                        ];
                    @endphp
                    <span class="w-1.5 h-8 {{ $severityColors[$disciplinary->severity] ?? 'bg-slate-600' }} rounded-full"></span>
                    Trace № {{ $disciplinary->id }}
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Permanent disciplinary investigation archive</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.disciplinary.index') }}" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-3 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Behavior Log
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Data Grid -->
            <div class="lg:col-span-2 space-y-8">
                 <!-- Subject Intel -->
                <div class="bg-indigo-900 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden group">
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/5 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-1000"></div>
                    <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                        <div class="w-32 h-32 rounded-[2.5rem] bg-white/10 backdrop-blur-md flex items-center justify-center text-4xl font-black shadow-inner border border-white/10 shrink-0">
                            {{ substr($disciplinary->student->first_name, 0, 1) }}{{ substr($disciplinary->student->last_name, 0, 1) }}
                        </div>
                        <div class="text-center md:text-left">
                            <span class="bg-white/10 text-white/60 text-[10px] font-black px-4 py-1 rounded-full uppercase tracking-widest border border-white/5">Subject Identity</span>
                            <h2 class="text-3xl font-black mt-4 tracking-tight">{{ $disciplinary->student->full_name }}</h2>
                            <p class="text-indigo-200 mt-1 font-bold uppercase text-xs tracking-[0.2em] opacity-80">{{ $disciplinary->student->student_id }} — {{ $disciplinary->student->currentSection?->name ?? 'Unassigned' }}</p>
                            
                            <div class="flex flex-wrap justify-center md:justify-start gap-4 mt-8">
                                <div class="px-4 py-2 bg-white/5 rounded-xl border border-white/10">
                                    <span class="block text-[8px] font-black text-indigo-300 uppercase tracking-widest">Incident Chronology</span>
                                    <span class="block text-sm font-bold mt-1">{{ $disciplinary->incident_date->format('M d, Y') }}</span>
                                </div>
                                <div class="px-4 py-2 bg-white/5 rounded-xl border border-white/10">
                                    <span class="block text-[8px] font-black text-indigo-300 uppercase tracking-widest">Log Typology</span>
                                    <span class="block text-sm font-bold mt-1">{{ \App\Models\DisciplinaryRecord::incidentTypes()[$disciplinary->incident_type] ?? $disciplinary->incident_type }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descriptive Narrative -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Observation Narrative</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Primary field intelligence report</p>
                        </div>
                    </div>
                    <div class="p-8 bg-slate-50/50 rounded-[2rem] border border-slate-100/50 min-h-[150px]">
                        <p class="text-slate-700 font-semibold leading-relaxed">{{ $disciplinary->description }}</p>
                    </div>

                    @if($disciplinary->action_taken)
                        <div class="mt-8">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 ml-2">Tactical Interventions Deployed</h4>
                            <div class="p-6 bg-amber-50/30 rounded-[1.5rem] border border-amber-100/50">
                                <p class="text-slate-600 font-bold text-sm">{{ $disciplinary->action_taken }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                @if($disciplinary->resolution_notes)
                <div class="bg-emerald-900 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden group">
                    <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-white/10 text-white flex items-center justify-center shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-tight">Resolution Conclusion</h3>
                                <p class="text-xs font-bold text-emerald-200/50 uppercase tracking-widest mt-1 italic italic">Final administrative ruling</p>
                            </div>
                        </div>
                        <div class="p-6 bg-white/5 rounded-[1.5rem] border border-white/10 backdrop-blur-sm">
                            <p class="text-sm font-semibold text-emerald-50 leading-relaxed">{{ $disciplinary->resolution_notes }}</p>
                        </div>
                        <div class="mt-6 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-800 flex items-center justify-center text-[10px] font-black">✓</div>
                            <span class="text-[10px] font-black text-emerald-300 uppercase tracking-widest">Finalized on {{ $disciplinary->resolution_date->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Management Sidebar -->
            <div class="space-y-8">
                <!-- Status Intelligence -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-8">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Status Intelligence</h3>
                    </div>

                    <div class="space-y-6">
                        @php
                            $statusMap = [
                                'reported' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'label' => 'New Report'],
                                'under_review' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'label' => 'In Review'],
                                'resolved' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'label' => 'Resolved'],
                                'escalated' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-600', 'label' => 'Escalated'],
                            ];
                            $stat = $statusMap[$disciplinary->status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'label' => $disciplinary->status];
                        @endphp
                        <div class="p-6 {{ $stat['bg'] }} rounded-[2rem] text-center border-b-4 border-current border-opacity-10">
                            <span class="block text-[10px] uppercase font-black tracking-[0.3em] {{ $stat['text'] }} opacity-50 mb-2">Workflow State</span>
                            <span class="text-2xl font-black {{ $stat['text'] }} uppercase tracking-tighter">{{ $stat['label'] }}</span>
                        </div>

                        <div class="space-y-4 pt-4">
                            <div class="flex justify-between items-center px-2">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Reported By</span>
                                <span class="text-xs font-bold text-slate-700">{{ $disciplinary->reporter->name ?? 'System' }}</span>
                            </div>
                            <div class="flex justify-between items-center px-2 border-t border-slate-50 pt-4">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Incident Lead</span>
                                <span class="text-xs font-bold text-slate-700">{{ $disciplinary->handler->name ?? 'Awaiting Assignment' }}</span>
                            </div>
                            <div class="flex justify-between items-center px-2 border-t border-slate-50 pt-4">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Guardian Notified</span>
                                <span class="text-xs font-black {{ $disciplinary->notify_parent ? 'text-emerald-500' : 'text-slate-300 uppercase' }}">{{ $disciplinary->notify_parent ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($disciplinary->status !== 'resolved')
                <!-- Workflow Transitions -->
                <div class="bg-slate-900 rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden group border border-white">
                    <div class="absolute inset-0 bg-indigo-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-white/10 text-white flex items-center justify-center shadow-inner">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-white uppercase tracking-widest">Workflow Bridge</h3>
                        </div>

                        <form action="{{ route('admin.disciplinary.update', $disciplinary) }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-2">
                                <label class="px-2 text-[8px] font-black text-slate-400 uppercase tracking-[0.3em]">State Transition</label>
                                <select name="status" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm font-semibold text-white focus:ring-1 focus:ring-indigo-500 transition-all outline-none">
                                    <option value="reported" class="text-slate-900" {{ $disciplinary->status == 'reported' ? 'selected' : '' }}>Revert to Reported</option>
                                    <option value="under_review" class="text-slate-900" {{ $disciplinary->status == 'under_review' ? 'selected' : '' }}>Initiate Review</option>
                                    <option value="resolved" class="text-slate-900" {{ $disciplinary->status == 'resolved' ? 'selected' : '' }}>Resolve Incident</option>
                                    <option value="escalated" class="text-slate-900" {{ $disciplinary->status == 'escalated' ? 'selected' : '' }}>Escalate Authority</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="px-2 text-[8px] font-black text-slate-400 uppercase tracking-[0.3em]">Resolution Synthesis</label>
                                <textarea name="resolution_notes" rows="3" 
                                          class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-sm font-semibold text-white focus:ring-1 focus:ring-indigo-500 transition-all outline-none placeholder-white/10" 
                                          placeholder="Notes for final archival conclusion...">{{ old('resolution_notes', $disciplinary->resolution_notes) }}</textarea>
                            </div>

                            <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-700 shadow-xl shadow-indigo-900/40 transition-all flex items-center justify-center gap-2 group/btn">
                                Update Trace State
                                <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
