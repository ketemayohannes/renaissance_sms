<x-parent-layout header="{{ $student->full_name }}'s Profile Details">
    <div class="space-y-8 max-w-6xl mx-auto" x-data="{ tab: 'overview' }">
        
        <!-- Profile Header Banner -->
        <div class="relative bg-gradient-to-r from-indigo-900 to-slate-900 dark:from-slate-900 dark:to-slate-950 rounded-3xl p-6 lg:p-8 text-white overflow-hidden shadow-xl border border-white/10">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -right-20 -bottom-20 w-60 h-60 bg-indigo-500/20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
                <!-- Avatar -->
                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-extrabold text-3xl shadow-lg border border-white/20 overflow-hidden flex-shrink-0">
                    @if($student->photo)
                        <img src="/storage/{{ $student->photo }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover">
                    @else
                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                    @endif
                </div>
                
                <!-- Info -->
                <div class="flex-grow text-center sm:text-left space-y-2">
                    <div class="flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                        <h2 class="text-2xl font-bold font-heading tracking-tight text-slate-100">{{ $student->full_name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $student->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                            {{ $student->is_active ? 'Active Student' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1.5 text-slate-350 text-sm font-medium">
                        <span>Student ID: <span class="text-white font-mono font-semibold">#{{ $student->student_id }}</span></span>
                        <span class="hidden sm:inline text-white/20">•</span>
                        <span>Admission No: <span class="text-white font-mono font-semibold">{{ $student->admission_number }}</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Glass Navigation Tabs -->
        <div class="bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl border border-white dark:border-slate-800 rounded-2xl shadow-sm p-1.5 overflow-x-auto no-scrollbar">
            <nav class="flex gap-1 min-w-max">
                <button @click="tab = 'overview'" 
                        :class="tab === 'overview' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Overview
                </button>
                <button @click="tab = 'academic'" 
                        :class="tab === 'academic' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.247 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Academics
                </button>
                <button @click="tab = 'guardians'" 
                        :class="tab === 'guardians' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Family Connections
                    <span class="py-0.5 px-1.5 rounded-md text-[8px] font-black" :class="tab === 'guardians' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'">{{ $student->guardians->count() }}</span>
                </button>
                <button @click="tab = 'medical_transport'" 
                        :class="tab === 'medical_transport' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                    Health & Commute
                </button>
            </nav>
        </div>

        <!-- TAB PANELS -->
        <div>
            <!-- OVERVIEW TAB -->
            <div x-show="tab === 'overview'" x-transition class="space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Personal Info Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-6 lg:p-8 rounded-3xl shadow-sm space-y-6">
                        <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading">Personal Details</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-450">Official identity and demographics</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Official Name</span>
                                <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $student->full_name }}</span>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Date of Birth</span>
                                <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : 'N/A' }}</span>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Age</span>
                                <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $student->date_of_birth ? $student->date_of_birth->age . ' Years Old' : 'N/A' }}</span>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Gender</span>
                                <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $student->gender == 'M' ? 'Male' : ($student->gender == 'F' ? 'Female' : $student->gender) }}</span>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Birthplace / Country</span>
                                <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $student->birth_city ? $student->birth_city . ', ' : '' }}{{ $student->birth_country ?? 'N/A' }}</span>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nationality / Language</span>
                                <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $student->nationality ?? 'Ethiopian' }} • {{ $student->language_spoken ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Residence & Contacts -->
                    <div class="space-y-8">
                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-6 lg:p-8 rounded-3xl shadow-sm space-y-6">
                            <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                                <div class="p-2.5 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-xl">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading">Address & Contact</h3>
                                    <p class="text-xs text-slate-400 dark:text-slate-450">Official coordinates and direct emergency lines</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Subcity / Woreda</span>
                                    <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $student->subcity ?? '-' }} / Woreda {{ $student->woreda ?? '-' }}</span>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">House Number</span>
                                    <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $student->house_number ?? '-' }}</span>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800 sm:col-span-2">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Registered Address</span>
                                    <span class="text-sm font-extrabold text-slate-750 dark:text-slate-300 leading-normal block">{{ $student->full_address }}</span>
                                </div>

                                <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-950/45 sm:col-span-2">
                                    <span class="block text-[10px] font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-wider mb-1">Emergency Direct Line</span>
                                    <span class="text-base font-extrabold text-indigo-700 dark:text-indigo-300 tracking-wider block">{{ $student->phone ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- System Portal Account Info -->
                        <div class="bg-indigo-900 rounded-3xl p-6 shadow-sm text-white relative overflow-hidden">
                            <div class="absolute top-0 right-0 -m-12 w-48 h-48 bg-white/5 rounded-full blur-3xl"></div>
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-3 font-heading">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0014 11V4h-3L9 7l-2-3H4v7a10 10 0 005.183 8.761"></path>
                                </svg>
                                Student Portal Account
                            </h3>
                            <div class="flex items-center justify-between gap-4 mt-4">
                                <div>
                                    <p class="text-indigo-200 text-[10px] font-bold uppercase tracking-wider mb-1">Login Sign-in Email</p>
                                    <p class="font-bold text-base leading-tight break-all">{{ $student->user->email ?? 'N/A' }}</p>
                                </div>
                                <span class="px-3 py-1 bg-white/10 border border-white/20 rounded-xl text-[10px] font-bold uppercase tracking-wider text-indigo-100 flex items-center gap-1.5 flex-shrink-0">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Live Portal
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACADEMICS TAB -->
            <div x-show="tab === 'academic'" x-transition class="space-y-8">
                <!-- Current Enrollment info -->
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-6 lg:p-8 rounded-3xl shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
                        <div class="p-2.5 bg-violet-50 dark:bg-violet-950/40 text-violet-600 dark:text-violet-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.247 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading">Current Enrollment Status</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-450">Active grade registry details</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-indigo-50/50 dark:bg-indigo-950/20 p-4 rounded-2xl border border-indigo-100/30 dark:border-indigo-900/40">
                            <span class="text-[9px] text-indigo-500 dark:text-indigo-400 font-bold uppercase tracking-wider block mb-1">Section</span>
                            <span class="font-extrabold text-base text-slate-800 dark:text-slate-100">{{ $student->currentEnrollment ? $student->currentEnrollment->section->name : 'N/A' }}</span>
                        </div>
                        <div class="bg-violet-50/50 dark:bg-violet-950/20 p-4 rounded-2xl border border-violet-100/30 dark:border-violet-900/40">
                            <span class="text-[9px] text-violet-500 dark:text-violet-400 font-bold uppercase tracking-wider block mb-1">Grade Level</span>
                            <span class="font-extrabold text-base text-slate-800 dark:text-slate-100">{{ $student->currentEnrollment ? $student->currentEnrollment->section->gradeLevel->name : 'N/A' }}</span>
                        </div>
                        <div class="bg-amber-50/50 dark:bg-amber-950/20 p-4 rounded-2xl border border-amber-100/30 dark:border-amber-900/40">
                            <span class="text-[9px] text-amber-600 dark:text-amber-400 font-bold uppercase tracking-wider block mb-1">Roll Number</span>
                            <span class="font-extrabold text-base text-slate-800 dark:text-slate-100">#{{ $student->currentEnrollment ? $student->currentEnrollment->roll_number ?? 'N/A' : 'N/A' }}</span>
                        </div>
                        <div class="bg-emerald-50/50 dark:bg-emerald-950/20 p-4 rounded-2xl border border-emerald-100/30 dark:border-emerald-900/40">
                            <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider block mb-1">Division</span>
                            <span class="font-extrabold text-base text-slate-800 dark:text-slate-100">{{ $student->currentEnrollment ? $student->currentEnrollment->section->gradeLevel->division->name : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Complete Enrollment Ledger -->
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-6 lg:p-8 rounded-3xl shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
                        <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading">Enrollment Ledger</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-450">Institution academic history records</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    <th class="pb-3">Academic Year</th>
                                    <th class="pb-3">Grade & Section</th>
                                    <th class="pb-3">Division</th>
                                    <th class="pb-3 text-center">Roll No</th>
                                    <th class="pb-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                @forelse($student->enrollments->sortByDesc('enrollment_date') as $enrollment)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/35 transition-colors">
                                        <td class="py-4 font-bold text-slate-800 dark:text-slate-200">
                                            {{ $enrollment->academicYear->name }}
                                        </td>
                                        <td class="py-4">
                                            <span class="font-extrabold text-slate-800 dark:text-slate-100">{{ $enrollment->section->gradeLevel->name }}</span>
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-350 rounded-lg text-xs font-black ml-1.5">{{ $enrollment->section->name }}</span>
                                        </td>
                                        <td class="py-4 text-xs font-semibold text-slate-500">
                                            {{ $enrollment->section->gradeLevel->division->name }}
                                        </td>
                                        <td class="py-4 text-center font-black text-slate-700 dark:text-slate-300">
                                            #{{ $enrollment->roll_number ?? '-' }}
                                        </td>
                                        <td class="py-4 text-right">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $enrollment->status == 'active' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                                {{ $enrollment->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-slate-400">
                                            No prior school enrollments logged.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- FAMILY CONNECTIONS TAB -->
            <div x-show="tab === 'guardians'" x-transition class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @forelse($student->guardians as $guardian)
                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 lg:p-8 shadow-sm flex flex-col justify-between relative overflow-hidden group">
                            <div class="flex items-start gap-4">
                                <div class="relative flex-shrink-0">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden shadow-inner flex items-center justify-center">
                                        @if($guardian->photo)
                                            <img src="/storage/{{ $guardian->photo }}" alt="Guardian Photo" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    @if($guardian->is_emergency_contact)
                                        <div class="absolute -top-2 -left-2 w-6 h-6 bg-rose-500 rounded-full border-2 border-white dark:border-slate-900 flex items-center justify-center text-white" title="Emergency Contact">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-grow">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="text-base font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ $guardian->full_name }}</h4>
                                        <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 text-[9px] font-bold uppercase tracking-wider">
                                            {{ $guardian->relationship }}
                                        </span>
                                    </div>
                                    <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-3">{{ ucfirst($guardian->guardian_type) }} Guardian</p>
                                    
                                    <div class="space-y-1.5 text-sm font-semibold">
                                        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-350">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span class="tracking-wide">{{ $guardian->phone }}</span>
                                        </div>
                                        @if($guardian->email)
                                            <div class="flex items-center gap-2 text-slate-600 dark:text-slate-350">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                                <span class="truncate max-w-[180px]">{{ $guardian->email }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[10px] text-slate-400">
                                <span class="flex items-center gap-1.5 {{ $guardian->user_id ? 'text-emerald-500' : 'text-slate-400' }} font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $guardian->user_id ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }}"></span>
                                    Portal Account: {{ $guardian->user_id ? 'Connected' : 'Offline' }}
                                </span>
                                <span class="font-mono">#G{{ str_pad($guardian->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-12 text-center text-slate-400">
                            No related family connections linked.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- HEALTH & COMMUTE TAB -->
            <div x-show="tab === 'medical_transport'" x-transition class="space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Medical Profile Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-6 lg:p-8 rounded-3xl shadow-sm space-y-6">
                        <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div class="p-2.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading">Medical Profile</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-450">Critical health markers on record</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @if($student->medicalInfo)
                                <div class="grid grid-cols-3 gap-4 items-center p-4 rounded-2xl bg-rose-500/5 dark:bg-rose-950/15 border border-rose-500/10">
                                    <div class="col-span-1 text-center border-r border-rose-500/10 dark:border-rose-900/25">
                                        <span class="block text-[8px] font-black text-rose-500 uppercase tracking-widest mb-0.5">Blood Type</span>
                                        <span class="text-2xl font-black text-rose-600 dark:text-rose-455">{{ $student->medicalInfo->blood_group ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-span-2 pl-2">
                                        <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Emergency Contact</span>
                                        <span class="text-sm font-extrabold text-slate-700 dark:text-slate-300 leading-tight block">{{ $student->medicalInfo->emergency_contact ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800/60">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Allergies & Sensitivities</span>
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 leading-normal">{{ $student->medicalInfo->allergies ?: 'No registered allergies.' }}</p>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800/60">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Registered Medical Issues</span>
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 leading-normal">{{ $student->medicalInfo->medical_issues ?: 'No chronic issues on record.' }}</p>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800/60">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Ongoing Medication</span>
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 leading-normal">{{ $student->medicalInfo->current_medication ?: 'No ongoing prescription medications.' }}</p>
                                </div>
                            @else
                                <div class="py-12 text-center text-slate-400">
                                    No chronic medical records logged.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Transportation info -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-6 lg:p-8 rounded-3xl shadow-sm space-y-6">
                        <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading">Commute details</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-450">Official daily bus commuter particulars</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            @if($student->transportation)
                                <!-- Driver & Bus Card -->
                                <div class="p-4 bg-slate-50 dark:bg-slate-950/30 rounded-2xl border border-slate-100 dark:border-slate-850 flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        @if($student->transportation->driver_photo)
                                            <img src="/storage/{{ $student->transportation->driver_photo }}" alt="Driver Photo" class="w-full h-full object-cover">
                                        @else
                                            <div class="text-indigo-400 font-extrabold text-xl">
                                                {{ substr($student->transportation->driver_full_name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow">
                                        <span class="inline-flex px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded text-[8px] font-bold uppercase tracking-wider border border-emerald-500/20 mb-1">Active Commuter</span>
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-150 leading-tight">{{ $student->transportation->driver_full_name }}</h4>
                                        <span class="text-xs text-slate-400 block mt-0.5">Bus Direct Commuter Driver</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800/60">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Vehicle Plate Number</span>
                                        <span class="text-sm font-extrabold text-slate-850 dark:text-slate-200">{{ $student->transportation->vehicle_plate ?: 'Not Specified' }}</span>
                                    </div>
                                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800/60">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Commute Route / Region</span>
                                        <span class="text-sm font-extrabold text-slate-850 dark:text-slate-200">{{ $student->transportation->route ?: 'Not Specified' }}</span>
                                    </div>
                                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850/40 border border-slate-100 dark:border-slate-800/60 sm:col-span-2">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Driver License Registration</span>
                                        <span class="text-sm font-extrabold text-slate-850 dark:text-slate-200">{{ $student->transportation->license_number ?: 'Not Registered' }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="py-12 text-center text-slate-400">
                                    Not registered under the school transport network.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-parent-layout>