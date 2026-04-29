<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure Your Account - {{ config('app.name', 'Renaissance') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50">
    <div class="min-h-screen w-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-indigo-50 to-slate-100 py-12 px-4">
        
        <!-- Animated Background Shapes -->
        <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none -z-10">
            <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-200/30 rounded-full blur-[120px] animate-pulse"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-rose-200/20 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="w-full sm:max-w-[480px] bg-white shadow-[0_32px_64px_-16px_rgba(0,0,0,0.1)] border border-white rounded-[3rem] overflow-hidden relative">
            
            <!-- Header Banner -->
            <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-rose-500"></div>

            <div class="px-8 pt-12 pb-10 sm:px-12">
                <div class="mb-10 text-center">
                    <div class="w-24 h-24 bg-indigo-600 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-indigo-200 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                        <svg class="w-12 h-12 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-3">SECURE YOUR ACCOUNT</h1>
                    <p class="text-slate-500 font-medium text-base">Set a new secure password to continue accessing your dashboard.</p>
                </div>

                @if(session('warning'))
                    <div class="mb-8 p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r-xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-semibold text-amber-900 uppercase tracking-wide">{{ session('warning') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.change-password.update') }}" class="space-y-8">
                    @csrf

                    <!-- Current Password -->
                    <div class="group">
                        <label for="current_password" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.25em] mb-3 ml-2 group-focus-within:text-indigo-600 transition-colors">Current Password</label>
                        <div class="relative">
                            <input id="current_password" type="password" name="current_password" required autofocus
                                   class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 focus:bg-white rounded-2xl transition-all duration-300 font-bold text-slate-900 placeholder-slate-300 focus:ring-0 outline-none shadow-sm"
                                   placeholder="••••••••">
                        </div>
                        @if($errors->get('current_password'))
                            <p class="mt-2 text-xs font-bold text-rose-500 uppercase tracking-wider ml-2">{{ $errors->first('current_password') }}</p>
                        @endif
                    </div>

                    <!-- New Password -->
                    <div class="group">
                        <label for="password" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.25em] mb-3 ml-2 group-focus-within:text-indigo-600 transition-colors">New Secure Password</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" required
                                   class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 focus:bg-white rounded-2xl transition-all duration-300 font-bold text-slate-900 placeholder-slate-300 focus:ring-0 outline-none shadow-sm"
                                   placeholder="••••••••">
                        </div>
                        @if($errors->get('password'))
                            <p class="mt-2 text-xs font-bold text-rose-500 uppercase tracking-wider ml-2">{{ $errors->first('password') }}</p>
                        @endif
                    </div>

                    <!-- Confirm Password -->
                    <div class="group">
                        <label for="password_confirmation" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.25em] mb-3 ml-2 group-focus-within:text-indigo-600 transition-colors">Confirm New Password</label>
                        <div class="relative">
                            <input id="password_confirmation" type="password" name="password_confirmation" required
                                   class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 focus:bg-white rounded-2xl transition-all duration-300 font-bold text-slate-900 placeholder-slate-300 focus:ring-0 outline-none shadow-sm"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" 
                                class="w-full py-5 bg-slate-900 hover:bg-indigo-600 text-white font-black uppercase tracking-[0.3em] text-sm rounded-2xl transition-all duration-500 transform hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-200">
                            UPDATE & CONTINUE
                        </button>
                    </div>
                </form>

                <div class="mt-12 pt-8 border-t border-slate-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-center text-[11px] font-black text-slate-400 hover:text-rose-500 uppercase tracking-[0.3em] transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            LOGOUT & RE-LOGIN LATER
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Footer Info -->
        <p class="mt-8 text-[10px] font-bold text-slate-400 uppercase tracking-[0.5em]">Renaissance SMS Security Protocol v1.0</p>
    </div>
</body>
</html>
