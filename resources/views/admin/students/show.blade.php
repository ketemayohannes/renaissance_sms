<x-admin-layout>
    <x-slot name="header">Student Profile: {{ $student->full_name }}</x-slot>

    <div class="space-y-6" x-data="{ 
        tab: 'overview', 
        searchQuery: '',
        searchResults: [],
        loading: false,
        async searchStudents() {
            const query = this.searchQuery.trim();
            if (query.length < 2) {
                this.searchResults = [];
                return;
            }
            this.loading = true;
            try {
                const resp = await fetch(`/admin/search?q=${encodeURIComponent(query)}`);
                const data = await resp.json();
                this.searchResults = data.filter(r => r.type === 'Student' && r.id != '{{ $student->id }}');
            } catch (e) {
                console.error('Search error:', e);
            } finally {
                this.loading = false;
            }
        }
    }" @open-sibling-modal.window="$store.ui.openModal('sibling')">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Students', 'url' => route('admin.students.index')],
                ['label' => $student->full_name, 'url' => '#']
            ]" />
            
    <!-- Profile Header Section -->
    <div class="relative mb-12 z-[50]">
        <!-- Background Banner Decorative Element -->
        <div class="absolute inset-0 h-48 bg-gradient-to-r from-indigo-600 to-violet-600 rounded-[3rem] opacity-10 blur-3xl -z-10"></div>
        
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                <!-- Profile Image & Quick Status -->
                <div class="relative group">
                    <div class="w-40 h-40 rounded-[2.5rem] bg-slate-100 border-4 border-white shadow-xl overflow-hidden">
                        @if($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" alt="Student Photo" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 text-slate-300">
                                <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                        @endif
                    </div>
                    @php
                        $latestStatus = $student->latestStatusHistory;
                        $statusLabel  = $student->is_active ? 'Active' : 'Inactive';
                        $statusColor  = $student->is_active ? 'bg-emerald-500 shadow-emerald-200' : 'bg-rose-500 shadow-rose-200';
                        if ($latestStatus) {
                            $map = [
                                'active'      => ['label' => 'Active',      'color' => 'bg-emerald-500 shadow-emerald-200'],
                                'inactive'    => ['label' => 'Inactive',    'color' => 'bg-slate-500 shadow-slate-200'],
                                'graduated'   => ['label' => 'Graduated',   'color' => 'bg-indigo-500 shadow-indigo-200'],
                                'withdrawn'   => ['label' => 'Withdrawn',   'color' => 'bg-amber-500 shadow-amber-200'],
                                'transferred' => ['label' => 'Transferred', 'color' => 'bg-sky-500 shadow-sky-200'],
                                'dropped_out' => ['label' => 'Dropped Out', 'color' => 'bg-rose-500 shadow-rose-200'],
                            ];
                            $entry = $map[$latestStatus->new_status] ?? null;
                            if ($entry) {
                                $statusLabel = $entry['label'];
                                $statusColor = $entry['color'];
                            }
                        }
                    @endphp
                    <div class="absolute -bottom-3 -right-3 px-4 py-1.5 rounded-2xl {{ $statusColor }} text-white text-[10px] font-black uppercase tracking-widest shadow-lg">
                        {{ $statusLabel }}
                    </div>
                </div>

                <!-- Info and Actions -->
                <div class="flex-grow">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-[60]">
                        <div>
                            <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">{{ $student->full_name }}</h1>
                            <div class="flex flex-wrap items-center gap-4 text-slate-500">
                                <span class="bg-slate-100 px-3 py-1 rounded-lg font-black text-slate-700 text-xs tracking-wider uppercase">{{ $student->student_id }}</span>
                                <span class="flex items-center gap-1.5 font-semibold text-sm">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Joined {{ $student->admission_date->format('M d, Y') }}
                                </span>
                                <span class="flex items-center gap-1.5 font-semibold text-sm">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    {{ $student->currentEnrollment ? $student->currentEnrollment->section->gradeLevel->name : 'No Enrollment' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @if(!auth()->user()?->hasRole('Vice Principal') && !auth()->user()?->hasRole('Supervisor'))
                            <a href="{{ route('admin.students.edit', $student) }}" class="px-6 py-3 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-white hover:text-indigo-600 hover:ring-2 hover:ring-indigo-600 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit Profile
                            </a>
                            @endif
                            <div class="relative z-50" x-data="{ open: false }">
                                <button @click.stop="open = !open" class="px-6 py-3 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all border border-slate-200 shadow-sm flex items-center gap-2">
                                    Quick Actions
                                    <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-cloak x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-3 w-64 bg-white border border-slate-100 rounded-[2rem] shadow-2xl p-3 z-[100]">
                                    <a href="{{ route('admin.students.id-card', $student) }}" target="_blank" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition-all font-semibold text-xs group/item">
                                        <svg class="w-4 h-4 text-indigo-500 group-hover/item:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        Print ID Card
                                    </a>
                                    <button @click.stop="$store.ui.openModal('reportCard'); open = false" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition-all font-semibold text-xs group/item text-left">
                                        <svg class="w-4 h-4 text-emerald-500 group-hover/item:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Report Card PDF
                                    </button>
                                    <a href="{{ route('admin.students.assign-electives', $student) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition-all font-semibold text-xs group/item">
                                        <svg class="w-4 h-4 text-violet-500 group-hover/item:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.247 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        Assign Electives
                                    </a>

                                    <div class="my-2 border-t border-slate-100/50 mx-2"></div>
                                    
                                    <!-- Behavior & Admin -->
                                    <div class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Behavior & Admin</div>
                                    <a href="{{ route('admin.disciplinary.create', $student) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition-all font-semibold text-xs group/item">
                                        <svg class="w-4 h-4 text-rose-500 group-hover/item:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Record Incident
                                    </a>
                                    <a href="{{ route('admin.students.transfer', $student) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition-all font-semibold text-xs group/item">
                                        <svg class="w-4 h-4 text-amber-500 group-hover/item:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                        Transfer Student
                                    </a>
                                    <a href="{{ route('admin.students.withdraw', $student) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-rose-600 transition-all font-semibold text-xs group/item">
                                        <svg class="w-4 h-4 text-rose-400 group-hover/item:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                        Withdraw Student
                                    </a>
                                    <a href="{{ route('admin.students.status-history', $student) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition-all font-semibold text-xs group/item">
                                        <svg class="w-4 h-4 text-slate-400 group-hover/item:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Status History
                                    </a>
                                    <button type="button" @click.stop="$store.ui.openModal('sibling'); open = false" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition-all font-semibold text-xs text-left group/item">
                                        <svg class="w-4 h-4 text-indigo-400 group-hover/item:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        Link Siblings
                                    </button>

                                    <div class="my-2 border-t border-slate-100/50 mx-2"></div>

                                    <!-- Status & Account -->
                                    <div class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Status & Account</div>

                                    <form action="{{ route('admin.students.toggle-block', $student) }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 {{ $student->is_active ? 'text-rose-600' : 'text-emerald-600' }} transition-all font-semibold text-xs">
                                            @if($student->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                Deactivate Student
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Activate Student
                                            @endif
                                        </button>
                                    </form>

                                    @if(!$student->user_id)
                                        <form action="{{ route('admin.students.create-user', $student) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-indigo-600 transition-all font-semibold text-xs">
                                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                                Create Login Account
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.students.reset-password', $student) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition-all font-semibold text-xs confirm-form" data-confirm-title="Reset Password" data-confirm-message="Are you sure you want to reset this student's password?">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                                Reset Password
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-8">
                        <div class="bg-slate-50/50 p-4 rounded-[1.5rem] border border-slate-100 shadow-sm">
                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Gender</span>
                            <span class="font-bold text-slate-900">{{ $student->gender == 'M' ? 'Male' : 'Female' }}</span>
                        </div>
                        <div class="bg-indigo-50/30 p-4 rounded-[1.5rem] border border-indigo-100/30 shadow-sm">
                            <span class="text-[10px] text-indigo-400 font-black uppercase tracking-widest block mb-1">Grade Level</span>
                            <span class="font-bold text-slate-900">{{ $student->currentEnrollment ? $student->currentEnrollment->section->gradeLevel->name : 'N/A' }}</span>
                        </div>
                        <div class="bg-amber-50/30 p-4 rounded-[1.5rem] border border-amber-100/30 shadow-sm">
                            <span class="text-[10px] text-amber-400 font-black uppercase tracking-widest block mb-1">Current Section</span>
                            <span class="font-bold text-slate-900">{{ $student->currentEnrollment ? $student->currentEnrollment->section->name : 'N/A' }}</span>
                        </div>
                        <div class="bg-emerald-50/30 p-4 rounded-[1.5rem] border border-emerald-100/30 shadow-sm">
                            <span class="text-[10px] text-emerald-400 font-black uppercase tracking-widest block mb-1">Birth Date</span>
                            <span class="font-bold text-slate-900">{{ $student->date_of_birth->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="space-y-8">
        <!-- Modern Tabs Navigation -->
        <div class="sticky top-4 z-40 bg-white/60 backdrop-blur-xl border border-white rounded-[2rem] shadow-xl shadow-slate-200/50 p-2 overflow-x-auto no-scrollbar">
            <nav class="flex gap-1 min-w-max">
                <button @click="tab = 'overview'" 
                        :class="tab === 'overview' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Overview
                </button>
                <button @click="tab = 'guardians'" 
                        :class="tab === 'guardians' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Guardians
                </button>
                <button @click="tab = 'academic'" 
                        :class="tab === 'academic' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Academic
                </button>
                <button @click="tab = 'medical_transport'" 
                        :class="tab === 'medical_transport' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Health & Bus
                </button>
                <button @click="tab = 'attendance'" 
                        :class="tab === 'attendance' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Attendance
                </button>
                <button @click="tab = 'disciplinary'" 
                        :class="tab === 'disciplinary' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Conduct
                </button>
                <button @click="tab = 'enrollment'" 
                        :class="tab === 'enrollment' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Enrollment
                </button>
                <button @click="tab = 'documents'" 
                        :class="tab === 'documents' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Documents
                    <span class="py-0.5 px-2 rounded-lg text-[8px] font-black" :class="tab === 'documents' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'">{{ $student->documents->count() }}</span>
                </button>
            </nav>
        </div>

                    <!-- OVERVIEW TAB -->
                    <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Personal Info Card -->
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900">Personal Details</h3>
                                        <p class="text-slate-500 text-sm">Identity and background information.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                                    <div class="sm:col-span-2">
                                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Full Name</span>
                                        <span class="font-bold text-slate-700 text-lg">{{ $student->full_name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Date of Birth</span>
                                        <span class="font-bold text-slate-700">{{ $student->date_of_birth->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Age</span>
                                        <span class="font-bold text-slate-700">{{ $student->date_of_birth->age }} Years Old</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Birth Place</span>
                                        <span class="font-bold text-slate-700">{{ $student->birth_city }}, {{ $student->birth_country }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Nationality</span>
                                        <span class="font-bold text-slate-700">{{ $student->nationality }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Preferred Language</span>
                                        <span class="font-bold text-slate-700">{{ $student->language_spoken }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact & Account Card -->
                            <div class="space-y-8">
                                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                                    <div class="flex items-center gap-4 mb-8">
                                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm border border-amber-100/50">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900">Address & Contact</h3>
                                            <p class="text-slate-500 text-sm">Where the student resides.</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                                        <div>
                                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Subcity / Woreda</span>
                                            <span class="font-bold text-slate-700">{{ $student->subcity ?? '-' }} / {{ $student->woreda ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">House Number</span>
                                            <span class="font-bold text-slate-700">{{ $student->house_number ?? '-' }}</span>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Full Primary Address</span>
                                            <span class="font-bold text-slate-700">{{ $student->full_address }}</span>
                                        </div>
                                        <div class="sm:col-span-2 pt-4 border-t border-slate-50">
                                            <span class="text-[10px] text-indigo-400 font-black uppercase tracking-widest block mb-1">Emergency Phone</span>
                                            <span class="font-black text-indigo-600 text-lg">{{ $student->phone ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-indigo-600 rounded-[2.5rem] shadow-xl shadow-indigo-100 p-8 text-white relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 -m-12 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                                    
                                    <h3 class="text-xl font-bold mb-4 flex items-center gap-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0014 11V4h-3L9 7l-2-3H4v7a10 10 0 005.183 8.761"></path></svg>
                                        Portal Account
                                    </h3>
                                    
                                    @if($student->user_id)
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="flex-grow">
                                                <p class="text-indigo-100 text-xs font-semibold uppercase tracking-widest mb-1">Registered Email</p>
                                                <p class="font-bold text-lg leading-tight break-all">{{ $student->user->email }}</p>
                                            </div>
                                            @if($student->user->temp_password)
                                                <div class="flex-grow">
                                                    <p class="text-indigo-100 text-xs font-semibold uppercase tracking-widest mb-1">Initial Password</p>
                                                    <div class="flex items-center gap-2">
                                                        <p class="font-black text-lg leading-tight tracking-wider">{{ $student->user->temp_password }}</p>
                                                            <button type="button" onclick="copyToClipboard('{{ addslashes($student->user->temp_password) }}', this)" class="p-1 px-2 bg-white/10 hover:bg-white/20 rounded-lg text-[10px] font-black uppercase tracking-tighter border border-white/20 transition-all">Copy</button>
                                                    </div>
                                                </div>
                                            @elseif($student->user->last_login_at)
                                                <div class="flex-grow">
                                                    <p class="text-indigo-100 text-xs font-semibold uppercase tracking-widest mb-1">Last Login</p>
                                                    <p class="font-bold text-xs text-white/60 italic uppercase tracking-widest">{{ $student->user->last_login_at->diffForHumans() }}</p>
                                                </div>
                                            @else
                                                <div class="flex-grow">
                                                    <p class="text-indigo-100 text-xs font-semibold uppercase tracking-widest mb-1">Status</p>
                                                    <p class="font-bold text-xs text-white/60 italic uppercase tracking-widest">Account Active</p>
                                                </div>
                                            @endif
                                            <form action="{{ route('admin.students.reset-password', $student) }}" method="POST" class="confirm-form" data-confirm-message="Reset password for this student?" data-confirm-title="Reset Password" data-confirm-type="warning" data-confirm-button="Reset">
                                                @csrf
                                                <button type="submit" class="p-3 bg-white/20 hover:bg-white/30 rounded-2xl transition-all border border-white/30 group/btn">
                                                    <svg class="w-6 h-6 text-white group-hover/btn:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1111 4.582V7m0 10a5 5 0 01-5-5h10a5 5 0 01-5 5z"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between gap-4">
                                            <p class="text-indigo-100 text-sm font-medium">No parent/student account has been linked to this profile yet.</p>
                                            <form action="{{ route('admin.students.create-user', $student) }}" method="POST" class="confirm-form" data-confirm-message="Create a portal account for this student?" data-confirm-title="Create Account" data-confirm-type="info" data-confirm-button="Create">
                                                @csrf
                                                <button type="submit" class="px-6 py-3 bg-white text-indigo-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:shadow-lg transition-all">
                                                    Enable
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GUARDIANS TAB -->
                    <div x-show="tab === 'guardians'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            @foreach($student->guardians as $guardian)
                                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8 overflow-hidden relative group">
                                    <div class="flex items-start gap-6">
                                        <!-- Photo -->
                                        <div class="relative">
                                            <div class="w-24 h-24 rounded-[1.5rem] bg-slate-100 border-2 border-white shadow-md overflow-hidden">
                                                @if($guardian->photo)
                                                    <img src="{{ asset('storage/' . $guardian->photo) }}" alt="Guardian Photo" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-300">
                                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                    </div>
                                                @endif
                                            </div>
                                            @if($guardian->is_emergency_contact)
                                                <div class="absolute -top-2 -left-2 w-8 h-8 bg-rose-500 rounded-full border-4 border-white shadow-md flex items-center justify-center text-white" title="Emergency Contact">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-grow">
                                            <div class="flex items-center justify-between mb-2">
                                                <a href="{{ route('admin.guardians.show', $guardian) }}" class="group/gname">
                                                    <h4 class="text-xl font-bold text-slate-900 leading-tight group-hover/gname:text-indigo-600 transition-colors">{{ $guardian->full_name }}</h4>
                                                </a>
                                                <span class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest ring-1 ring-indigo-100">{{ $guardian->relationship }}</span>
                                            </div>
                                            <p class="text-slate-500 text-sm font-semibold mb-4">{{ ucfirst($guardian->guardian_type) }}</p>
                                            
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-3 group/info">
                                                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover/info:bg-indigo-50 group-hover/info:text-indigo-500 transition-colors shadow-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                    </div>
                                                    <span class="font-black text-slate-700 text-sm">{{ $guardian->phone }}</span>
                                                </div>
                                                @if($guardian->email)
                                                    <div class="flex items-center gap-3 group/info">
                                                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover/info:bg-rose-50 group-hover/info:text-rose-500 transition-colors shadow-sm">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                        </div>
                                                        <span class="font-semibold text-slate-600 text-sm truncate">{{ $guardian->email }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-8 pt-6 border-t border-slate-50 flex items-center justify-between">
                                        @if($guardian->user_id)
                                            <div class="flex items-center gap-2 text-emerald-500">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                                <span class="text-[10px] font-black uppercase tracking-widest">Portal Access Active</span>
                                            </div>
                                        @else
                                            <form action="{{ route('admin.guardians.create-user', $guardian) }}" method="POST" class="confirm-form" data-confirm-message="Create a portal account for this guardian?" data-confirm-title="Create Account" data-confirm-type="info" data-confirm-button="Create">
                                                @csrf
                                                <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-indigo-500 hover:text-indigo-700 transition-colors flex items-center gap-2">
                                                    Enable Portal Access
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <div class="px-3 py-1 bg-slate-50 rounded-lg border border-slate-100 text-[10px] font-bold text-slate-400">
                                            UID: #G{{ str_pad($guardian->id, 5, '0', STR_PAD_LEFT) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- MEDICAL & TRANSPORT TAB -->
                    <div x-show="tab === 'medical_transport'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Medical Information -->
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-sm border border-rose-100/50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900">Health Records</h3>
                                        <p class="text-slate-500 text-sm">Vital medical information.</p>
                                    </div>
                                </div>

                                @if($student->medicalInfo)
                                    <div class="space-y-6">
                                        <div class="flex items-center justify-between p-4 bg-rose-50/50 rounded-2xl border border-rose-100/30">
                                            <span class="text-[10px] text-rose-400 font-black uppercase tracking-widest">Blood Group</span>
                                            <span class="px-4 py-1 bg-white rounded-lg text-rose-600 font-black text-xl shadow-sm">{{ $student->medicalInfo->blood_group ?? '??' }}</span>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 gap-6">
                                            <div>
                                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Emergency Name</span>
                                                <span class="font-bold text-slate-700">{{ $student->medicalInfo->emergency_contact_name ?? 'N/A' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Emergency Phone</span>
                                                <span class="font-bold text-slate-700">{{ $student->medicalInfo->emergency_contact_phone ?? 'N/A' }}</span>
                                            </div>
                                            <div class="pt-4 border-t border-slate-50">
                                                <span class="text-[10px] text-rose-400 font-black uppercase tracking-widest block mb-1">Allergies</span>
                                                <p class="font-bold text-slate-700">{{ $student->medicalInfo->allergies ?? 'None Reported' }}</p>
                                            </div>
                                            <div>
                                                <span class="text-[10px] text-amber-400 font-black uppercase tracking-widest block mb-1">Medical Conditions</span>
                                                <p class="font-bold text-slate-700">{{ $student->medicalInfo->medical_conditions ?? 'None Reported' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center py-12 text-slate-300">
                                        <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="font-bold text-sm">No Medical Records Foundations</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Transportation Information -->
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm border border-amber-100/50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900">Transport & Bus</h3>
                                        <p class="text-slate-500 text-sm">Daily commute details.</p>
                                    </div>
                                </div>

                                @if($student->has_transport)
                                    <div class="space-y-8">
                                        <!-- Driver Info -->
                                        <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                            <div class="w-16 h-16 rounded-xl border-2 border-white shadow-sm overflow-hidden">
                                                @if($student->driver_photo)
                                                    <img src="{{ asset('storage/' . $student->driver_photo) }}" alt="Driver Photo" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-amber-100 text-amber-500 font-black text-xl">D</div>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 class="font-black text-slate-900 leading-tight">{{ $student->driver_name }}</h4>
                                                <p class="text-[10px] text-amber-500 font-black uppercase tracking-widest">{{ $student->driver_phone }}</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-6">
                                            <div>
                                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Route Name</span>
                                                <span class="font-bold text-slate-700">{{ $student->transport_route ?? 'Unassigned' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Pickup Location</span>
                                                <p class="font-bold text-slate-700">{{ $student->pickup_location ?? 'Default Station' }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 p-4 bg-indigo-50 rounded-2xl border border-indigo-100 group">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    <span class="text-xs font-black text-indigo-600 uppercase tracking-widest">Live Track</span>
                                                </div>
                                                <button class="text-[10px] font-black uppercase tracking-widest text-indigo-400 group-hover:text-indigo-600 transition-colors">Open Maps</button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center py-12 text-slate-300">
                                        <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p class="font-bold text-sm text-center px-8">Not enrolled in official school transportation</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ENROLLMENT & SIBLINGS TAB -->
                    <div x-show="tab === 'enrollment'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Enrollment History -->
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8 overflow-hidden">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center text-violet-600 shadow-sm border border-violet-100/50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900">Academic Journey</h3>
                                        <p class="text-slate-500 text-sm">Enrollment history and logs.</p>
                                    </div>
                                </div>

                                <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-z-10 before:h-full before:w-0.5 before:-translate-x-px before:bg-gradient-to-b before:from-transparent before:via-slate-100 before:to-transparent">
                                    @foreach($student->enrollments->sortByDesc('created_at') as $enrollment)
                                        <div class="relative pl-12 group">
                                            <div class="absolute left-0 top-1 w-10 h-10 rounded-xl bg-white border-2 border-slate-100 flex items-center justify-center text-slate-400 group-hover:border-violet-300 group-hover:text-violet-500 transition-all shadow-sm">
                                                <span class="text-[10px] font-black uppercase tracking-tighter">{{ $loop->iteration }}</span>
                                            </div>
                                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100/50 hover:bg-white hover:shadow-lg transition-all">
                                                <div class="flex items-center justify-between mb-1">
                                                    <h5 class="font-black text-slate-800">{{ $enrollment->section->gradeLevel->name }} - {{ $enrollment->section->name }}</h5>
                                                    <span class="text-[10px] font-bold text-slate-400">{{ $enrollment->academicYear->name }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded-lg {{ $enrollment->status == 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }} text-[9px] font-black uppercase tracking-widest">
                                                        {{ ucfirst($enrollment->status) }}
                                                    </span>
                                                    <span class="text-[10px] font-semibold text-slate-400">Promoted on {{ $enrollment->created_at->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Siblings -->
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100/50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 mb-8">
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900">Family Members</h3>
                                            <p class="text-slate-500 text-sm">Siblings enrolled in the school.</p>
                                        </div>
                                        <button @click="$store.ui.openModal('sibling')" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">Link Sibling</button>
                                    </div>
                                </div>

                                @if($student->siblings->count() > 0)
                                    <div class="space-y-4">
                                        @foreach($student->siblings as $sibling)
                                            <div class="flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl border border-slate-100/50 group hover:bg-white hover:border-indigo-100 hover:shadow-md transition-all">
                                                <a href="{{ route('admin.students.show', $sibling) }}" class="flex items-center gap-4 flex-grow group/sib">
                                                    <div class="w-12 h-12 rounded-xl border-2 border-white shadow-sm overflow-hidden bg-slate-200 group-hover/sib:border-indigo-200 transition-all">
                                                        @if($sibling->photo)
                                                            <img src="{{ asset('storage/' . $sibling->photo) }}" alt="Sibling photo" class="w-full h-full object-cover">
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h4 class="font-black text-slate-800 group-hover/sib:text-indigo-600 transition-colors">{{ $sibling->full_name }}</h4>
                                                        <p class="text-xs text-gray-500">{{ $sibling->student_id }}</p>
                                                    </div>
                                                </a>
                                                <form action="{{ route('admin.students.siblings.unlink', ['student' => $student, 'sibling' => $sibling]) }}" method="POST" class="confirm-form" data-confirm-message="Are you sure you want to unlink these siblings?" data-confirm-title="Unlink Siblings" data-confirm-type="danger" data-confirm-button="Unlink">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Unlink</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center py-12 text-slate-300">
                                        <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <p class="text-sm font-bold">No registered siblings found</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ACADEMIC TAB -->
                    <div x-show="tab === 'academic'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                        @forelse($academicRecords->sortKeysDesc() as $year => $terms)
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden" x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }">
                                <div class="p-8 flex items-center justify-between cursor-pointer hover:bg-slate-50/50 transition-all" @click="expanded = !expanded">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900">{{ $year }} Academic Year</h3>
                                            <p class="text-slate-500 text-sm font-medium">Historical performance and term results.</p>
                                        </div>
                                    </div>
                                    <svg class="w-6 h-6 text-slate-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>

                                <div x-show="expanded" x-collapse x-cloak>
                                    <div class="px-8 pb-8 space-y-6">
                                        @foreach($terms as $termName => $marks)
                                            @php
                                                $termRecord = $termRecords[$year][$termName] ?? null;
                                            @endphp
                                            <div class="bg-slate-50/50 rounded-[2rem] border border-slate-100 overflow-hidden" x-data="{ termOpen: {{ $loop->first ? 'true' : 'false' }} }">
                                                <div class="px-6 py-4 flex items-center justify-between cursor-pointer hover:bg-white transition-all" @click="termOpen = !termOpen">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                                        <h4 class="font-black text-slate-800 uppercase tracking-widest text-xs">{{ $termName }}</h4>
                                                        @if($termRecord)
                                                            <div class="flex items-center gap-3 ml-4">
                                                                @if($termRecord->average_score)
                                                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[10px] font-black">AVG: {{ number_format($termRecord->average_score, 2) }}</span>
                                                                @endif
                                                                @if($termRecord->rank)
                                                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[10px] font-black">RANK: {{ $termRecord->rank }}/{{ $termRecord->rank_out_of }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-4">
                                                        @if($termRecord && $termRecord->term_id)
                                                            <a href="{{ route('admin.report-cards.pdf', ['student' => $student->id, 'term_id' => $termRecord->term_id, 'academic_year_id' => $termRecord->academic_year_id]) }}" 
                                                               target="_blank" @click.stop
                                                               class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-100 transition-all group/btn">
                                                                <svg class="w-4 h-4 text-slate-400 group-hover/btn:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                Download Report
                                                            </a>
                                                        @endif
                                                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-300" :class="termOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                    </div>
                                                </div>

                                                <div x-show="termOpen" x-collapse x-cloak>
                                                    <div class="px-6 pb-6">
                                                        @php
                                                            $pivotedMarks = $marks->groupBy('subject_id');
                                                        @endphp
                                                        <div class="overflow-x-auto no-scrollbar pb-4">
                                                            <table class="w-full text-left border-separate border-spacing-x-2">
                                                                <thead>
                                                                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                                        @foreach($pivotedMarks as $subjectId => $subjectMarks)
                                                                            <th class="pb-3 text-center px-4 bg-slate-50 rounded-t-xl border-x border-t border-slate-100 min-w-[120px]">
                                                                                {{ $subjectMarks->first()->subject->name }}
                                                                            </th>
                                                                        @endforeach
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        @foreach($pivotedMarks as $subjectId => $subjectMarks)
                                                                            @php
                                                                                $total = $subjectMarks->sum('score');
                                                                            @endphp
                                                                            <td class="py-6 text-center bg-white border border-slate-100 rounded-b-xl shadow-sm">
                                                                                <span class="text-xl font-black text-indigo-600">
                                                                                    {{ number_format($total, 1) }}
                                                                                </span>
                                                                                <div class="text-[8px] font-black text-slate-300 uppercase tracking-widest mt-1">Total Score</div>
                                                                            </td>
                                                                        @endforeach
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-20 flex flex-col items-center justify-center text-slate-300">
                                <div class="w-20 h-20 rounded-[2rem] bg-slate-50 flex items-center justify-center mb-4 border border-slate-100 shadow-inner">
                                    <svg class="w-10 h-10 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                                <p class="font-black text-sm uppercase tracking-widest">No Academic Records</p>
                                <p class="text-xs font-semibold mt-1">This student has no graded assessments yet.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- ATTENDANCE TAB -->
                    <div x-show="tab === 'attendance'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white shadow-xl shadow-slate-200/50 p-6">
                                <span class="text-[10px] text-emerald-500 font-black uppercase tracking-widest block mb-1">Present</span>
                                <span class="text-3xl font-black text-slate-900">{{ $attendanceStats['present'] ?? 0 }}</span>
                                <div class="mt-4 w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $attendanceStats['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white shadow-xl shadow-slate-200/50 p-6">
                                <span class="text-[10px] text-rose-500 font-black uppercase tracking-widest block mb-1">Absent</span>
                                <span class="text-3xl font-black text-slate-900">{{ $attendanceStats['absent'] ?? 0 }}</span>
                                <div class="mt-4 text-[10px] font-bold text-slate-400">Excused: {{ $attendanceStats['excused'] ?? 0 }}</div>
                            </div>
                            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white shadow-xl shadow-slate-200/50 p-6">
                                <span class="text-[10px] text-amber-500 font-black uppercase tracking-widest block mb-1">Late Arrivals</span>
                                <span class="text-3xl font-black text-slate-900">{{ $attendanceStats['late'] ?? 0 }}</span>
                            </div>
                            <div class="bg-indigo-600 rounded-[2rem] p-6 text-white shadow-xl shadow-indigo-100">
                                <span class="text-[10px] text-indigo-100 font-black uppercase tracking-widest block mb-1">Attendance Rate</span>
                                <span class="text-3xl font-black">{{ $attendanceStats['percentage'] ?? 0 }}%</span>
                                <p class="mt-4 text-[10px] font-semibold text-indigo-100 tracking-wide uppercase">Consistently Attending</p>
                            </div>

                            <div class="md:col-span-4 bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 shadow-sm border border-slate-100/50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900">Recent Logs</h3>
                                </div>

                                <div class="overflow-x-auto no-scrollbar -mx-8 px-8">
                                    <table class="w-full text-left border-separate border-spacing-y-2">
                                        <thead>
                                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                <th class="pb-4 pl-4">Date</th>
                                                <th class="pb-4">Status</th>
                                                <th class="pb-4">Remark</th>
                                                <th class="pb-4 text-right pr-4">Recorded By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentAttendance as $attendance)
                                                <tr class="group hover:bg-slate-50/50 transition-colors">
                                                    <td class="py-4 pl-4 bg-slate-50/30 rounded-l-2xl border-y border-l border-slate-100">
                                                        <span class="font-bold text-slate-700 leading-tight">{{ $attendance->attendance_date->format('D, M d, Y') }}</span>
                                                    </td>
                                                    <td class="py-4 bg-slate-50/30 border-y border-slate-100">
                                                        @php
                                                            $statusColors = [
                                                                'present' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
                                                                'absent' => 'bg-rose-50 text-rose-600 ring-rose-100',
                                                                'late' => 'bg-amber-50 text-amber-600 ring-amber-100',
                                                                'excused' => 'bg-indigo-50 text-indigo-600 ring-indigo-100'
                                                            ];
                                                            $color = $statusColors[strtolower($attendance->status)] ?? 'bg-slate-50 text-slate-600 ring-slate-100';
                                                        @endphp
                                                        <span class="px-3 py-1 rounded-lg {{ $color }} text-[10px] font-black uppercase tracking-widest ring-1 ring-inset whitespace-nowrap">
                                                            {{ $attendance->status }}
                                                        </span>
                                                    </td>
                                                    <td class="py-4 bg-slate-50/30 border-y border-slate-100">
                                                        <span class="text-sm font-semibold text-slate-500 italic">{{ \Str::limit($attendance->remarks, 30) ?: 'No remark' }}</span>
                                                    </td>
                                                    <td class="py-4 pr-4 bg-slate-50/30 rounded-r-2xl border-y border-r border-slate-100 text-right text-xs font-bold text-slate-400">
                                                        {{ $attendance->marker->full_name ?? 'System' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DISCIPLINARY TAB -->
                    <div x-show="tab === 'disciplinary'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                            <div class="flex items-center justify-between mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-sm border border-rose-100/50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900">Conduct & Discipline</h3>
                                </div>
                            </div>

                            @if($disciplinaryRecords->count() > 0)
                                <div class="space-y-6">
                                    @foreach($disciplinaryRecords as $record)
                                        <div class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 hover:bg-white hover:shadow-xl transition-all group">
                                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                                <div class="flex items-center gap-3">
                                                    <span class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-[10px] font-black text-rose-600 uppercase tracking-widest shadow-sm">{{ $record->action_taken ?: 'Pending' }}</span>
                                                    <span class="text-xs font-bold text-slate-400">{{ $record->incident_date->format('M d, Y') }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tier:</span>
                                                    @php
                                                        $tiers = [
                                                            'minor'    => ['color' => 'bg-emerald-500', 'bars' => 1],
                                                            'moderate' => ['color' => 'bg-amber-500',   'bars' => 2],
                                                            'critical' => ['color' => 'bg-rose-600',    'bars' => 3],
                                                        ];
                                                        $tierData = $tiers[$record->tier] ?? ['color' => 'bg-slate-400', 'bars' => 1];
                                                    @endphp
                                                    <div class="flex gap-1">
                                                        <div class="w-4 h-1 rounded-full {{ $tierData['color'] }}"></div>
                                                        <div class="w-4 h-1 rounded-full {{ $tierData['bars'] >= 2 ? $tierData['color'] : 'bg-slate-100' }}"></div>
                                                        <div class="w-4 h-1 rounded-full {{ $tierData['bars'] >= 3 ? $tierData['color'] : 'bg-slate-100' }}"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="text-lg font-black text-slate-800 mb-2">{{ ucfirst($record->infraction_name) }}</h4>
                                            <p class="text-sm text-slate-500 font-semibold leading-relaxed">{{ $record->description }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center py-20 text-slate-300">
                                    <div class="w-20 h-20 rounded-[2rem] bg-slate-50 flex items-center justify-center mb-4 border border-slate-100 shadow-inner">
                                        <svg class="w-10 h-10 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <p class="font-black text-sm uppercase tracking-widest">Exemplary Conduct</p>
                                    <p class="text-xs font-semibold mt-1">No disciplinary incidents on record.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- DOCUMENTS TAB -->
                    <div x-show="tab === 'documents'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                            <div class="flex items-center justify-between mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900">Document Vault</h3>
                                </div>
                                <div x-data="{ open: false }">
                                    <button @click="open = true" class="px-6 py-3 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:shadow-lg transition-all flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Upload New
                                    </button>
                                    
                                    <!-- Simple Upload Modal (Alpine.js) -->
                                    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
                                        <div @click.away="open = false" class="bg-white rounded-[2.5rem] p-8 w-full max-w-md shadow-2xl">
                                            <h4 class="text-xl font-bold text-slate-900 mb-6">Upload Document</h4>
                                            <form action="{{ route('admin.students.store-document', $student) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                                @csrf
                                                <div>
                                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Document Name</label>
                                                    <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-semibold text-slate-700">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">File Selection</label>
                                                    <input type="file" name="file" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-all">
                                                </div>
                                                <div class="flex gap-3 pt-4">
                                                    <button type="button" @click="open = false" class="flex-1 px-6 py-3 bg-slate-100 text-slate-500 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
                                                    <button type="submit" class="flex-1 px-6 py-3 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:shadow-lg transition-all">Upload</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @forelse($student->documents as $doc)
                                    <div class="bg-slate-50/50 border border-slate-100 rounded-[2rem] p-6 hover:bg-white hover:shadow-xl transition-all group relative">
                                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-slate-400 mb-4 group-hover:scale-110 transition-transform">
                                            @php
                                                $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                                                $icon = match($ext) {
                                                    'pdf' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                                    'jpg', 'jpeg', 'png' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                                                    default => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'
                                                };
                                            @endphp
                                            <svg class="w-6 h-6 {{ $ext == 'pdf' ? 'text-rose-500' : 'text-indigo-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path></svg>
                                        </div>
                                        <h5 class="font-black text-slate-800 text-sm truncate pr-8">{{ $doc->name }}</h5>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ strtoupper($ext) }} • {{ $doc->created_at->format('M d, Y') }}</p>
                                        
                                        <div class="mt-6 flex items-center gap-2">
                                            <a href="{{ route('admin.students.download-document', [$student, $doc]) }}" target="_blank" class="flex-grow py-2.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 text-center transition-all">View</a>
                                            <form action="{{ route('admin.students.delete-document', [$student, $doc]) }}" method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2.5 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-3 flex flex-col items-center justify-center py-20 text-slate-300">
                                        <div class="w-20 h-20 rounded-[2rem] bg-slate-50 flex items-center justify-center mb-4 border border-slate-100 shadow-inner">
                                            <svg class="w-10 h-10 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                                        </div>
                                        <p class="font-black text-sm uppercase tracking-widest">Digital Archive Empty</p>
                                        <p class="text-xs font-semibold mt-1">No documents have been uploaded yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- Link Sibling Modal -->
    <template x-teleport="body">
        <div x-cloak x-show="$store.ui.modal.sibling" 
             x-transition.opacity
             class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             @keydown.escape.window="$store.ui.closeModal('sibling')">
            <div @click.away="$store.ui.closeModal('sibling')" 
                 x-data="{
                    searchQuery: '',
                    searchResults: [],
                    loading: false,
                    allLinkedSiblings: {{ $student->siblings->pluck('id')->toJson() }},
                    sessionChanges: 0,
                    async searchStudents() {
                        const query = this.searchQuery.trim();
                        if (query.length < 2) {
                            this.searchResults = [];
                            return;
                        }
                        this.loading = true;
                        try {
                            const resp = await fetch('/admin/search?q=' + encodeURIComponent(query));
                            const data = await resp.json();
                            this.searchResults = data.filter(r => r.type === 'Student' && r.id != '{{ $student->id }}');
                        } catch (e) {
                            console.error('Search error:', e);
                        } finally {
                            this.loading = false;
                        }
                    },
                    async linkSibling(siblingId) {
                        try {
                            const resp = await fetch('{{ route('admin.students.siblings.link', $student) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ sibling_id: siblingId })
                            });
                            
                            if (resp.ok) {
                                this.allLinkedSiblings.push(parseInt(siblingId));
                                this.sessionChanges++;
                            } else {
                                console.error('Failed to link sibling');
                            }
                        } catch (e) {
                            console.error('Link error:', e);
                        }
                    },
                    async unlinkSibling(siblingId) {
                        try {
                            const resp = await fetch(`/admin/students/{{ $student->id }}/siblings/${siblingId}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });
                            if (resp.ok) {
                                this.allLinkedSiblings = this.allLinkedSiblings.filter(id => id != siblingId);
                                this.sessionChanges++;
                            } else {
                                console.error('Failed to unlink sibling');
                            }
                        } catch (e) {
                            console.error('Unlink error:', e);
                        }
                    },
                    isLinked(id) {
                        return this.allLinkedSiblings.some(linkedId => linkedId == id);
                    }
                 }"
                 class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh] pointer-events-auto">
                <div class="p-8 border-b border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-black text-slate-900">Manage Siblings</h3>
                        <button @click="$store.ui.closeModal('sibling'); if(sessionChanges > 0) location.reload();" class="p-2 hover:bg-slate-100 rounded-full transition-colors text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" 
                               x-model.debounce.300ms="searchQuery" 
                               @input.stop="searchStudents()"
                               placeholder="Search student by name or ID..." 
                               autocomplete="off"
                               class="w-full pl-12 pr-4 py-4 rounded-2xl bg-slate-50 border-0 focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-700">
                    </div>
                    
                    <div x-show="sessionChanges > 0" class="mt-4 p-3 bg-indigo-50 rounded-xl">
                        <p class="text-indigo-700 font-bold text-sm"><span x-text="sessionChanges"></span> change(s) made this session. Page will refresh on close.</p>
                    </div>
                </div>

                <div class="flex-grow overflow-y-auto p-8 bg-slate-50/30 no-scrollbar">
                    <div x-show="loading" class="flex flex-col items-center justify-center py-12">
                        <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-slate-500 font-bold text-sm uppercase tracking-widest">Searching Database...</p>
                    </div>

                    <div x-show="!loading && searchResults.length === 0 && searchQuery.length >= 2" class="text-center py-12">
                        <p class="text-slate-400 font-bold">No students found matching your search.</p>
                    </div>

                    <div x-show="!loading && searchResults.length > 0" class="space-y-4">
                        <template x-for="result in searchResults" :key="result.id">
                            <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-white shadow-sm hover:shadow-md transition-all group" :class="isLinked(result.id) ? 'ring-2 ring-emerald-500' : ''">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-sm font-black ring-1" :class="isLinked(result.id) ? 'bg-emerald-50 text-emerald-600 ring-emerald-100' : 'bg-indigo-50 text-indigo-600 ring-indigo-100'">
                                        <template x-if="isLinked(result.id)">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </template>
                                        <template x-if="!isLinked(result.id)">
                                            <span x-text="result.title.substring(0, 1)"></span>
                                        </template>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-900 leading-tight" x-text="result.title"></h4>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest" x-text="result.subtitle"></p>
                                    </div>
                                </div>
                                <button 
                                    x-show="!isLinked(result.id)"
                                    @click="linkSibling(result.id)" 
                                    class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">
                                    Link Now
                                </button>
                                <button 
                                    x-show="isLinked(result.id)"
                                    @click="unlinkSibling(result.id)" 
                                    class="px-4 py-2 bg-rose-50 text-rose-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all">
                                    Unlink
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </template>
    </div>
    <!-- Report Card Modal -->
    <template x-teleport="body">
        <div x-show="$store.ui.modal.reportCard" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             x-cloak>
            <div x-show="$store.ui.modal.reportCard" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
                 @click="$store.ui.closeModal('reportCard')"></div>

            <div x-show="$store.ui.modal.reportCard" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 x-data="{ 
                    selectedYear: '{{ $academicYear->id ?? '' }}', 
                    selectedTerm: '{{ $availableTerms->first()->id ?? '' }}',
                    availableTerms: {{ $availableTerms->toJson() }}
                 }"
                 class="bg-white/90 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl w-full max-w-lg overflow-hidden relative z-10">
                
                <div class="p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Generate Report Card</h3>
                            <p class="text-slate-500 text-sm font-semibold">Select the academic year and term.</p>
                        </div>
                        <button @click="$store.ui.closeModal('reportCard')" class="p-2 hover:bg-slate-100 rounded-full transition-colors text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form action="{{ route('admin.report-cards.pdf', $student) }}" method="GET" target="_blank" class="space-y-6">
                        <div>
                            <label class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-2 ml-1">Academic Year</label>
                            <select name="academic_year_id" x-model="selectedYear" class="w-full bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700 h-14 px-4 transition-all">
                                @foreach($availableYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-2 ml-1">Select Term</label>
                            <select name="term_id" x-model="selectedTerm" class="w-full bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700 h-14 px-4 transition-all">
                                <option value="yearly">Yearly Report (Full Year)</option>
                                <template x-for="term in availableTerms.filter(t => t.academic_year_id == selectedYear)" :key="term.id">
                                    <option :value="term.id" x-text="term.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="pt-4">
                            <button type="submit" 
                                    @click="$store.ui.closeModal('reportCard')"
                                    class="w-full py-4 bg-indigo-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-emerald-500 transition-all shadow-xl shadow-indigo-100 flex items-center justify-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Generate Report Card
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</x-admin-layout>
