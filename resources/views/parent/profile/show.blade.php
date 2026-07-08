<x-parent-layout header="My Profile">
    <div class="space-y-8 max-w-6xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'My Profile', 'url' => '#']
        ]" />

        <!-- Profile Header / Hero Card -->
        <div class="relative bg-gradient-to-r from-indigo-900 to-slate-900 dark:from-slate-900 dark:to-slate-950 rounded-3xl p-6 lg:p-8 text-white overflow-hidden shadow-xl border border-slate-100/10">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-xl"></div>
            <div class="absolute -right-20 -bottom-20 w-60 h-60 bg-indigo-500/20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
                <!-- Avatar -->
                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-extrabold text-3xl shadow-lg border border-white/20">
                    {{ substr($user->name, 0, 1) }}
                </div>
                
                <!-- Account Info -->
                <div class="flex-1 text-center sm:text-left space-y-2">
                    <div class="flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                        <h2 class="text-2xl font-bold font-heading tracking-tight text-slate-100">{{ $user->name }}</h2>
                        <span class="px-2.5 py-0.5 bg-indigo-500/20 text-indigo-300 rounded-full text-[10px] font-bold uppercase tracking-wider border border-indigo-500/30">
                            Parent Account
                        </span>
                    </div>
                    <p class="text-sm text-slate-450 dark:text-slate-400 flex items-center justify-center sm:justify-start gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ $user->email }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-500">
                        Registered Member since {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-250 dark:bg-rose-950/30 dark:border-rose-800/50 p-4 rounded-2xl flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="space-y-1">
                    <h5 class="text-sm font-bold text-rose-800 dark:text-rose-455">Validation Errors Detected</h5>
                    <ul class="list-disc list-inside text-xs text-rose-700 dark:text-rose-400 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Profile Modules Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Side: Communication Preferences -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 lg:p-8 shadow-sm flex flex-col justify-between">
                <div>
                    <!-- Section Header -->
                    <div class="flex items-center gap-3 pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-150 font-heading">Alerts & Preferences</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-450">Customize notification methods and frequencies</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('parent.preferences.update') }}" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <!-- Toggle Options -->
                        <div class="space-y-4">
                            <!-- Toggle 1: Email -->
                            <div class="flex items-start justify-between p-4 bg-slate-50 dark:bg-slate-850/45 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/40 transition-colors border border-slate-50 dark:border-slate-800">
                                <div class="flex-1 pr-4">
                                    <span class="block text-sm font-bold text-slate-805 dark:text-slate-200">Email Notifications</span>
                                    <span class="block text-xs text-slate-450 dark:text-slate-400 mt-1">Receive weekly academic progress logs, attendance summaries, and official report notices.</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1 select-none">
                                    <input type="checkbox" name="preferences[email_notifications]" value="1" {{ ($user->preferences['email_notifications'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none dark:bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            <!-- Toggle 2: Attendance alerts (simulated/stored inside json) -->
                            <div class="flex items-start justify-between p-4 bg-slate-50 dark:bg-slate-850/45 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/40 transition-colors border border-slate-50 dark:border-slate-800">
                                <div class="flex-1 pr-4">
                                    <span class="block text-sm font-bold text-slate-805 dark:text-slate-200">Instant Attendance Alerts</span>
                                    <span class="block text-xs text-slate-450 dark:text-slate-400 mt-1">Get immediate alerts when your child is marked absent or late by their homeroom teacher.</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1 select-none">
                                    <input type="checkbox" name="preferences[attendance_alerts]" value="1" {{ ($user->preferences['attendance_alerts'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none dark:bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            <!-- Toggle 3: Disciplinary alerts (simulated/stored inside json) -->
                            <div class="flex items-start justify-between p-4 bg-slate-50 dark:bg-slate-850/45 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/40 transition-colors border border-slate-50 dark:border-slate-800">
                                <div class="flex-1 pr-4">
                                    <span class="block text-sm font-bold text-slate-805 dark:text-slate-200">Conduct Report Alerts</span>
                                    <span class="block text-xs text-slate-450 dark:text-slate-400 mt-1">Receive priority notifications when conduct logs or behavioral reports are filed.</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1 select-none">
                                    <input type="checkbox" name="preferences[disciplinary_alerts]" value="1" {{ ($user->preferences['disciplinary_alerts'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none dark:bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-indigo-200 dark:shadow-none hover:shadow-lg">
                            Save Preferences
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Side: Security & Credentials -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 lg:p-8 shadow-sm flex flex-col justify-between">
                <div>
                    <!-- Section Header -->
                    <div class="flex items-center gap-3 pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <div class="p-2.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-150 font-heading">Security Credentials</h3>
                            <p class="text-xs text-slate-450 dark:text-slate-450">Change your portal account login credentials</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('parent.password.update') }}" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <!-- Current Password Input -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Current Password</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </span>
                                <input type="password" name="current_password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 border border-slate-100 dark:border-slate-800 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-400 transition-colors">
                            </div>
                        </div>

                        <!-- New Password Input -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">New Password</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-5-4v12m0 0l-4-4m4 4l4-4M3 4h18"></path>
                                    </svg>
                                </span>
                                <input type="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 border border-slate-100 dark:border-slate-800 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-400 transition-colors">
                            </div>
                        </div>

                        <!-- Confirm Password Input -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Confirm Password</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                                <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 border border-slate-100 dark:border-slate-800 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-400 transition-colors">
                            </div>
                        </div>

                        <!-- Help Banner -->
                        <div class="p-3 bg-slate-50 dark:bg-slate-850/30 rounded-xl border border-slate-100 dark:border-slate-800 text-[10px] text-slate-450 leading-relaxed">
                            Password must contain at least <span class="font-bold text-indigo-600 dark:text-indigo-400">8 characters</span> and should include a combination of letters, numbers, and symbols.
                        </div>

                        <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-rose-200 dark:shadow-none hover:shadow-lg">
                            Update Credentials
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-parent-layout>