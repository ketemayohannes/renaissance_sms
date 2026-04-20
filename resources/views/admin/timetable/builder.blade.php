<x-admin-layout>
    <div class="space-y-8" x-data="{ isSaving: false }">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Timetable Management', 'url' => route('admin.timetable.index')],
                    ['label' => 'Section ' . $section->name . ' Builder', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Timetable Builder</h1>
                <p class="text-slate-500 font-semibold mt-1">
                    {{ $section->gradeLevel->name }} &bull; Section {{ $section->name }} &bull; {{ $academicYear->name }}
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.timetable.index') }}" 
                   class="px-6 py-3 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-2xl border border-slate-200 hover:bg-slate-50 shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Selection
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-100 p-6 rounded-[2rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-emerald-900 font-black text-sm uppercase tracking-widest">Success</h3>
                    <p class="text-emerald-700 text-xs font-semibold mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error_conflicts'))
            <div class="bg-rose-50 border border-rose-100 p-8 rounded-[2rem] animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center text-rose-600 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-rose-900 font-black text-sm uppercase tracking-widest">Timetable Conflict Detected</h3>
                        <p class="text-rose-700 text-xs font-semibold mt-0.5">The following overlaps prevent this schedule from being saved:</p>
                    </div>
                </div>
                <ul class="space-y-2 ml-16">
                    @foreach(session('error_conflicts') as $error)
                        <li class="flex items-center gap-2 text-xs font-bold text-rose-600">
                            <div class="w-1.5 h-1.5 rounded-full bg-rose-400"></div>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.timetable.store') }}" method="POST">
            @csrf
            <input type="hidden" name="section_id" value="{{ $section->id }}">

            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden relative">
                <div class="overflow-x-auto custom-scrollbar pb-12">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center border-r border-slate-100 w-40">Period / Day</th>
                                @foreach($days as $dayNum => $dayName)
                                    <th class="px-6 py-5 text-[10px] font-black text-indigo-900 uppercase tracking-[0.2em] text-center min-w-[220px]">{{ $dayName }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($periods as $period)
                                <tr class="{{ $period->is_break ? 'bg-slate-50/50' : '' }}">
                                    <!-- Period Time Column -->
                                    <td class="px-6 py-4 border-r border-slate-100 text-center relative">
                                        @if($period->is_break)
                                            <div class="absolute inset-y-0 left-0 w-1 bg-amber-400"></div>
                                        @endif
                                        <div class="text-xs font-black text-slate-900 uppercase">{{ $period->name }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 mt-1 uppercase">
                                            {{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}
                                        </div>
                                    </td>

                                    <!-- Day Slots -->
                                    @foreach($days as $dayNum => $dayName)
                                        <td class="px-4 py-4 p-2">
                                            @if($period->is_break)
                                                <div class="flex items-center justify-center py-4 bg-amber-50/30 rounded-2xl border border-dashed border-amber-200/50">
                                                    <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest">{{ $period->name }}</span>
                                                </div>
                                            @else
                                                @php
                                                    $entry = $existingEntries->get("$dayNum-$period->id")?->first();
                                                @endphp
                                                <div class="space-y-2">
                                                    <!-- Subject Select -->
                                                    <select name="timetable[{{ $dayNum }}][{{ $period->id }}][assignment_id]" 
                                                            class="w-full text-[11px] font-bold border-slate-200 rounded-xl focus:ring-indigo-500 py-2.5 shadow-sm transition-all hover:border-indigo-300">
                                                        <option value="">- Select Subject -</option>
                                                        @foreach($assignments as $assignment)
                                                            <option value="{{ $assignment->id }}" {{ $entry && $entry->teacher_assignment_id == $assignment->id ? 'selected' : '' }}>
                                                                {{ $assignment->subject->name }} ({{ $assignment->teacher->first_name ?? 'N/A' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    
                                                    <!-- Room Number -->
                                                    <div class="relative group">
                                                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                                            <svg class="w-3 h-3 text-slate-400 group-focus-within:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                        </div>
                                                        <input type="text" 
                                                               name="timetable[{{ $dayNum }}][{{ $period->id }}][room]" 
                                                               value="{{ $entry->room_number ?? '' }}"
                                                               placeholder="Room (Optional)" 
                                                               class="w-full pl-8 text-[10px] font-semibold border-slate-200 rounded-xl focus:ring-indigo-500 py-1.5 shadow-sm transition-all hover:border-indigo-300">
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Actions -->
                <div class="p-8 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-xs font-bold text-slate-500 max-w-md">Conflicts (overlaps) are automatically checked upon saving. Ensure you have assigned teachers to subjects before building the timetable.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" 
                                @click="isSaving = true"
                                class="px-10 py-4 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-900 shadow-xl shadow-indigo-100 transition-all flex items-center gap-3 group">
                            <span x-show="!isSaving">Save Schedule Configuration</span>
                            <span x-show="isSaving" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Syncing Changes...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
