<x-admin-layout>
    <x-slot name="header">Academic Excellence</x-slot>

    <div class="space-y-8 pb-12">
        <!-- Modern Header & Actions (No-Print) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'Academic Excellence', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                    Academic Excellence
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.3em] italic">
                    {{ $academicYear->name }} | {{ $term->name }}
                    @if($selectedSection)
                        &bull; {{ $selectedSection->gradeLevel->name }} - {{ $selectedSection->name }}
                    @elseif($selectedGrade)
                        &bull; {{ $selectedGrade->name }}
                    @else
                        &bull; School Wide
                    @endif
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 active:scale-95 group">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    Print Certified List
                </button>
            </div>
        </div>

        <!-- Excellence Banner -->
        <div class="bg-gradient-to-r from-amber-500 to-yellow-600 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -left-12 -bottom-12 w-36 h-36 bg-amber-400/20 rounded-full blur-xl"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md rounded-full border border-white/20 mb-4 text-[9px] font-black uppercase tracking-[0.25em]">
                        ⭐ Honor Roll
                    </span>
                    <h2 class="text-3xl font-black tracking-tight leading-none uppercase font-heading italic">High Achievers</h2>
                    <p class="text-amber-100 text-[11px] font-bold uppercase tracking-widest mt-2">Students who achieved an overall average score of 90% and above</p>
                </div>
                <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/20 shadow-xl min-w-[180px] text-center">
                    <span class="text-[9px] font-black text-amber-200 uppercase tracking-widest block mb-1">Total Achievers</span>
                    <span class="text-4xl font-black tracking-tighter">{{ $records->count() }}</span>
                </div>
            </div>
        </div>

        @if($records->isEmpty())
            <div class="bg-white rounded-[2.5rem] border border-slate-200 p-16 text-center shadow-sm">
                <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-400 mx-auto mb-4 border border-amber-100">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.175 0l-3.97 2.883c-.783.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.3c-.773-.57-.375-1.81.587-1.81H8.3a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">No high achievers found</h3>
                <p class="text-slate-500 text-sm mt-1">There are no students with averages of 90% or above in the selected scope.</p>
            </div>
        @else
            <!-- List Table -->
            <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm max-w-5xl mx-auto">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 uppercase tracking-widest text-[9px] font-black border-b border-slate-100">
                                <th class="py-4 px-6 text-center w-20">No</th>
                                <th class="py-4 px-6">Student Info</th>
                                <th class="py-4 px-6 text-center">Grade Level</th>
                                <th class="py-4 px-6 text-center">Section</th>
                                <th class="py-4 px-6 text-right w-36">Average Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-700">
                            @foreach($records as $index => $row)
                                <tr class="transition-colors hover:bg-slate-50/50">
                                    <td class="py-4 px-6 text-center text-slate-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 flex items-center justify-center font-black text-[12px] text-amber-600 border border-amber-500/20">
                                                ★
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-900 text-sm">{{ $row->first_name }} {{ $row->father_name }} {{ $row->grandfather_name }}</div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">ID: {{ $row->student_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center text-slate-600">
                                        {{ $row->grade_level_name }}
                                    </td>
                                    <td class="py-4 px-6 text-center text-slate-600">
                                        {{ $row->section_name }}
                                    </td>
                                    <td class="py-4 px-6 text-right font-black text-amber-600 text-sm">
                                        {{ number_format($row->average_score, 2) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>
