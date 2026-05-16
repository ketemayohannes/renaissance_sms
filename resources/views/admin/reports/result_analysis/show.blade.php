<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between no-print">
            <h2 class="font-heading font-black text-xl md:text-2xl text-slate-900 tracking-tight flex items-start gap-3">
                <span class="w-2 h-8 bg-rose-600 rounded-full shadow-[0_0_15px_rgba(225,29,72,0.4)] mt-1 flex-shrink-0"></span>
                <div class="leading-tight">
                    <span class="block text-slate-400 text-[10px] uppercase tracking-[0.2em] mb-1">Result Analysis Report</span>
                    {{ $assignment->teacher->name }} — {{ $assignment->section->gradeLevel->name }}<br>
                    <span class="text-rose-600">{{ $sectionLabel }}</span>: {{ $assignment->subject->name }}
                </div>
            </h2>
            <div class="flex gap-4 flex-shrink-0">
                <a href="{{ route('admin.result-analysis.index') }}" 
                   class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all active:scale-95 flex items-center gap-2 whitespace-nowrap shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </a>
                {{-- Add print link here later if needed --}}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-8">
                <!-- Analysis Stats Grid Table -->
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-2xl shadow-slate-200/50 overflow-hidden">
                    <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Performance Distribution By Section</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $term->name }} — {{ $academicYear->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Prepared By</p>
                            <p class="text-sm font-black text-indigo-600 uppercase tracking-tight">{{ $assignment->teacher->name }}</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto p-4">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th rowspan="2" class="px-4 py-4 border border-slate-200 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Sections</th>
                                    <th colspan="3" class="px-4 py-2 border border-slate-200 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">0 - 49</th>
                                    <th colspan="3" class="px-4 py-2 border border-slate-200 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">50 - 74</th>
                                    <th colspan="3" class="px-4 py-2 border border-slate-200 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">75 - 100</th>
                                    <th colspan="3" class="px-4 py-2 border border-slate-200 text-center text-[10px] font-black text-indigo-400 uppercase tracking-widest bg-indigo-50/30">Total Student</th>
                                    <th rowspan="2" class="px-4 py-4 border border-slate-200 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Remark</th>
                                </tr>
                                <tr class="bg-slate-50">
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">M</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">F</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase bg-slate-100">T</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">M</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">F</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase bg-slate-100">T</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">M</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">F</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase bg-slate-100">T</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-indigo-400 uppercase bg-indigo-50/30">M</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-indigo-400 uppercase bg-indigo-50/30">F</th>
                                    <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-indigo-400 uppercase bg-indigo-500 text-white">T</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($sectionData as $data)
                                    @php 
                                        $secAnalysis = $data['analysis'];
                                        $secReport = $data['report'];
                                        $secAssignment = $data['assignment'];
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-4 py-4 border border-slate-100 text-sm font-black text-slate-900 text-center bg-slate-50/30">
                                            {{ $secAssignment->section->name }}
                                        </td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['0-49']['male'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['0-49']['female'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-slate-900 bg-slate-50">{{ $secAnalysis['0-49']['total'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['50-74']['male'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['50-74']['female'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-slate-900 bg-slate-50">{{ $secAnalysis['50-74']['total'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['75-100']['male'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['75-100']['female'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-slate-900 bg-slate-50">{{ $secAnalysis['75-100']['total'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-indigo-600 bg-indigo-50/30">{{ $secAnalysis['total_students']['male'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-indigo-600 bg-indigo-50/30">{{ $secAnalysis['total_students']['female'] }}</td>
                                        <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-white bg-indigo-600">{{ $secAnalysis['total_students']['total'] }}</td>
                                        <td class="px-4 py-4 border border-slate-100 italic text-[11px] text-slate-500">
                                            {{ $secReport->section_remark ?? 'No remarks provided.' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-900 text-white">
                                    <td class="px-4 py-4 text-center text-[10px] font-black uppercase tracking-widest">Total</td>
                                    <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['0-49']['male'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['0-49']['female'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-black bg-white/10">{{ $grandTotalAnalysis['0-49']['total'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['50-74']['male'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['50-74']['female'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-black bg-white/10">{{ $grandTotalAnalysis['50-74']['total'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['75-100']['male'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['75-100']['female'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-black bg-white/10">{{ $grandTotalAnalysis['75-100']['total'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-black bg-indigo-500/20">{{ $grandTotalAnalysis['total_students']['male'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-black bg-indigo-500/20">{{ $grandTotalAnalysis['total_students']['female'] }}</td>
                                    <td class="px-2 py-4 text-center text-xs font-black bg-indigo-500">{{ $grandTotalAnalysis['total_students']['total'] }}</td>
                                    <td class="px-4 py-4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Detailed Analysis View -->
                <div class="bg-white rounded-[2.5rem] p-10 border border-slate-200 shadow-2xl shadow-slate-200/50 space-y-10">
                    <div>
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Detailed Teacher Analysis</h4>
                        
                        <div class="grid grid-cols-1 gap-8">
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">A. Subject Teacher’s Comment Based on Comparison:</label>
                                <div class="p-5 rounded-2xl bg-slate-50 text-sm text-slate-700 leading-relaxed border border-slate-100">
                                    {{ $globalReport->comparison_comment ?? 'No comment provided.' }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">B. Problems Encountered:</label>
                                <div class="p-5 rounded-2xl bg-slate-50 text-sm text-slate-700 leading-relaxed border border-slate-100">
                                    {{ $globalReport->problems_encountered ?? 'No problems documented.' }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">C. Solutions Implemented:</label>
                                <div class="p-5 rounded-2xl bg-slate-50 text-sm text-slate-700 leading-relaxed border border-slate-100">
                                    {{ $globalReport->solutions_implemented ?? 'No solutions documented.' }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">D. Additional Comment:</label>
                                <div class="p-5 rounded-2xl bg-slate-50 text-sm text-slate-700 leading-relaxed border border-slate-100">
                                    {{ $globalReport->additional_comment ?? 'No additional comments.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
