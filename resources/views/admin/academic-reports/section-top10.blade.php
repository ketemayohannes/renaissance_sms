<x-admin-layout>
    <x-slot name="header">Section Ranks: {{ $section->name }}</x-slot>

    <div class="space-y-8 pb-12">
        <!-- Modern Header & Actions (No-Print) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'Section Top 10', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                    Section Top 10
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.3em] italic">
                    {{ $section->gradeLevel->name }} — {{ $section->name }} | {{ $term->name }}
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 active:scale-95 group">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    Print Rankings
                </button>
            </div>
        </div>

        @if($top10->isEmpty())
            <div class="bg-white rounded-[2.5rem] border border-slate-200 p-12 text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">No student records found</h3>
                <p class="text-slate-500 text-sm mt-1">Make sure grade records are submitted and statistics are recalculated for this section.</p>
            </div>
        @else
            <!-- Podium Display (Top 3) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-end max-w-4xl mx-auto no-print">
                <!-- 2nd Place -->
                @if($top10->count() > 1)
                    @php $second = $top10[1]; @endphp
                    <div class="bg-white/80 backdrop-blur-md rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-xl shadow-slate-100 flex flex-col items-center relative overflow-hidden transition-all hover:scale-[1.03] duration-300">
                        <div class="absolute -right-8 -top-8 w-24 h-24 bg-slate-50 rounded-full blur-xl"></div>
                        <div class="w-16 h-16 rounded-full bg-slate-100 border-2 border-slate-300 flex items-center justify-center text-slate-500 text-xl font-bold mb-4 shadow-inner">
                            2
                        </div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight text-center">{{ $second['student']->full_name }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">ID: {{ $second['student']->student_id }}</p>
                        
                        <div class="mt-6 flex flex-col items-center bg-slate-50 rounded-2xl py-3 px-6 w-full border border-slate-100">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Average score</span>
                            <span class="text-2xl font-black text-slate-700 mt-1">
                                @php
                                    $secAvg = ($term->id === 'yearly')
                                        ? ($second['rows']['avg']['average'] ?? 0)
                                        : ($second['average'] ?? $second['rows']['avg']['average'] ?? 0);
                                @endphp
                                {{ number_format($secAvg, 2) }}%
                            </span>
                        </div>
                    </div>
                @endif

                <!-- 1st Place -->
                @if($top10->count() > 0)
                    @php $first = $top10[0]; @endphp
                    <div class="bg-gradient-to-b from-amber-50 to-white rounded-[2.5rem] border-2 border-amber-300 p-10 shadow-2xl shadow-amber-100/50 flex flex-col items-center relative overflow-hidden transition-all hover:scale-[1.05] duration-300 transform md:-translate-y-4">
                        <div class="absolute -right-8 -top-8 w-28 h-28 bg-amber-200/30 rounded-full blur-2xl"></div>
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 border-4 border-amber-200 flex items-center justify-center text-white text-3xl font-black mb-4 shadow-lg">
                            🏆
                        </div>
                        <h3 class="text-base font-black text-amber-950 tracking-tight text-center">{{ $first['student']->full_name }}</h3>
                        <p class="text-[10px] font-bold text-amber-600/80 uppercase tracking-widest mt-1">ID: {{ $first['student']->student_id }}</p>
                        
                        <div class="mt-6 flex flex-col items-center bg-amber-500/10 rounded-2xl py-4 px-6 w-full border border-amber-200">
                            <span class="text-[9px] font-black text-amber-800 uppercase tracking-wider">Average score</span>
                            <span class="text-3xl font-black text-amber-600 mt-1">
                                @php
                                    $firstAvg = ($term->id === 'yearly')
                                        ? ($first['rows']['avg']['average'] ?? 0)
                                        : ($first['average'] ?? $first['rows']['avg']['average'] ?? 0);
                                @endphp
                                {{ number_format($firstAvg, 2) }}%
                            </span>
                        </div>
                    </div>
                @endif

                <!-- 3rd Place -->
                @if($top10->count() > 2)
                    @php $third = $top10[2]; @endphp
                    <div class="bg-white/80 backdrop-blur-md rounded-[2.5rem] border-2 border-orange-200 p-8 shadow-xl shadow-orange-50/50 flex flex-col items-center relative overflow-hidden transition-all hover:scale-[1.03] duration-300">
                        <div class="absolute -right-8 -top-8 w-24 h-24 bg-orange-50 rounded-full blur-xl"></div>
                        <div class="w-16 h-16 rounded-full bg-orange-50 border-2 border-orange-300 flex items-center justify-center text-orange-600 text-xl font-bold mb-4 shadow-inner">
                            3
                        </div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight text-center">{{ $third['student']->full_name }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">ID: {{ $third['student']->student_id }}</p>
                        
                        <div class="mt-6 flex flex-col items-center bg-orange-50 rounded-2xl py-3 px-6 w-full border border-orange-100">
                            <span class="text-[9px] font-black text-orange-800 uppercase tracking-wider">Average score</span>
                            <span class="text-2xl font-black text-orange-600 mt-1">
                                @php
                                    $thirdAvg = ($term->id === 'yearly')
                                        ? ($third['rows']['avg']['average'] ?? 0)
                                        : ($third['average'] ?? $third['rows']['avg']['average'] ?? 0);
                                @endphp
                                {{ number_format($thirdAvg, 2) }}%
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- List Table -->
            <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm max-w-5xl mx-auto mt-12 print:border-0 print:shadow-none print:mt-0">
                <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between print:hidden">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Section Rank Table</h3>
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase rounded-lg tracking-wider">Top 10</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase tracking-widest text-[9px] font-black border-b border-slate-100">
                                <th class="py-4 px-6 text-center w-20">Rank</th>
                                <th class="py-4 px-6">Student Info</th>
                                <th class="py-4 px-6 text-center w-24">Gender</th>
                                <th class="py-4 px-6 text-right">Total Score</th>
                                <th class="py-4 px-6 text-right">Average Score</th>
                                <th class="py-4 px-6 text-center w-24">Conduct</th>
                                <th class="py-4 px-6 text-center w-24">Absences</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-700">
                            @foreach($top10 as $index => $row)
                                @php 
                                    $rank = $index + 1;
                                    $isTop3 = $rank <= 3;
                                    $bgClass = '';
                                    if ($rank === 1) $bgClass = 'bg-amber-500/5 hover:bg-amber-500/10';
                                    elseif ($rank === 2) $bgClass = 'bg-slate-500/5 hover:bg-slate-500/10';
                                    elseif ($rank === 3) $bgClass = 'bg-orange-500/5 hover:bg-orange-500/10';
                                    else $bgClass = 'hover:bg-slate-50/50';
                                    
                                    // Handle yearly vs regular term rows safely
                                    $isYearlyTerm = ($term->id === 'yearly');
                                    $displayAvg = $isYearlyTerm
                                        ? ($row['rows']['avg']['average'] ?? 0)
                                        : ($row['average'] ?? $row['rows']['avg']['average'] ?? 0);
                                    $displayTotal = $isYearlyTerm
                                        ? ($row['yearTotal'] ?? 0)
                                        : ($row['total'] ?? 0);
                                    $displayConduct = $row['conduct'] 
                                        ?? ($row['rows']['avg']['conduct'] ?? 'A');
                                    $displayAbsence = $row['absence'] ?? '_';
                                @endphp
                                <tr class="transition-colors {{ $bgClass }}">
                                    <td class="py-4 px-6 text-center">
                                        @if($rank === 1)
                                            <span class="inline-flex w-7 h-7 rounded-full bg-amber-500 text-white font-black items-center justify-center shadow-md shadow-amber-200">1</span>
                                        @elseif($rank === 2)
                                            <span class="inline-flex w-7 h-7 rounded-full bg-slate-400 text-white font-black items-center justify-center shadow-md shadow-slate-200">2</span>
                                        @elseif($rank === 3)
                                            <span class="inline-flex w-7 h-7 rounded-full bg-orange-400 text-white font-black items-center justify-center shadow-md shadow-orange-100">3</span>
                                        @else
                                            <span class="text-slate-400 font-bold">{{ $rank }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-black text-[10px] text-slate-500">
                                                {{ substr($row['student']->first_name, 0, 1) }}{{ substr($row['student']->last_name ?? '', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-900 text-sm">{{ $row['student']->full_name }}</div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">ID: {{ $row['student']->student_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded {{ ($row['student']->gender ?? 'M') === 'F' ? 'bg-pink-50 text-pink-600' : 'bg-blue-50 text-blue-600' }} text-[10px] font-bold">
                                            {{ $row['student']->gender ?? 'M' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right font-black text-slate-800">
                                        {{ number_format($displayTotal, 2) }}
                                    </td>
                                    <td class="py-4 px-6 text-right font-black text-slate-900 text-sm">
                                        {{ number_format($displayAvg, 2) }}%
                                    </td>
                                    <td class="py-4 px-6 text-center text-slate-600">
                                        {{ $displayConduct }}
                                    </td>
                                    <td class="py-4 px-6 text-center text-slate-600">
                                        {{ $displayAbsence }}
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
