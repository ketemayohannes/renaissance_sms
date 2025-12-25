<x-admin-layout>
    <div class="space-y-8" x-data="attendanceManager()">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
                    ['label' => 'Attendance Hub', 'url' => route('admin.attendance.index')],
                    ['label' => 'Mark Attendance', 'url' => '#']
        ]" />
        
        <div class="premium-card p-10 relative overflow-hidden">
                <!-- Background Accent -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-50/30 rounded-bl-[20rem] -z-0"></div>

                <div class="relative z-10">
                    <!-- Command Center Heading -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                                <span class="w-2.5 h-10 bg-blue-600 rounded-full mr-4 shadow-xl shadow-blue-200"></span>
                                Attendance Terminal
                            </h3>
                            <p class="text-slate-400 text-sm mt-1 font-medium italic ml-6">Finalizing roster for {{ $section->gradeLevel->name }}{{ $section->name }}</p>
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

                    <!-- Real-time Stats Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-12">
                        <div class="glass-card p-6 text-center group transition-all hover:border-emerald-200 cursor-default bg-white/40">
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Present</p>
                            <p class="text-4xl font-black text-emerald-600 tracking-tighter" x-text="stats.present">0</p>
                            <div class="mt-4 w-full h-1.5 bg-slate-100/50 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.4)] transition-all duration-700" :style="'width: ' + (stats.present / {{ $students->count() }} * 100) + '%'"></div>
                            </div>
                        </div>
                        <div class="glass-card p-6 text-center group transition-all hover:border-rose-200 cursor-default bg-white/40">
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Absent</p>
                            <p class="text-4xl font-black text-rose-500 tracking-tighter" x-text="stats.absent">0</p>
                            <div class="mt-4 w-full h-1.5 bg-slate-100/50 rounded-full overflow-hidden">
                                <div class="h-full bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.4)] transition-all duration-700" :style="'width: ' + (stats.absent / {{ $students->count() }} * 100) + '%'"></div>
                            </div>
                        </div>
                        <div class="glass-card p-6 text-center group transition-all hover:border-amber-200 cursor-default bg-white/40">
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Late</p>
                            <p class="text-4xl font-black text-amber-500 tracking-tighter" x-text="stats.late">0</p>
                            <div class="mt-4 w-full h-1.5 bg-slate-100/50 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.4)] transition-all duration-700" :style="'width: ' + (stats.late / {{ $students->count() }} * 100) + '%'"></div>
                            </div>
                        </div>
                        <div class="glass-card p-6 text-center group transition-all hover:border-blue-200 cursor-default bg-white/40">
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Excused</p>
                            <p class="text-4xl font-black text-blue-500 tracking-tighter" x-text="stats.excused">0</p>
                            <div class="mt-4 w-full h-1.5 bg-slate-100/50 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.4)] transition-all duration-700" :style="'width: ' + (stats.excused / {{ $students->count() }} * 100) + '%'"></div>
                            </div>
                        </div>
                        <div class="premium-card p-6 text-center bg-slate-800 border-none group shadow-2xl shadow-slate-900/20">
                            <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-2">Active Net</p>
                            <p class="text-4xl font-black text-white tracking-tighter">{{ $students->count() }}</p>
                            <div class="mt-4 text-[10px] font-bold text-slate-600 italic tracking-tight">System Total</div>
                        </div>
                    </div>

                    <!-- Hub Quick Actions -->
                    <div class="flex flex-wrap items-center gap-4 mb-10 bg-slate-50/50 backdrop-blur-sm p-4 rounded-[2rem] border border-slate-100 shadow-inner">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4 mr-2">Batch Actions:</span>
                        <div class="flex gap-2">
                            <button type="button" @click="markAll('present')" class="vibrant-btn-emerald px-6 py-3 rounded-2xl text-[11px] flex items-center gap-2 group outline-none">
                                <svg class="w-3.5 h-3.5 transition-transform group-active:scale-150" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <span class="tracking-widest">MARK ALL PRESENT</span>
                            </button>
                            <button type="button" @click="markAll('absent')" class="vibrant-btn-rose px-6 py-3 rounded-2xl text-[11px] flex items-center gap-2 group outline-none">
                                <svg class="w-3.5 h-3.5 transition-transform group-active:scale-150" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="tracking-widest">MARK ALL ABSENT</span>
                            </button>
                            <button type="button" @click="markAll('late')" class="vibrant-btn-amber px-6 py-3 rounded-2xl text-[11px] flex items-center gap-2 group outline-none">
                                <svg class="w-3.5 h-3.5 transition-transform group-active:scale-150" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="tracking-widest">MARK ALL LATE</span>
                            </button>
                        </div>
                        <div class="w-px h-8 bg-slate-200 mx-2"></div>
                        <button type="button" @click="resetAll()" class="premium-card bg-white hover:bg-slate-100 text-slate-400 px-6 py-3 rounded-2xl font-black text-[11px] transition-all flex items-center gap-2 active:scale-95 shadow-sm border-slate-200 outline-none">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span class="tracking-widest">RESET ALL</span>
                        </button>
                    </div>

                    <!-- Main Entry Table -->
                    <form id="attendance-form" action="{{ route('admin.attendance.store') }}" method="POST" @submit="hasChanges = false">
                        @csrf
                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                        <input type="hidden" name="date" value="{{ $date }}">

                        <div class="overflow-hidden rounded-[2.5rem] border border-slate-100 shadow-xl bg-white/40 mb-10">
                            <table class="min-w-full divide-y divide-slate-100/50">
                                <thead>
                                    <tr class="bg-slate-900 border-b border-slate-800">
                                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-20">Seq</th>
                                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Student Identity</th>
                                        <th class="px-2 py-6 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] bg-emerald-600/10 w-24">P</th>
                                        <th class="px-2 py-6 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] bg-rose-600/10 w-24">A</th>
                                        <th class="px-2 py-6 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] bg-amber-600/10 w-24">L</th>
                                        <th class="px-2 py-6 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] bg-blue-600/10 w-24">E</th>
                                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Clinical Obs / Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100/50">
                                    @foreach($students as $index => $student)
                                        @php
                                            $current = $existingAttendance[$student->id] ?? null;
                                            $currentStatus = $current ? $current->status : 'present';
                                        @endphp
                                        <tr class="group transition-all duration-300 relative hover:bg-white"
                                            :class="getRowClass({{ $student->id }})"
                                            @click="focusRow({{ $index }})"
                                            data-row="{{ $index }}"
                                            :data-focused="focusedRow === {{ $index }}">
                                            <td class="px-8 py-5 whitespace-nowrap text-[11px] font-black text-slate-400 italic">
                                                #{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td class="px-8 py-5 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-black text-slate-800 tracking-tight leading-none group-hover:text-blue-600 transition-colors">{{ $student->full_name }}</span>
                                                    <span class="text-[9px] text-slate-400 font-bold uppercase mt-1 tracking-wider">{{ $student->student_id }}</span>
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
                                            <td class="px-8 py-5">
                                                <input type="text" name="remarks[{{ $student->id }}]" value="{{ $current ? $current->remarks : '' }}"
                                                       placeholder="Enter crucial observations..." @input="hasChanges = true"
                                                       class="bg-slate-50/50 border-slate-100 rounded-2xl shadow-inner focus:ring-blue-500 focus:border-blue-500 text-[10px] font-black w-full placeholder:text-slate-300 py-3 px-5 uppercase tracking-widest transition-all focus:bg-white focus:shadow-lg">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Keyboard Intelligence -->
                        <div class="flex flex-wrap items-center gap-6 bg-slate-900 border border-slate-800 p-6 rounded-[2rem] shadow-2xl mb-12">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-2">Smart Keys:</span>
                            <div class="flex gap-4">
                                <span class="flex items-center gap-2 group cursor-help"><kbd class="px-3 py-1 bg-slate-800 rounded-lg border border-slate-700 shadow-sm text-slate-300 text-[12px] font-black transition-transform group-hover:-translate-y-1">↑</kbd><kbd class="px-3 py-1 bg-slate-800 rounded-lg border border-slate-700 shadow-sm text-slate-300 text-[12px] font-black group-hover:translate-y-1">↓</kbd> <span class="text-[10px] font-bold text-slate-500 tracking-widest">NAVIGATE</span></span>
                                <span class="flex items-center gap-2 group cursor-help"><kbd class="px-3 py-1 bg-emerald-900/50 rounded-lg border border-emerald-500/30 shadow-sm text-emerald-400 text-[12px] font-black group-hover:scale-110">P</kbd> <span class="text-[10px] font-bold text-slate-500 tracking-widest">PRESENT</span></span>
                                <span class="flex items-center gap-2 group cursor-help"><kbd class="px-3 py-1 bg-rose-900/50 rounded-lg border border-rose-500/30 shadow-sm text-rose-400 text-[12px] font-black group-hover:scale-110">A</kbd> <span class="text-[10px] font-bold text-slate-500 tracking-widest">ABSENT</span></span>
                                <span class="flex items-center gap-2 group cursor-help"><kbd class="px-3 py-1 bg-amber-900/50 rounded-lg border border-amber-500/30 shadow-sm text-amber-400 text-[12px] font-black group-hover:scale-110">L</kbd> <span class="text-[10px] font-bold text-slate-500 tracking-widest">LATE</span></span>
                                <span class="flex items-center gap-2 group cursor-help"><kbd class="px-3 py-1 bg-blue-900/50 rounded-lg border border-blue-500/30 shadow-sm text-blue-400 text-[12px] font-black group-hover:scale-110">E</kbd> <span class="text-[10px] font-bold text-slate-500 tracking-widest">EXCUSED</span></span>
                            </div>
                        </div>

                        <!-- Finalize Submission Footer -->
                        <div class="flex flex-col md:flex-row justify-between items-center gap-8 pt-10 border-t border-slate-100">
                            <div class="flex items-center gap-6 bg-slate-50/50 p-3 pr-8 rounded-3xl border border-slate-100">
                                 <div class="flex -space-x-3 overflow-hidden p-1">
                                    @foreach($students->take(5) as $s)
                                        <div class="inline-block h-12 w-12 rounded-[1.25rem] ring-4 ring-white shadow-xl vibrant-gradient-{{ ['blue','emerald','amber','rose','indigo'][$loop->index] }} flex items-center justify-center text-white text-xs font-black ring-offset-2 ring-offset-slate-50 transition-transform hover:-translate-y-1 hover:z-20 scale-90">
                                            {{ substr($s->first_name, 0, 1) }}
                                        </div>
                                    @endforeach
                                 </div>
                                 <div class="space-y-0.5">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Dataset Integrity</p>
                                    <p class="text-xl font-black text-slate-800 tracking-tight">{{ $students->count() }} Managed Entities</p>
                                 </div>
                            </div>
                            <div class="flex items-center gap-4 w-full md:w-auto">
                                <a href="{{ route('admin.attendance.index') }}" class="flex-1 md:flex-none text-center bg-slate-100 hover:bg-slate-200 text-slate-400 font-black py-5 px-10 rounded-[1.5rem] transition-all active:scale-95 uppercase text-xs tracking-[0.2em] leading-none outline-none">
                                    ABORT
                                </a>
                                <button type="submit" class="vibrant-btn-blue flex-1 md:flex-none py-5 px-16 rounded-[1.5rem] shadow-[0_15px_30px_rgba(59,130,246,0.3)] flex items-center justify-center gap-4 text-xs tracking-[0.2em] leading-none animate-float outline-none">
                                    <svg class="w-5 h-5 shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    SYNC TO CLOUD
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
        </div>
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
                    // Initialize attendance state from existing data
                    this.studentIds.forEach(id => {
                        if (!this.attendance[id]) {
                            this.attendance[id] = 'present';
                        }
                    });
                    this.updateStats();
                    
                    // Setup keyboard navigation
                    document.addEventListener('keydown', (e) => this.handleKeyboard(e));
                    
                    // Setup beforeunload warning
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
                
                get attendanceRate() {
                    const total = this.totalStudents;
                    if (total === 0) return 100;
                    const presentAndLate = this.stats.present + this.stats.late;
                    return (presentAndLate / total) * 100;
                },
                
                updateStats() {
                    // Force reactivity update
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
                        message: `Are you sure you want to mark ALL students as ${statusText}?`,
                        type: 'warning',
                        title: 'Bulk Mark Attendance',
                        buttonText: 'Mark All',
                        callback: () => {
                            this.studentIds.forEach(id => {
                                this.attendance[id] = status;
                                document.querySelector(`input[name="attendance[${id}]"][value="${status}"]`).checked = true;
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
                                document.querySelector(`input[name="attendance[${id}]"][value="present"]`).checked = true;
                            });
                            this.hasChanges = true;
                            this.updateStats();
                        }
                    });
                },
                
                getRowClass(studentId) {
                    const status = this.attendance[studentId] || 'present';
                    const focusClass = this.focusedRow === this.studentIds.indexOf(studentId) ? 'ring-2 ring-blue-400 ring-inset' : '';
                    
                    // Transparent rows to let background/glass show through
                    return focusClass;
                },
                
                focusRow(index) {
                    this.focusedRow = index;
                },
                
                handleKeyboard(e) {
                    // Only handle if not in an input field
                    if (e.target.tagName === 'INPUT' && e.target.type !== 'radio') return;
                    
                    const key = e.key.toLowerCase();
                    
                    // Arrow navigation
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.focusedRow = Math.min(this.focusedRow + 1, this.totalStudents - 1);
                        this.scrollToRow(this.focusedRow);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.focusedRow = Math.max(this.focusedRow - 1, 0);
                        this.scrollToRow(this.focusedRow);
                    }
                    
                    // Quick status keys
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
                    if (row) {
                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }
        }
    </script>
    @endpush
</x-admin-layout>
