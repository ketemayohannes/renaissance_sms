<x-guest-layout>
    <div class="min-h-screen w-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-indigo-50 to-slate-100 py-12 px-4">
        <div class="w-full sm:max-w-md px-10 py-12 bg-white shadow-[0_20px_50px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden sm:rounded-[2.5rem] relative">
            
            <!-- Background Decorative Elements -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-48 h-48 bg-rose-50 rounded-full blur-3xl opacity-50"></div>

            <div class="relative z-10">
                <div class="mb-10 text-center">
                    <div class="w-20 h-20 bg-indigo-600 rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-xl shadow-indigo-200 rotate-3">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-2">Secure Your Account</h2>
                    <p class="text-slate-500 font-medium text-sm px-4">You are using a temporary password. Please set a new secure password to continue.</p>
                </div>

                <form method="POST" action="{{ route('auth.change-password.update') }}" class="space-y-6">
                    @csrf

                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Current Password</label>
                        <input id="current_password" type="password" name="current_password" required autofocus
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl transition-all duration-300 font-medium text-slate-900 placeholder-slate-400 focus:ring-0 outline-none shadow-sm"
                               placeholder="••••••••">
                        <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">New Secure Password</label>
                        <input id="password" type="password" name="password" required
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl transition-all duration-300 font-medium text-slate-900 placeholder-slate-400 focus:ring-0 outline-none shadow-sm"
                               placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Confirm New Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl transition-all duration-300 font-medium text-slate-900 placeholder-slate-400 focus:ring-0 outline-none shadow-sm"
                               placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full py-4 bg-slate-900 hover:bg-indigo-600 text-white font-black uppercase tracking-[0.2em] text-xs rounded-2xl transition-all duration-500 transform hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-200">
                            Update & Continue
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-8 border-t border-slate-100 flex items-center justify-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-[10px] font-black text-slate-400 hover:text-rose-500 uppercase tracking-widest transition-colors">
                            Logout & Re-login Later
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
