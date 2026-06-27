<x-admin-layout>
    <x-slot name="header">Category Ranks</x-slot>

    <div class="space-y-8 pb-12">
        <!-- Modern Header & Actions (No-Print) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'Category Ranks', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                    Category Rankings
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.3em] italic">
                    {{ $academicYear->name }} | {{ $term->name }}
                    @if($divisionCode)
                        &bull; {{ $divisionCode === 'ES' ? 'Elementary' : 'High School' }}
                    @else
                        &bull; All Divisions
                    @endif
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 active:scale-95 group">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    Print Category Ranks
                </button>
            </div>
        </div>

        @php
            // Hardcoded styles per category key — avoids Tailwind JIT purging dynamic class strings
            $categoryStyles = [
                'grade_1_6' => [
                    'header'    => 'bg-indigo-600',
                    'subtext'   => 'text-indigo-200',
                    'rank1_bg'  => 'bg-amber-500',
                    'rank2_bg'  => 'bg-slate-400',
                    'rank3_bg'  => 'bg-orange-400',
                    'row1_bg'   => 'bg-amber-500/5',
                    'row2_bg'   => 'bg-slate-500/5',
                    'row3_bg'   => 'bg-orange-500/5',
                ],
                'grade_7_8' => [
                    'header'    => 'bg-violet-600',
                    'subtext'   => 'text-violet-200',
                    'rank1_bg'  => 'bg-amber-500',
                    'rank2_bg'  => 'bg-slate-400',
                    'rank3_bg'  => 'bg-orange-400',
                    'row1_bg'   => 'bg-amber-500/5',
                    'row2_bg'   => 'bg-slate-500/5',
                    'row3_bg'   => 'bg-orange-500/5',
                ],
                'grade_9_12' => [
                    'header'    => 'bg-amber-600',
                    'subtext'   => 'text-amber-200',
                    'rank1_bg'  => 'bg-amber-500',
                    'rank2_bg'  => 'bg-slate-400',
                    'rank3_bg'  => 'bg-orange-400',
                    'row1_bg'   => 'bg-amber-500/5',
                    'row2_bg'   => 'bg-slate-500/5',
                    'row3_bg'   => 'bg-orange-500/5',
                ],
            ];
            $colCount = count($results);
            $gridClass = match($colCount) {
                1       => 'grid-cols-1 max-w-lg mx-auto',
                2       => 'grid-cols-1 lg:grid-cols-2',
                default => 'grid-cols-1 lg:grid-cols-3',
            };
        @endphp

        <!-- Category Columns -->
        <div class="grid {{ $gridClass }} gap-8">
            @foreach($results as $key => $category)
                @php
                    $styles = $categoryStyles[$key] ?? $categoryStyles['grade_1_6'];
                    $records = $category['records'];
                @endphp
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition-shadow duration-300">
                    
                    <!-- Category Header — hardcoded bg class per category -->
                    <div class="p-6 border-b border-white/10 {{ $styles['header'] }} text-white">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em]">{{ $category['name'] }}</h3>
                        <p class="text-[9px] font-bold {{ $styles['subtext'] }} uppercase tracking-widest mt-1">Top Ranked Students</p>
                    </div>

                    <div class="flex-1 overflow-x-auto">
                        @if($records->isEmpty())
                            <div class="p-12 text-center text-slate-400 italic text-sm">
                                No records found for this category.
                            </div>
                        @else
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-400 uppercase tracking-widest text-[8px] font-black border-b border-slate-100">
                                        <th class="py-3 px-4 text-center w-12">Rank</th>
                                        <th class="py-3 px-4">Student Info</th>
                                        <th class="py-3 px-4 text-right w-24">Avg Score</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-[11px] font-bold text-slate-700">
                                    @foreach($records as $index => $row)
                                        @php 
                                            $rank = $index + 1;
                                            $rowBg = match(true) {
                                                $rank === 1 => $styles['row1_bg'],
                                                $rank === 2 => $styles['row2_bg'],
                                                $rank === 3 => $styles['row3_bg'],
                                                default     => '',
                                            };
                                            $rankBg = match(true) {
                                                $rank === 1 => $styles['rank1_bg'],
                                                $rank === 2 => $styles['rank2_bg'],
                                                $rank === 3 => $styles['rank3_bg'],
                                                default     => '',
                                            };
                                        @endphp
                                        <tr class="transition-colors hover:bg-slate-50 {{ $rowBg }}">
                                            <td class="py-3 px-4 text-center">
                                                @if($rank <= 3)
                                                    <span class="inline-flex w-5 h-5 rounded-full {{ $rankBg }} text-white font-black items-center justify-center text-[9px] shadow-sm">{{ $rank }}</span>
                                                @else
                                                    <span class="text-slate-400 font-bold">{{ $rank }}</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                <div>
                                                    <div class="font-black text-slate-900 text-xs">{{ $row->first_name }} {{ $row->father_name }}</div>
                                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">{{ $row->grade_level_name }} &mdash; {{ $row->section_name }}</div>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-right font-black text-slate-900 text-xs">
                                                {{ number_format($row->average_score, 2) }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>
