<x-admin-layout>
    <div class="space-y-8" x-data="attendanceManager()">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Attendance Hub', 'url' => route('admin.attendance.index')],
                    ['label' => 'Mark Attendance', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 text-balance">Attendance Terminal</h1>
                <p class="text-slate-500 font-semibold mt-1">Finalizing roster for {{ $section->gradeLevel->name }} — {{ $section->name }}</p>
            </div>

            <!-- Unsaved Changes Badge -->
            <div x-show="hasChanges" x-transition 
                 class="flex items-center gap-3 text-amber-600 bg-amber-50/80 backdrop-blur-md px-6 py-2.5 rounded-2xl border border-amber-100 shadow-xl shadow-amber-200/20 animate-pulse-subtle">
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="absolute top-0 right-0 h-2 w-2 bg-amber-500 rounded-full"></span>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest">Unsaved Modifications Detected</span>
            </div>
        </div>
        
        <!-- Premium Stats Grid (Matched with Student List) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Present -->
            <div class="bg-white/60 backdrop-blur-md p-5 rounded-3xl border border-white shadow-sm flex items-center gap-4 transition-all hover:shadow-md hover:bg-white/80">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm shadow-emerald-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Present</p>
                    <p class="text-2xl font-black text-slate-900 leading-tight" x-text="stats.present">0</p>
                </div>
            </div>

            <!-- Absent -->
            <div class="bg-white/60 backdrop-blur-md p-5 rounded-3xl border border-white shadow-sm flex items-center gap-4 transition-all hover:shadow-md hover:bg-white/80">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-sm shadow-rose-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Absent</p>
                    <p class="text-2xl font-black text-slate-900 leading-tight" x-text="stats.absent">0</p>
                </div>
            </div>

            <!-- Late -->
            <div class="bg-white/60 backdrop-blur-md p-5 rounded-3xl border border-white shadow-sm flex items-center gap-4 transition-all hover:shadow-md hover:bg-white/80">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm shadow-amber-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Late</p>
                    <p class="text-2xl font-black text-slate-900 leading-tight" x-text="stats.late">0</p>
                </div>
            </div>

            <!-- Excused -->
            <div class="bg-white/60 backdrop-blur-md p-5 rounded-3xl border border-white shadow-sm flex items-center gap-4 transition-all hover:shadow-md hover:bg-white/80">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm shadow-blue-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Excused</p>
                    <p class="text-2xl font-black text-slate-900 leading-tight" x-text="stats.excused">0</p>
                </div>
            </div>

            <!-- Total -->
            <div class="bg-slate-900 p-5 rounded-3xl border border-slate-800 shadow-xl shadow-slate-200/50 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Active Net</p>
                    <p class="text-2xl font-black text-white leading-tight">{{ $students->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Hub Quick Actions -->
        <div class="flex flex-wrap items-center gap-4 p-6 bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-2">Batch Actions</span>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="markAll('present')" class="inline-flex items-center px-6 py-2.5 bg-emerald-50 text-emerald-700 text-xs font-black rounded-xl hover:bg-emerald-100 transition-all gap-2 group tracking-widest">
                    <svg class="w-4 h-4 transition-transform group-active:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    MARK ALL PRESENT
                </button>
                <button type="button" @click="markAll('absent')" class="inline-flex items-center px-6 py-2.5 bg-rose-50 text-rose-700 text-xs font-black rounded-xl hover:bg-rose-100 transition-all gap-2 group tracking-widest">
                    <svg class="w-4 h-4 transition-transform group-active:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    MARK ALL ABSENT
                </button>
                <button type="button" @click="markAll('late')" class="inline-flex items-center px-6 py-2.5 bg-amber-50 text-amber-700 text-xs font-black rounded-xl hover:bg-amber-100 transition-all gap-2 group tracking-widest">
                    <svg class="w-4 h-4 transition-transform group-active:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    MARK ALL LATE
                </button>
                <div class="w-px h-8 bg-slate-100 mx-2"></div>
                <button type="button" @click="resetAll()" class="inline-flex items-center px-6 py-2.5 bg-slate-50 text-slate-400 text-xs font-black rounded-xl hover:bg-slate-100 transition-all gap-2 tracking-widest outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    RESET ALL
                </button>
            </div>
        </div>

        <!-- Main Entry Table -->
        <form id="attendance-form" action="{{ route('admin.attendance.store') }}" method="POST" @submit="hasChanges = false">
            @csrf
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="p-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-20">Seq</th>
                            <th class="p-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Student Identity</th>
                            <th class="p-2 py-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-emerald-500/5 w-24">P</th>
                            <th class="p-2 py-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-rose-500/5 w-24">A</th>
                            <th class="p-2 py-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-amber-500/5 w-24">L</th>
                            <th class="p-2 py-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-blue-500/5 w-24">E</th>
                            <th class="p-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Clinical Obs / Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $index => $student)
                            @php
                                $current = $existingAttendance[$student->id] ?? null;
                                $currentStatus = $current ? $current->status : 'present';
                            @endphp
                            <tr class="group transition-all duration-300 relative hover:bg-slate-50/50"
                                :class="getRowClass({{ $student->id }})"
                                @click="focusRow({{ $index }})"
                                data-row="{{ $index }}"
                                :data-focused="focusedRow === {{ $index }}">
                                <td class="p-6 whitespace-nowrap text-sm font-medium text-slate-400">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="p-6 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            @if($student->photo)
                                                <img class="h-11 w-11 rounded-2xl object-cover ring-2 ring-white shadow-sm" src="{{ Storage::url($student->photo) }}" alt="">
                                            @else
                                                <div class="h-11 w-11 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm ring-2 ring-white shadow-sm">
                                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white bg-emerald-500"></div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $student->full_name }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $student->student_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <!-- Present Toggle -->
                                <td class="px-2 py-5 text-center border-r border-slate-50/50">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="radio" name="attendance[{{ $student->id }}]" value="present" 
                                               {{ $currentStatus == 'present' ? 'checked' : '' }}
                                               x-init="if ('{{ $currentStatus }}' === 'present') attendance[{{ $student->id }}] = 'present'"
                                               @change="setStatus({{ $student->id }}, 'present')"
                                               class="sr-only peer">
                                        <div class="w-10 h-10 rounded-full border-2 border-slate-100 flex items-center justify-center transition-all peer-checked:bg-emerald-500 peer-checked:border-emerald-500 peer-checked:shadow-lg peer-checked:shadow-emerald-200 peer-checked:text-white text-slate-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </label>
                                </td>
                                <!-- Absent Toggle -->
                                <td class="px-2 py-5 text-center border-r border-slate-50/50">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="radio" name="attendance[{{ $student->id }}]" value="absent" 
                                               {{ $currentStatus == 'absent' ? 'checked' : '' }}
                                               x-init="if ('{{ $currentStatus }}' === 'absent') attendance[{{ $student->id }}] = 'absent'"
                                               @change="setStatus({{ $student->id }}, 'absent')"
                                               class="sr-only peer">
                                        <div class="w-10 h-10 rounded-full border-2 border-slate-100 flex items-center justify-center transition-all peer-checked:bg-rose-500 peer-checked:border-rose-500 peer-checked:shadow-lg peer-checked:shadow-rose-200 peer-checked:text-white text-slate-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </div>
                                    </label>
                                </td>
                                <!-- Late Toggle -->
                                <td class="px-2 py-5 text-center border-r border-slate-50/50">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="radio" name="attendance[{{ $student->id }}]" value="late" 
                                               {{ $currentStatus == 'late' ? 'checked' : '' }}
                                               x-init="if ('{{ $currentStatus }}' === 'late') attendance[{{ $student->id }}] = 'late'"
                                               @change="setStatus({{ $student->id }}, 'late')"
                                               class="sr-only peer">
                                        <div class="w-10 h-10 rounded-full border-2 border-slate-100 flex items-center justify-center transition-all peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:shadow-lg peer-checked:shadow-amber-200 peer-checked:text-white text-slate-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                    </label>
                                </td>
                                <!-- Excused Toggle -->
                                <td class="px-2 py-5 text-center border-r border-slate-50/50">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="radio" name="attendance[{{ $student->id }}]" value="excused" 
                                               {{ $currentStatus == 'excused' ? 'checked' : '' }}
                                               x-init="if ('{{ $currentStatus }}' === 'excused') attendance[{{ $student->id }}] = 'excused'"
                                               @change="setStatus({{ $student->id }}, 'excused')"
                                               class="sr-only peer">
                                        <div class="w-10 h-10 rounded-full border-2 border-slate-100 flex items-center justify-center transition-all peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-checked:shadow-lg peer-checked:shadow-blue-200 peer-checked:text-white text-slate-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                    </label>
                                </td>
                                <td class="p-6">
                                    <input type="text" name="remarks[{{ $student->id }}]" value="{{ $current ? $current->remarks : '' }}"
                                           placeholder="Notes..." @input="hasChanges = true"
                                           class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-xs font-semibold placeholder:text-slate-300">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Keyboard Intelligence (Matched with Student Management Style) -->
            <div class="p-6 bg-slate-900 rounded-[2.5rem] border border-slate-800 shadow-2xl mt-8">
                <div class="flex flex-wrap items-center gap-6">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-2">Smart Keys</span>
                    <div class="flex flex-wrap gap-4">
                        <span class="flex items-center gap-2 group cursor-help text-white/50">
                            <kbd class="px-2.5 py-1 bg-slate-800 rounded-lg border border-slate-700 text-slate-300 text-xs font-black">↑</kbd>
                            <kbd class="px-2.5 py-1 bg-slate-800 rounded-lg border border-slate-700 text-slate-300 text-xs font-black">↓</kbd>
                            <span class="text-[10px] font-bold tracking-widest uppercase">Navigate</span>
                        </span>
                        <span class="flex items-center gap-2 group cursor-help text-emerald-400">
                            <kbd class="px-2.5 py-1 bg-emerald-900/50 rounded-lg border border-emerald-500/30 text-[11px] font-black">P</kbd>
                            <span class="text-[10px] font-bold tracking-widest uppercase">Present</span>
                        </span>
                        <span class="flex items-center gap-2 group cursor-help text-rose-400">
                            <kbd class="px-2.5 py-1 bg-rose-900/50 rounded-lg border border-rose-500/30 text-[11px] font-black">A</kbd>
                            <span class="text-[10px] font-bold tracking-widest uppercase">Absent</span>
                        </span>
                        <span class="flex items-center gap-2 group cursor-help text-amber-400">
                            <kbd class="px-2.5 py-1 bg-amber-900/50 rounded-lg border border-amber-500/30 text-[11px] font-black">L</kbd>
                            <span class="text-[10px] font-bold tracking-widest uppercase">Late</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Finalize Submission Footer -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 pt-10 mt-10 border-t border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="flex -space-x-3 overflow-hidden p-1">
                        @foreach($students->take(5) as $s)
                            <div class="inline-block h-10 w-10 rounded-xl ring-2 ring-white shadow-sm flex items-center justify-center text-[10px] font-black text-white {{ ['bg-indigo-500','bg-emerald-500','bg-amber-500','bg-rose-500','bg-blue-500'][$loop->index] }}">
                                {{ substr($s->first_name, 0, 1) }}
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Dataset Capacity</p>
                        <p class="text-xl font-bold text-slate-800 tracking-tight">{{ $students->count() }} Entries</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <a href="{{ route('admin.attendance.index') }}" class="flex-1 md:flex-none text-center bg-slate-100 hover:bg-slate-200 text-slate-500 font-bold py-3.5 px-8 rounded-2xl transition-all text-xs tracking-widest outline-none">
                        ABORT
                    </a>
                    <button type="submit" class="flex-1 md:flex-none py-3.5 px-12 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-100 transition-all flex items-center justify-center gap-2 text-xs tracking-widest outline-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        SAVE ATTENDANCE
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function attendanceManager() {
            return {
                attendance: {},
                hasChanges: false,
                focusedRow: -1,
                totalStudents: {{ $students->count() }},
                studentIds: @json($students->pluck('id')),
                
                init() {
                    this.studentIds.forEach(id => {
                        if (!this.attendance[id]) {
                            this.attendance[id] = 'present';
                        }
                    });
                    this.updateStats();
                    document.addEventListener('keydown', (e) => this.handleKeyboard(e));
                    window.addEventListener('beforeunload', (e) => {
                        if (this.hasChanges) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });
                },
                
                get stats() {
                    let present = 0, absent = 0, late = 0, excused = 0;
                    Object.values(this.attendance).forEach(status => {
                        if (status === 'present') present++;
                        else if (status === 'absent') absent++;
                        else if (status === 'late') late++;
                        else if (status === 'excused') excused++;
                    });
                    return { present, absent, late, excused };
                },
                
                updateStats() {
                    this.attendance = {...this.attendance};
                },
                
                setStatus(studentId, status) {
                    this.attendance[studentId] = status;
                    this.hasChanges = true;
                    this.updateStats();
                },
                
                markAll(status) {
                    const statusText = status.charAt(0).toUpperCase() + status.slice(1);
                    window.confirmUI({
                        message: `Mark all ${this.totalStudents} students as ${statusText}?`,
                        type: 'info',
                        title: 'Bulk Action',
                        buttonText: 'Confirm',
                        callback: () => {
                            this.studentIds.forEach(id => {
                                this.attendance[id] = status;
                                const radio = document.querySelector(`input[name="attendance[${id}]"][value="${status}"]`);
                                if (radio) radio.checked = true;
                            });
                            this.hasChanges = true;
                            this.updateStats();
                        }
                    });
                },
                
                resetAll() {
                    window.confirmUI({
                        message: 'Reset all attendance to Present?',
                        type: 'info',
                        title: 'Reset Attendance',
                        buttonText: 'Reset',
                        callback: () => {
                            this.studentIds.forEach(id => {
                                this.attendance[id] = 'present';
                                const radio = document.querySelector(`input[name="attendance[${id}]"][value="present"]`);
                                if (radio) radio.checked = true;
                            });
                            this.hasChanges = true;
                            this.updateStats();
                        }
                    });
                },
                
                getRowClass(studentId) {
                    return this.focusedRow === this.studentIds.indexOf(studentId) ? 'bg-slate-50/80 ring-1 ring-slate-200' : '';
                },
                
                focusRow(index) {
                    this.focusedRow = index;
                },
                
                handleKeyboard(e) {
                    if (e.target.tagName === 'INPUT' && e.target.type !== 'radio') return;
                    const key = e.key.toLowerCase();
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.focusedRow = Math.min(this.focusedRow + 1, this.totalStudents - 1);
                        this.scrollToRow(this.focusedRow);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.focusedRow = Math.max(this.focusedRow - 1, 0);
                        this.scrollToRow(this.focusedRow);
                    }
                    if (this.focusedRow >= 0) {
                        const studentId = this.studentIds[this.focusedRow];
                        if (key === 'p') {
                            this.setStatus(studentId, 'present');
                            document.querySelector(`input[name="attendance[${studentId}]"][value="present"]`).checked = true;
                        } else if (key === 'a') {
                            this.setStatus(studentId, 'absent');
                            document.querySelector(`input[name="attendance[${studentId}]"][value="absent"]`).checked = true;
                        } else if (key === 'l') {
                            this.setStatus(studentId, 'late');
                            document.querySelector(`input[name="attendance[${studentId}]"][value="late"]`).checked = true;
                        } else if (key === 'e') {
                            this.setStatus(studentId, 'excused');
                            document.querySelector(`input[name="attendance[${studentId}]"][value="excused"]`).checked = true;
                        }
                    }
                },

                scrollToRow(index) {
                    const row = document.querySelector(`tr[data-row="${index}"]`);
                    if (row) row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
    </script>
    @endpush
</x-admin-layout>
