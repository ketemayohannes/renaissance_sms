<x-student-layout header="My Profile">

    <div class="space-y-8" x-data="{ tab: 'overview' }">
        
        <!-- Profile Header Banner -->
        <div class="relative overflow-hidden rounded-[3rem] bg-slate-900 p-8 md:p-12 shadow-2xl glass-panel border-0">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/30 via-transparent to-purple-500/30 opacity-70"></div>
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl mix-blend-screen pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-6">
                <!-- Large Avatar Badge with Photo fallback -->
                <div class="relative group flex-shrink-0">
                    <div class="w-24 h-24 md:w-28 md:h-28 rounded-[2rem] bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl font-black shadow-xl shadow-indigo-500/20 border-4 border-white/10 overflow-hidden">
                        @if($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" alt="Student Photo" class="w-full h-full object-cover">
                        @else
                            {{ substr($student->full_name, 0, 1) }}
                        @endif
                    </div>
                    <div class="absolute -bottom-2 -right-2 px-3 py-1 bg-emerald-500 border-2 border-slate-900 rounded-xl text-[9px] font-black uppercase tracking-widest text-white shadow-lg">
                        {{ $student->is_active ? 'Active' : 'Inactive' }}
                    </div>
                </div>
                
                <div class="space-y-2">
                    <span class="px-3 py-1 bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 rounded-xl text-xs font-black uppercase tracking-widest">Student Portal</span>
                    <h1 class="text-3xl md:text-5xl font-black text-white font-heading tracking-tight mt-1">{{ $student->full_name }}</h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-indigo-200/80 text-sm font-medium">
                        <span>Student ID: <span class="text-white font-extrabold uppercase">#{{ $student->student_id }}</span></span>
                        <span class="hidden sm:inline text-indigo-200/40">•</span>
                        <span>Admission No: <span class="text-white font-extrabold">{{ $student->admission_number }}</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Glass Navigation Tabs -->
        <div class="sticky top-4 z-40 bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2rem] shadow-xl shadow-slate-200/40 dark:shadow-none p-2 overflow-x-auto no-scrollbar">
            <nav class="flex gap-1 min-w-max">
                <button @click="tab = 'overview'" 
                        :class="tab === 'overview' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Overview
                </button>
                <button @click="tab = 'academic'" 
                        :class="tab === 'academic' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.247 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Academics
                </button>
                <button @click="tab = 'guardians'" 
                        :class="tab === 'guardians' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Guardians
                    <span class="py-0.5 px-2 rounded-lg text-[8px] font-black" :class="tab === 'guardians' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'">{{ $student->guardians->count() }}</span>
                </button>
                <button @click="tab = 'medical_transport'" 
                        :class="tab === 'medical_transport' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                    Health & Bus
                </button>
                <button @click="tab = 'security'" 
                        :class="tab === 'security' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2 font-heading">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Security Settings
                </button>
            </nav>
        </div>

        <!-- TAB CONTENT PANELS -->
        <div>
            <!-- OVERVIEW TAB -->
            <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Personal Info Card -->
                    <div class="glass-panel border-white dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 p-8 shadow-sm space-y-6">
                        <div class="flex items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Personal Details</h2>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Demographics and identity records</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-300">
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Official Name</span>
                                <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">{{ $student->full_name }}</span>
                            </div>

                            <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-300">
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Date of Birth</span>
                                <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : 'N/A' }}</span>
                            </div>

                            <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-300">
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Age</span>
                                <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">{{ $student->date_of_birth ? $student->date_of_birth->age . ' Years Old' : 'N/A' }}</span>
                            </div>

                            <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-300">
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Gender</span>
                                <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 capitalize">{{ $student->gender == 'M' ? 'Male' : ($student->gender == 'F' ? 'Female' : $student->gender) }}</span>
                            </div>

                            <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-300">
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Birthplace / Country</span>
                                <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">{{ $student->birth_city ? $student->birth_city . ', ' : '' }}{{ $student->birth_country ?? 'N/A' }}</span>
                            </div>

                            <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-300">
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Nationality & Spoken Language</span>
                                <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">{{ $student->nationality ?? 'Ethiopian' }} • {{ $student->language_spoken ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Residence & Account details -->
                    <div class="space-y-8">
                        <div class="glass-panel border-white dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 p-8 shadow-sm space-y-6">
                            <div class="flex items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-amber-100/30 dark:border-amber-950/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Residency & Address</h2>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Your official home coordinates</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-300">
                                    <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Subcity / Woreda</span>
                                    <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">{{ $student->subcity ?? '-' }} / Woreda {{ $student->woreda ?? '-' }}</span>
                                </div>

                                <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-300">
                                    <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">House Number</span>
                                    <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">{{ $student->house_number ?? '-' }}</span>
                                </div>

                                <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-300 sm:col-span-2">
                                    <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Registered Primary Address</span>
                                    <span class="text-base font-extrabold text-slate-700 dark:text-slate-300">{{ $student->full_address }}</span>
                                </div>

                                <div class="p-5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-950 hover:border-indigo-200 dark:hover:border-indigo-800 transition-all duration-300 sm:col-span-2">
                                    <span class="block text-[10px] font-black text-indigo-400 dark:text-indigo-400 uppercase tracking-widest mb-1">Emergency Direct Line</span>
                                    <span class="text-lg font-black text-indigo-700 dark:text-indigo-300 tracking-wider">{{ $student->phone ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Portal Access Card -->
                        <div class="bg-indigo-600 dark:bg-indigo-700 rounded-[2.5rem] shadow-xl shadow-indigo-100 dark:shadow-none p-8 text-white relative overflow-hidden group">
                            <div class="absolute top-0 right-0 -m-12 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                            
                            <h3 class="text-xl font-bold mb-4 flex items-center gap-3 font-heading">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0014 11V4h-3L9 7l-2-3H4v7a10 10 0 005.183 8.761"></path>
                                </svg>
                                System Portal Account
                            </h3>
                            
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6">
                                <div>
                                    <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1">Registered Sign-in Email</p>
                                    <p class="font-bold text-lg leading-tight break-all">{{ $user->email }}</p>
                                </div>
                                <div class="flex items-center gap-2 bg-white/10 border border-white/20 px-4 py-2 rounded-2xl">
                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <span class="text-xs font-black uppercase tracking-widest text-indigo-100">Live Access</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACADEMICS TAB -->
            <div x-show="tab === 'academic'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                <!-- Academic Badges and Quick details -->
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2.5rem] shadow-xl p-8">
                    <div class="flex items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-5 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-violet-100/30 dark:border-violet-950/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.247 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Current Enrollment status</h2>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Your active class registry particulars</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-indigo-50/50 dark:bg-indigo-950/20 p-5 rounded-[1.5rem] border border-indigo-100/30 dark:border-indigo-900/40 shadow-sm">
                            <span class="text-[10px] text-indigo-500 dark:text-indigo-400 font-black uppercase tracking-widest block mb-1">Current Section</span>
                            <span class="font-black text-xl text-slate-800 dark:text-slate-100">{{ $student->currentEnrollment ? $student->currentEnrollment->section->name : 'N/A' }}</span>
                        </div>
                        <div class="bg-violet-50/50 dark:bg-violet-950/20 p-5 rounded-[1.5rem] border border-violet-100/30 dark:border-violet-900/40 shadow-sm">
                            <span class="text-[10px] text-violet-500 dark:text-violet-400 font-black uppercase tracking-widest block mb-1">Grade Level</span>
                            <span class="font-black text-xl text-slate-800 dark:text-slate-100">{{ $student->currentEnrollment ? $student->currentEnrollment->section->gradeLevel->name : 'N/A' }}</span>
                        </div>
                        <div class="bg-amber-50/50 dark:bg-amber-950/20 p-5 rounded-[1.5rem] border border-amber-100/30 dark:border-amber-900/40 shadow-sm">
                            <span class="text-[10px] text-amber-600 dark:text-amber-400 font-black uppercase tracking-widest block mb-1">Roll Number</span>
                            <span class="font-black text-xl text-slate-800 dark:text-slate-100">#{{ $student->currentEnrollment ? $student->currentEnrollment->roll_number ?? 'N/A' : 'N/A' }}</span>
                        </div>
                        <div class="bg-emerald-50/50 dark:bg-emerald-950/20 p-5 rounded-[1.5rem] border border-emerald-100/30 dark:border-emerald-900/40 shadow-sm">
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-black uppercase tracking-widest block mb-1">Division</span>
                            <span class="font-black text-xl text-slate-800 dark:text-slate-100">{{ $student->currentEnrollment ? $student->currentEnrollment->section->gradeLevel->division->name : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Complete Enrollment History -->
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2.5rem] shadow-xl p-8">
                    <div class="flex items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-5 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Enrollment Ledger</h2>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Your official academic chronology at this institution</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <th class="pb-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Academic Year</th>
                                    <th class="pb-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Grade & Section</th>
                                    <th class="pb-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Division</th>
                                    <th class="pb-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Roll No</th>
                                    <th class="pb-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/40">
                                @forelse($student->enrollments->sortByDesc('enrollment_date') as $enrollment)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                        <td class="py-4 font-bold text-slate-800 dark:text-slate-200">
                                            {{ $enrollment->academicYear->name }}
                                        </td>
                                        <td class="py-4">
                                            <span class="font-extrabold text-slate-900 dark:text-slate-100">{{ $enrollment->section->gradeLevel->name }}</span>
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-black ml-1.5">{{ $enrollment->section->name }}</span>
                                        </td>
                                        <td class="py-4 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $enrollment->section->gradeLevel->division->name }}
                                        </td>
                                        <td class="py-4 text-center font-black text-slate-700 dark:text-slate-300">
                                            #{{ $enrollment->roll_number ?? '-' }}
                                        </td>
                                        <td class="py-4 text-right">
                                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest ring-1 ring-inset {{ $enrollment->status == 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 ring-emerald-500/20' : 'bg-slate-500/10 text-slate-500 ring-slate-500/20' }}">
                                                {{ $enrollment->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-slate-400 dark:text-slate-500 font-semibold text-sm">
                                            No prior institutional enrollments found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- GUARDIANS TAB -->
            <div x-show="tab === 'guardians'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @forelse($student->guardians as $guardian)
                        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2.5rem] shadow-xl p-8 relative overflow-hidden group">
                            <div class="flex flex-col sm:flex-row items-start gap-6">
                                <!-- Photo -->
                                <div class="relative flex-shrink-0">
                                    <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-800 border-2 border-white dark:border-slate-800 shadow-md overflow-hidden">
                                        @if($guardian->photo)
                                            <img src="{{ asset('storage/' . $guardian->photo) }}" alt="Guardian Photo" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-slate-800 dark:to-slate-700 text-indigo-300 dark:text-slate-500">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    @if($guardian->is_emergency_contact)
                                        <div class="absolute -top-2.5 -left-2.5 w-7 h-7 bg-rose-500 rounded-full border-4 border-white dark:border-slate-900 shadow-md flex items-center justify-center text-white" title="Primary Emergency Contact">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-grow">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">{{ $guardian->full_name }}</h4>
                                        <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 text-[9px] font-black uppercase tracking-widest border border-indigo-100 dark:border-indigo-900/40">
                                            {{ $guardian->relationship }}
                                        </span>
                                    </div>
                                    <p class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-4">{{ ucfirst($guardian->guardian_type) }} Guardian</p>
                                    
                                    <div class="space-y-2.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-lg bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-400 dark:text-slate-600 shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                            </div>
                                            <span class="font-extrabold text-slate-700 dark:text-slate-300 text-sm tracking-wider">{{ $guardian->phone }}</span>
                                        </div>
                                        @if($guardian->email)
                                            <div class="flex items-center gap-3">
                                                <div class="w-7 h-7 rounded-lg bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-400 dark:text-slate-600 shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                <span class="font-semibold text-slate-600 dark:text-slate-400 text-sm truncate max-w-[200px]">{{ $guardian->email }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest {{ $guardian->user_id ? 'text-emerald-500' : 'text-slate-400' }}">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $guardian->user_id ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }}"></div>
                                    Portal Connected: {{ $guardian->user_id ? 'Active' : 'Offline' }}
                                </div>
                                <div class="text-[9px] font-bold text-slate-400 dark:text-slate-500">
                                    UID: #G{{ str_pad($guardian->id, 5, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2.5rem] p-12 text-center text-slate-400 dark:text-slate-500">
                            <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-950 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-black text-sm uppercase tracking-widest text-slate-800 dark:text-slate-200">No Family Records Found</h4>
                            <p class="text-xs font-semibold mt-1">Please contact the administration to update your linked parent or guardian details.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- HEALTH & TRANSPORT TAB -->
            <div x-show="tab === 'medical_transport'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Medical Information Card -->
                    <div class="glass-panel border-white dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 p-8 shadow-sm space-y-6">
                        <div class="flex items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-450 flex items-center justify-center flex-shrink-0 shadow-sm border border-rose-100/30 dark:border-rose-950/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Medical Profile</h2>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Critical school health parameters</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @if($student->medicalInfo)
                                <div class="grid grid-cols-3 gap-6 items-center p-4 rounded-2xl bg-rose-500/5 dark:bg-rose-950/15 border border-rose-500/10">
                                    <div class="col-span-1 text-center border-r border-rose-500/10">
                                        <span class="block text-[8px] font-black text-rose-500 uppercase tracking-widest mb-0.5">Blood Type</span>
                                        <span class="text-3xl font-black text-rose-600 dark:text-rose-400">{{ $student->medicalInfo->blood_group ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="block text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-0.5">Emergency Contact</span>
                                        <span class="text-sm font-extrabold text-slate-700 dark:text-slate-300 leading-snug block">{{ $student->medicalInfo->emergency_contact ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80">
                                    <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Allergies & Sensitivities</span>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100/50 dark:border-slate-800/60 leading-relaxed">
                                        {{ $student->medicalInfo->allergies ?: 'No registered allergies.' }}
                                    </p>
                                </div>

                                <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80">
                                    <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Registered Medical Issues</span>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100/50 dark:border-slate-800/60 leading-relaxed">
                                        {{ $student->medicalInfo->medical_issues ?: 'No chronic issues on record.' }}
                                    </p>
                                </div>

                                <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80">
                                    <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Ongoing Medication</span>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100/50 dark:border-slate-800/60 leading-relaxed">
                                        {{ $student->medicalInfo->current_medication ?: 'No ongoing prescription medications.' }}
                                    </p>
                                </div>
                            @else
                                <div class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-950 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-black text-sm uppercase tracking-widest text-slate-800 dark:text-slate-200">No Medical Info Declared</h4>
                                    <p class="text-xs font-semibold mt-1">Please provide any health concerns directly to our registrar office.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Transportation Info Card -->
                    <div class="glass-panel border-white dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 p-8 shadow-sm space-y-6">
                        <div class="flex items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Transportation & School Bus</h2>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Your official daily commute registry</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            @if($student->transportation)
                                <!-- Driver & Bus Card -->
                                <div class="p-6 bg-slate-50 dark:bg-slate-950/30 rounded-[2rem] border border-slate-100 dark:border-slate-800/80 relative overflow-hidden group">
                                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                                        <!-- Driver Avatar -->
                                        <div class="w-20 h-20 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-md overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            @if($student->transportation->driver_photo)
                                                <img src="{{ asset('storage/' . $student->transportation->driver_photo) }}" alt="Driver Photo" class="w-full h-full object-cover">
                                            @else
                                                <div class="text-indigo-400 font-black text-2xl">
                                                    {{ substr($student->transportation->driver_first_name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-grow text-center sm:text-left">
                                            <span class="inline-flex px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-lg text-[9px] font-black uppercase tracking-widest mb-1.5 border border-emerald-500/20">Active Rider</span>
                                            <h4 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $student->transportation->driver_full_name }}</h4>
                                            <p class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mt-0.5">Assigned Bus Driver</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80">
                                        <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Vehicle Plate</span>
                                        <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 font-mono tracking-widest uppercase bg-slate-100 dark:bg-slate-950/70 px-3 py-1 rounded-lg border border-slate-200/50 dark:border-slate-850 inline-block mt-0.5">
                                            {{ $student->transportation->vehicle_plate }}
                                        </span>
                                    </div>

                                    <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80">
                                        <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Route Particulars</span>
                                        <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 block mt-0.5">
                                            {{ $student->transportation->route }}
                                        </span>
                                    </div>

                                    <div class="p-5 rounded-2xl bg-white/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 sm:col-span-2">
                                        <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Driver License Number</span>
                                        <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 block mt-0.5">
                                            {{ $student->transportation->license_number }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="py-20 text-center text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-slate-950/10 border border-slate-100/50 dark:border-slate-800/60 rounded-[2.5rem]">
                                    <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-950 flex items-center justify-center mx-auto mb-4 border border-slate-200 dark:border-slate-900 shadow-inner">
                                        <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-black text-sm uppercase tracking-widest text-slate-800 dark:text-slate-200">No Bus Service Linked</h4>
                                    <p class="text-xs font-semibold mt-1">This profile is not registered under our school transportation network.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECURITY SETTINGS TAB -->
            <div x-show="tab === 'security'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-2xl mx-auto">
                <div class="glass-panel border-white dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 p-8 shadow-sm space-y-6">
                    <div class="flex items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Security Settings</h2>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Manage your credentials and login safety</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('student.password.update') }}" class="space-y-6">
                        @csrf
                        @method('put')

                        <div class="space-y-2">
                            <x-input-label for="current_password" :value="__('Current Password')" class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest" />
                            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/70 dark:bg-slate-950/50 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 dark:focus:ring-indigo-950/50 focus:ring-opacity-50 text-sm font-semibold transition" autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('current_password')" class="mt-2 text-xs text-rose-500 font-semibold" />
                        </div>

                        <div class="space-y-2">
                            <x-input-label for="password" :value="__('New Password')" class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/70 dark:bg-slate-950/50 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 dark:focus:ring-indigo-950/50 focus:ring-opacity-50 text-sm font-semibold transition" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-semibold" />
                        </div>

                        <div class="space-y-2">
                            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/70 dark:bg-slate-950/50 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 dark:focus:ring-indigo-950/50 focus:ring-opacity-50 text-sm font-semibold transition" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-rose-500 font-semibold" />
                        </div>

                        <div class="flex items-center gap-4 pt-4">
                            <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white rounded-3xl font-black shadow-lg shadow-indigo-500/10 hover:shadow-xl transition-all transform hover:-translate-y-0.5 text-xs uppercase tracking-widest">
                                {{ __('Save Credentials') }}
                            </button>

                            @if (session('status') === 'password-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 3000)"
                                    class="text-sm font-bold text-emerald-600 dark:text-emerald-400"
                                >{{ __('Saved Successfully.') }}</p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-student-layout>
