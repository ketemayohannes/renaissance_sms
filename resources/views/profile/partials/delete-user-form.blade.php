<section class="space-y-6">
    <header class="relative z-10">
        <h2 class="text-xl font-black text-rose-600 tracking-tight flex items-center gap-3">
            <span class="w-1.5 h-6 bg-rose-600 rounded-full shadow-[0_0_15px_rgba(225,29,72,0.4)]"></span>
            {{ __('Danger Zone') }}
        </h2>

        <p class="mt-2 text-sm text-slate-500 font-medium max-w-xl italic">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <div class="relative z-10">
        <button x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" 
            class="vibrant-btn-rose group px-8 py-4">
            <svg class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            {{ __('Delete Account Permanently') }}
        </button>
    </div>

    @push('modals')
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-12">
            @csrf
            @method('delete')

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8">
                <!-- Icon Bubble -->
                <div class="flex-shrink-0 flex items-center justify-center h-20 w-20 rounded-[2rem] vibrant-gradient-rose text-white shadow-2xl shadow-rose-200/50">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div class="flex-1 text-center sm:text-left">
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-tight">
                        {{ __('Are you sure?') }}
                    </h2>

                    <p class="mt-3 text-slate-500 font-medium leading-relaxed">
                        {{ __('This action is irreversible. All your data will be purged from our servers. Please enter your password to authorize this request.') }}
                    </p>

                    <div class="mt-8">
                        <x-input-label for="password" value="{{ __('AUTHORIZATION PASSWORD') }}" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1" />

                        <div class="relative group">
                            <x-text-input
                                id="password"
                                name="password"
                                type="password"
                                class="w-full bg-slate-50/50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-rose-500 focus:border-rose-500 text-sm font-black shadow-inner transition-all hover:border-rose-200"
                                placeholder="{{ __('Enter password to confirm...') }}"
                            />
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-rose-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                        </div>

                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-3" />
                    </div>

                    <div class="mt-10 flex flex-col sm:flex-row-reverse gap-4">
                        <button type="submit" class="w-full sm:flex-1 py-5 bg-slate-900 text-white font-black text-[11px] uppercase tracking-[0.2em] rounded-[1.5rem] hover:bg-rose-600 shadow-2xl transition-all active:scale-95 flex items-center justify-center gap-2 group">
                            {{ __('Delete Account') }}
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </button>

                        <button type="button" x-on:click="$dispatch('close')" class="w-full sm:flex-1 py-5 bg-white border border-slate-100 text-slate-400 font-black text-[11px] uppercase tracking-[0.2em] rounded-[1.5rem] hover:bg-slate-50 hover:text-slate-600 transition-all active:scale-95">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </x-modal>
    @endpush
</section>
