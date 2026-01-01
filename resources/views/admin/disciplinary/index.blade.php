<x-admin-layout>
    <x-slot name="header">Behavior & Conduct</x-slot>

    <div class="space-y-8 pb-12">
        <!-- Header & Action Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Behavior Management', 'url' => '#'],
                    ['label' => 'Incident Records', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-rose-600 rounded-full"></span>
                    Behavior Log
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Tracking and resolution of student conduct incidents</p>
            </div>
            
            <a href="{{ route('admin.disciplinary.create') }}" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-[2rem] hover:bg-rose-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 group">
                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:bg-white/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                Report Incident
            </a>
        </div>

        <!-- Analytical Context -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
             @php
                $criticalCount = \App\Models\DisciplinaryRecord::where('academic_year_id', $academicYear->id)->where('severity', 'critical')->count();
                $pendingCount = \App\Models\DisciplinaryRecord::where('academic_year_id', $academicYear->id)->where('status', 'reported')->count();
                $totalIncidents = $records->total();
            @endphp
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 border border-white shadow-xl shadow-slate-200/50">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Incidents</span>
                <span class="block text-3xl font-black text-slate-900 mt-2 italic tracking-tighter">{{ $totalIncidents }}</span>
                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Total academic year log</p>
            </div>
            <div class="bg-rose-50/50 backdrop-blur-xl rounded-[2rem] p-6 border border-rose-100 shadow-xl shadow-rose-200/20">
                <span class="block text-[10px] font-black text-rose-400 uppercase tracking-widest">Critical Alert</span>
                <span class="block text-3xl font-black text-rose-600 mt-2 italic tracking-tighter">{{ $criticalCount }}</span>
                <p class="text-[9px] font-bold text-rose-400 uppercase mt-1">Requiring immediate action</p>
            </div>
            <div class="bg-amber-50/50 backdrop-blur-xl rounded-[2rem] p-6 border border-amber-100 shadow-xl shadow-amber-200/20">
                <span class="block text-[10px] font-black text-amber-500 uppercase tracking-widest">Pending Review</span>
                <span class="block text-3xl font-black text-amber-600 mt-2 italic tracking-tighter">{{ $pendingCount }}</span>
                <p class="text-[9px] font-bold text-amber-400 uppercase mt-1">New reported incidents</p>
            </div>
            <div class="bg-indigo-50/50 backdrop-blur-xl rounded-[2rem] p-6 border border-indigo-100 shadow-xl shadow-indigo-200/20">
                <span class="block text-[10px] font-black text-indigo-500 uppercase tracking-widest">Academic Year</span>
                <span class="block text-lg font-black text-indigo-900 mt-2 truncate">{{ $academicYear->name }}</span>
            </div>
        </div>

        <!-- Filter & Search Panel -->
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-6 flex flex-col lg:flex-row items-center justify-between gap-6">
            <form action="{{ route('admin.disciplinary.index') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full">
                <div class="flex-1 min-w-[200px]">
                    <select name="severity" class="premium-select w-full bg-white/60">
                        <option value="">Filter Severity</option>
                        @foreach(\App\Models\DisciplinaryRecord::severityLevels() as $key => $label)
                            <option value="{{ $key }}" {{ request('severity') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <select name="status" class="premium-select w-full bg-white/60">
                        <option value="">Filter Status</option>
                        <option value="reported" {{ request('status') == 'reported' ? 'selected' : '' }}>Reported</option>
                        <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="escalated" {{ request('status') == 'escalated' ? 'selected' : '' }}>Escalated</option>
                    </select>
                </div>
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">
                    Apply Filter Matrix
                </button>
                @if(request()->anyFilled(['severity', 'status']))
                    <a href="{{ route('admin.disciplinary.index') }}" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-rose-500 transition-colors">Clear All</a>
                @endif
            </form>
        </div>

        <!-- Records Table -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Incident Timing</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Involved Subject</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Log Type</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Severity</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Workflow</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Reporter</th>
                            <th class="px-8 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($records as $record)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-6">
                                <span class="block text-sm font-bold text-slate-900">{{ $record->incident_date->format('M d, Y') }}</span>
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Trace № {{ $record->id }}</span>
                            </td>
                            <td class="px-6 py-6">
                                <a href="{{ route('admin.students.show', $record->student) }}" class="flex items-center gap-3 group/link">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-xs shadow-sm group-hover/link:bg-indigo-600 group-hover/link:text-white transition-all">
                                        {{ substr($record->student->first_name, 0, 1) }}{{ substr($record->student->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="block text-sm font-black text-slate-900 group-hover/link:text-indigo-600 transition-colors">{{ $record->student->full_name }}</span>
                                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ $record->student->student_id }}</span>
                                    </div>
                                </a>
                            </td>
                            <td class="px-6 py-6">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                    {{ \App\Models\DisciplinaryRecord::incidentTypes()[$record->incident_type] ?? $record->incident_type }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-center">
                                @php
                                    $severityColors = [
                                        'minor' => 'bg-emerald-100 text-emerald-600',
                                        'moderate' => 'bg-amber-100 text-amber-600',
                                        'major' => 'bg-orange-100 text-orange-600',
                                        'critical' => 'bg-rose-100 text-rose-600',
                                    ];
                                @endphp
                                <span class="px-3 py-1.5 {{ $severityColors[$record->severity] ?? 'bg-slate-100 text-slate-500' }} rounded-xl text-[10px] font-black uppercase tracking-widest">
                                    {{ $record->severity }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-center">
                                @php
                                    $statusColors = [
                                        'reported' => 'bg-blue-50 text-blue-500',
                                        'under_review' => 'bg-amber-50 text-amber-500',
                                        'resolved' => 'bg-emerald-50 text-emerald-500',
                                        'escalated' => 'bg-rose-50 text-rose-500',
                                    ];
                                @endphp
                                <span class="px-3 py-1.5 {{ $statusColors[$record->status] ?? 'bg-slate-50 text-slate-400' }} rounded-xl text-[10px] font-black uppercase tracking-widest border border-current opacity-70">
                                    {{ str_replace('_', ' ', $record->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700">{{ $record->reporter->name ?? 'System Identity' }}</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Authorized Agent</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <a href="{{ route('admin.disciplinary.show', $record) }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                                    View Log
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-12 py-24 text-center">
                                <div class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 mx-auto mb-6">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944V22m0-19.056c1.1 0 2.1.2 3 .6a11.955 11.955 0 018.618 3.04M12 2.944a11.955 11.955 0 00-8.618 3.04"></path></svg>
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 tracking-tight italic">Conduct Archives Empty</h3>
                                <p class="text-slate-500 font-semibold mt-2 text-sm max-w-sm mx-auto">No disciplinary incidents have been registered for the current academic infrastructure.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="px-8 py-6 bg-slate-50/40 border-t border-slate-100">
                    {{ $records->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
