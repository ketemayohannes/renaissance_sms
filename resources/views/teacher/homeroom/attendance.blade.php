<x-teacher-layout>
    <x-slot name="header">
        Daily Attendance: {{ $section->gradeLevel->name }} - {{ $section->name }}
    </x-slot>

    <div class="space-y-6">
        <!-- Filter & Date Selection -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">Attendance Date</h3>
                    <form action="{{ route('teacher.homeroom.attendance') }}" method="GET" class="mt-1 flex items-center gap-2">
                        <input type="date" name="date" value="{{ $date }}" max="{{ now()->format('Y-m-d') }}" 
                               class="bg-slate-50 border-slate-200 rounded-lg text-sm font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500"
                               onchange="this.form.submit()">
                    </form>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Status Summary</p>
                    <p class="text-sm font-medium text-slate-600">{{ $students->count() }} Students Enrolled</p>
                </div>
            </div>
        </div>

        <!-- Attendance Form -->
        <form action="{{ route('teacher.homeroom.attendance.store') }}" method="POST" x-data="{ 
            allPresent() {
                const radios = document.querySelectorAll('input[value=\'present\']');
                radios.forEach(radio => radio.checked = true);
            }
        }">
            @csrf
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <input type="hidden" name="attendance_date" value="{{ $date }}">

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 font-heading">Student List</h2>
                    <button type="button" @click="allPresent()" class="text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                        Mark All as Present
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest w-12 text-center">#</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Student</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $enrollment)
                            @php
                                $existing = $enrollment->student->attendance->first();
                                $currentStatus = $existing ? $existing->status : 'present';
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 text-xs font-black text-slate-400 text-center bg-slate-50/30">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs">
                                            {{ substr($enrollment->student->user->name, 0, 1) }}
                                        </div>
                                        <div class="font-bold text-slate-900">{{ $enrollment->student->user->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        @foreach(['present' => 'P', 'absent' => 'A', 'late' => 'L', 'excused' => 'E'] as $value => $label)
                                            <label class="relative flex items-center justify-center cursor-pointer group">
                                                <input type="radio" name="attendance[{{ $enrollment->student->id }}]" value="{{ $value }}" 
                                                       class="peer sr-only" {{ $currentStatus === $value ? 'checked' : '' }}>
                                                <div class="w-8 h-8 flex items-center justify-center rounded-lg border-2 border-slate-100 text-xs font-bold transition-all peer-checked:border-0
                                                    @if($value === 'present') peer-checked:bg-emerald-500 peer-checked:text-white @elseif($value === 'absent') peer-checked:bg-rose-500 peer-checked:text-white @elseif($value === 'late') peer-checked:bg-amber-500 peer-checked:text-white @else peer-checked:bg-indigo-500 peer-checked:text-white @endif">
                                                    {{ $label }}
                                                </div>
                                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                                                    {{ ucfirst($value) }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" name="remarks[{{ $enrollment->student->id }}]" 
                                           value="{{ $existing ? $existing->remarks : '' }}"
                                           placeholder="Optional note..."
                                           class="w-full text-xs bg-slate-50 border-slate-100 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 hover:scale-[1.02] active:scale-[0.98]">
                    Save Attendance
                </button>
            </div>
        </form>
    </div>
</x-teacher-layout>
