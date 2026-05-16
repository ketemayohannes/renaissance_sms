<x-teacher-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between no-print">
            <h2 class="font-heading font-black text-2xl text-slate-900 tracking-tight flex items-center gap-3">
                <span class="w-2 h-8 bg-rose-600 rounded-full shadow-[0_0_15px_rgba(225,29,72,0.4)]"></span>
                Result Analysis: {{ $assignment->section->gradeLevel->name }} - {{ $assignment->subject->name }}
            </h2>
            <div class="flex gap-4">
                <a href="{{ route('teacher.reports.result-analysis.print', ['assignment' => $assignment->id, 'term_id' => $term->id]) }}" 
                   target="_blank"
                   class="px-6 py-3 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-800 shadow-xl transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Analysis
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('teacher.reports.result-analysis.store', $assignment) }}" method="POST">
                @csrf
                <input type="hidden" name="term_id" value="{{ $term->id }}">
                
                <div class="space-y-8">
                    <!-- Analysis Stats Grid Table -->
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-2xl shadow-slate-200/50 overflow-hidden">
                        <div class="p-8 border-b border-slate-100">
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Performance Distribution By Section</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $term->name }} — {{ $academicYear->name }}</p>
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
                                        <!-- 0-49 -->
                                        <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">M</th>
                                        <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">F</th>
                                        <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase bg-slate-100">T</th>
                                        <!-- 50-74 -->
                                        <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">M</th>
                                        <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">F</th>
                                        <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase bg-slate-100">T</th>
                                        <!-- 75-100 -->
                                        <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">M</th>
                                        <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase">F</th>
                                        <th class="px-2 py-2 border border-slate-200 text-center text-[9px] font-black text-slate-400 uppercase bg-slate-100">T</th>
                                        <!-- Total -->
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
                                            <!-- 0-49 -->
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['0-49']['male'] }}</td>
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['0-49']['female'] }}</td>
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-slate-900 bg-slate-50">{{ $secAnalysis['0-49']['total'] }}</td>
                                            <!-- 50-74 -->
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['50-74']['male'] }}</td>
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['50-74']['female'] }}</td>
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-slate-900 bg-slate-50">{{ $secAnalysis['50-74']['total'] }}</td>
                                            <!-- 75-100 -->
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['75-100']['male'] }}</td>
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs text-slate-600">{{ $secAnalysis['75-100']['female'] }}</td>
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-slate-900 bg-slate-50">{{ $secAnalysis['75-100']['total'] }}</td>
                                            <!-- Total -->
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-indigo-600 bg-indigo-50/30">{{ $secAnalysis['total_students']['male'] }}</td>
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-indigo-600 bg-indigo-50/30">{{ $secAnalysis['total_students']['female'] }}</td>
                                            <td class="px-2 py-4 border border-slate-100 text-center text-xs font-black text-white bg-indigo-600 shadow-inner">{{ $secAnalysis['total_students']['total'] }}</td>
                                            
                                            <td class="px-4 py-2 border border-slate-100">
                                                <textarea name="remarks[{{ $secAssignment->id }}]" rows="2" 
                                                          class="w-full rounded-xl border-slate-100 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 text-[11px] transition-all placeholder:text-slate-300"
                                                          placeholder="Enter remarks...">{{ old("remarks.{$secAssignment->id}", $secReport->section_remark ?? '') }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-900 text-white">
                                        <td class="px-4 py-4 text-center text-[10px] font-black uppercase tracking-widest">Total</td>
                                        <!-- 0-49 -->
                                        <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['0-49']['male'] }}</td>
                                        <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['0-49']['female'] }}</td>
                                        <td class="px-2 py-4 text-center text-xs font-black bg-white/10">{{ $grandTotalAnalysis['0-49']['total'] }}</td>
                                        <!-- 50-74 -->
                                        <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['50-74']['male'] }}</td>
                                        <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['50-74']['female'] }}</td>
                                        <td class="px-2 py-4 text-center text-xs font-black bg-white/10">{{ $grandTotalAnalysis['50-74']['total'] }}</td>
                                        <!-- 75-100 -->
                                        <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['75-100']['male'] }}</td>
                                        <td class="px-2 py-4 text-center text-xs font-bold">{{ $grandTotalAnalysis['75-100']['female'] }}</td>
                                        <td class="px-2 py-4 text-center text-xs font-black bg-white/10">{{ $grandTotalAnalysis['75-100']['total'] }}</td>
                                        <!-- Grand Total -->
                                        <td class="px-2 py-4 text-center text-xs font-black bg-indigo-500/20">{{ $grandTotalAnalysis['total_students']['male'] }}</td>
                                        <td class="px-2 py-4 text-center text-xs font-black bg-indigo-500/20">{{ $grandTotalAnalysis['total_students']['female'] }}</td>
                                        <td class="px-2 py-4 text-center text-xs font-black bg-indigo-500">{{ $grandTotalAnalysis['total_students']['total'] }}</td>
                                        <td class="px-4 py-4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Detailed Analysis Sections -->
                    <div class="bg-white rounded-[2.5rem] p-10 border border-slate-200 shadow-2xl shadow-slate-200/50 space-y-10">
                        <div>
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Detailed Teacher Analysis</h4>
                            
                            <div class="grid grid-cols-1 gap-8">
                                <!-- A. Subject Teacher’s Comment -->
                                <div class="space-y-4">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">A. Subject Teacher’s Comment Based on a Comparison of the Current Results with Previous Quarters:</label>
                                    <textarea name="comparison_comment" rows="3" 
                                              class="w-full rounded-2xl border-slate-100 bg-slate-50/50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all p-4">{{ old('comparison_comment', $globalReport->comparison_comment ?? '') }}</textarea>
                                </div>

                                <!-- B. Problems Encountered -->
                                <div class="space-y-4">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">B. Problems Encountered:</label>
                                    <textarea name="problems_encountered" rows="3" 
                                              class="w-full rounded-2xl border-slate-100 bg-slate-50/50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all p-4">{{ old('problems_encountered', $globalReport->problems_encountered ?? '') }}</textarea>
                                </div>

                                <!-- C. Solutions Implemented -->
                                <div class="space-y-4">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">C. Solutions Implemented:</label>
                                    <textarea name="solutions_implemented" rows="3" 
                                              class="w-full rounded-2xl border-slate-100 bg-slate-50/50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all p-4">{{ old('solutions_implemented', $globalReport->solutions_implemented ?? '') }}</textarea>
                                </div>

                                <!-- D. Additional Comment -->
                                <div class="space-y-4">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">D. Additional Comment:</label>
                                    <textarea name="additional_comment" rows="3" 
                                              class="w-full rounded-2xl border-slate-100 bg-slate-50/50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all p-4">{{ old('additional_comment', $globalReport->additional_comment ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="px-10 py-5 bg-indigo-600 text-white font-black text-xs uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 shadow-2xl shadow-indigo-200 transition-all active:scale-95 flex items-center gap-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Save Analysis Report
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-teacher-layout>
