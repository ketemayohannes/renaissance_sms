<x-admin-layout>
    <x-slot name="header">Report Conduct Incident</x-slot>

    <div class="space-y-8 pb-20">
        <!-- Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Behavior Management', 'url' => route('admin.disciplinary.index')],
                    ['label' => 'New Incident Log', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-rose-600 rounded-full"></span>
                    Initiate Incident Trace
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Formal declaration of behavioral protocol breach</p>
            </div>
        </div>

        <form action="{{ route('admin.disciplinary.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Reporting Form -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-10">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 flex items-center justify-center shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Incident Core Data</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Detailed observation of the behavioral breach</p>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <!-- Student Selection -->
                            <div class="space-y-2">
                                <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Target Subject (Student)</label>
                                <select name="student_id" class="premium-select w-full" required>
                                    <option value="">Select Enrolled Student</option>
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}" {{ (old('student_id', $student?->id) == $s->id) ? 'selected' : '' }}>
                                            {{ $s->full_name }} — {{ $s->student_id }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest px-1 italic">Ensure correct subject identity mapping before submission.</p>
                            </div>

                            <!-- Narrative Description -->
                            <div class="space-y-2">
                                <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Incident Narrative</label>
                                <textarea name="description" rows="5" class="premium-input w-full py-4 min-h-[150px] leading-relaxed" 
                                          required placeholder="Provide a granular description of the observed behavior, including context, witnesses, and immediate impact...">{{ old('description') }}</textarea>
                            </div>

                            <!-- Action Taken -->
                            <div class="space-y-2">
                                <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Intervention Measures (Optional)</label>
                                <textarea name="action_taken" rows="2" class="premium-input w-full py-4 min-h-[80px]" 
                                          placeholder="State any immediate tactical measures deployed during the incident...">{{ old('action_taken') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Incident Parameters -->
                <div class="space-y-8">
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944V22m0-19.056c1.1 0 2.1.2 3 .6a11.955 11.955 0 018.618 3.04M12 2.944a11.955 11.955 0 00-8.618 3.04"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Incident Matrix</h3>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="px-1 text-[9px] font-black text-slate-400 uppercase tracking-widest">Breach Typography</label>
                                <select name="incident_type" class="premium-select w-full" required>
                                    @foreach(\App\Models\DisciplinaryRecord::incidentTypes() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="px-1 text-[9px] font-black text-slate-400 uppercase tracking-widest">Chronological Timestamp</label>
                                <input type="date" name="incident_date" value="{{ old('incident_date', date('Y-m-d')) }}" 
                                       class="premium-input w-full font-bold text-slate-700" required>
                            </div>

                            <div class="space-y-2">
                                <label class="px-1 text-[9px] font-black text-slate-400 uppercase tracking-widest">Criticality Rating</label>
                                <select name="severity" class="premium-select w-full border-rose-100 focus:ring-rose-500/20" required>
                                    @foreach(\App\Models\DisciplinaryRecord::severityLevels() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-50">
                            <label class="flex items-start gap-4 p-4 rounded-2xl bg-rose-50/50 border border-rose-100/50 cursor-pointer group">
                                <div class="relative flex items-center mt-1">
                                    <input type="checkbox" name="notify_parent" value="1" 
                                           class="w-5 h-5 rounded border-rose-300 text-rose-600 focus:ring-rose-500/20 transition-all">
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black text-rose-900 uppercase tracking-widest">Parental Synchronization</span>
                                    <span class="block text-[9px] font-semibold text-rose-600/70 uppercase mt-0.5">Automated notification dispatch</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Safety Warning -->
                    <div class="p-8 bg-slate-900 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden group">
                        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-rose-600/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="relative z-10">
                            <h4 class="font-black text-sm uppercase tracking-widest mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Protocol Compliance
                            </h4>
                            <p class="text-[10px] font-medium text-slate-400 leading-relaxed uppercase tracking-wider">All incident logs are irreversible once committed to the institutional archive. Maintain objective, non-emotional narrative standards throughout the report.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Command Hub -->
            <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 flex items-center gap-4 p-4 bg-white/80 backdrop-blur-2xl rounded-[2.5rem] border border-white shadow-2xl animate-in slide-in-from-bottom-12 duration-500">
                <a href="{{ route('admin.disciplinary.index') }}" class="px-8 py-4 bg-slate-100 text-slate-500 font-black text-[10px] uppercase tracking-widest rounded-full hover:bg-slate-200 transition-all">Abort Log</a>
                <button type="submit" class="px-12 py-4 bg-rose-600 text-white font-black text-[10px] uppercase tracking-widest rounded-full hover:bg-rose-700 transition-all shadow-xl shadow-rose-900/20 flex items-center gap-2 group">
                    Commit Incident Record
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
