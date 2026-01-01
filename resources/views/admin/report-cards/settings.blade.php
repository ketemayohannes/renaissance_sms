<x-admin-layout>
    <x-slot name="header">Config & Identity</x-slot>

    <div class="space-y-8 pb-20">
        <!-- Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'Interface Configuration', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                    Report Card Engine
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Institutional identity and certification parameters</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50/50 backdrop-blur-md border border-emerald-100 p-6 rounded-[2rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-emerald-900 font-black text-sm uppercase tracking-widest">Update Successful</h3>
                    <p class="text-emerald-700 text-xs font-semibold mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.report-cards.update-settings') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Institutional Identity Section -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-10">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Institutional Identity</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Formal details displayed on official certifications</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2 col-span-full">
                                <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Full Institutional Name</label>
                                <input type="text" name="school_name" value="{{ old('school_name', $settings->school_name) }}" 
                                       class="premium-input w-full" placeholder="e.g., Renaissance Academy International">
                            </div>

                            <div class="space-y-2 col-span-full">
                                <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Physical Address / Headquarters</label>
                                <textarea name="school_address" rows="3" class="premium-input w-full py-4 min-h-[100px]" placeholder="Detailed address information...">{{ old('school_address', $settings->school_address) }}</textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Official Website</label>
                                <input type="text" name="website" value="{{ old('website', $settings->website) }}" 
                                       class="premium-input w-full" placeholder="www.school.edu.et">
                            </div>

                            <div class="space-y-2">
                                <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Direct Telephone</label>
                                <input type="text" name="telephone" value="{{ old('telephone', $settings->telephone) }}" 
                                       class="premium-input w-full" placeholder="+251 ...">
                            </div>

                            <div class="space-y-2">
                                <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Communication Email</label>
                                <input type="email" name="email" value="{{ old('email', $settings->email) }}" 
                                       class="premium-input w-full" placeholder="info@school.edu.et">
                            </div>

                            <div class="space-y-2">
                                <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">P.O. Box</label>
                                <input type="text" name="po_box" value="{{ old('po_box', $settings->po_box) }}" 
                                       class="premium-input w-full" placeholder="Box № ...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interface & Appearance Section -->
                <div class="space-y-8">
                    <!-- Logo & Branding -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-8 overflow-hidden relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-8">
                                <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Logo & Branding</h3>
                            </div>

                            <div class="flex flex-col items-center">
                                <div class="relative group/logo w-full aspect-video bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden mb-6 transition-all hover:border-indigo-400">
                                    @if($settings->logo_path)
                                        <img src="/storage/{{ $settings->logo_path }}" alt="Institutional Logo" class="h-32 w-auto object-contain p-4 group-hover/logo:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="text-center">
                                            <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No Logo Defined</span>
                                        </div>
                                    @endif
                                    <input type="file" name="logo" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                </div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center px-4">Recommended: PNG with transparency, max 2MB. Drag & drop to update.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Display Config -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-inner">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Visibility Controls</h3>
                        </div>

                        <div class="space-y-4">
                            @foreach([
                                ['id' => 'show_rank', 'label' => 'Rank Visibility', 'desc' => 'Display student ranking in section'],
                                ['id' => 'show_conduct', 'label' => 'Conduct Matrix', 'desc' => 'Include behavior assessment grid'],
                                ['id' => 'show_attendance', 'label' => 'Attendance Stats', 'desc' => 'Show term presence/absence data']
                            ] as $toggle)
                            <label class="flex items-start gap-4 p-4 rounded-2xl hover:bg-slate-50 transition-all cursor-pointer group">
                                <div class="relative flex items-center mt-1">
                                    <input type="checkbox" name="{{ $toggle['id'] }}" value="1" {{ ($settings->template_config[$toggle['id']] ?? true) ? 'checked' : '' }} 
                                           class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 transition-all">
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black text-slate-700 uppercase tracking-widest">{{ $toggle['label'] }}</span>
                                    <span class="block text-[9px] font-semibold text-slate-400 uppercase mt-0.5">{{ $toggle['desc'] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Behavior Traits -->
                    <div class="bg-slate-900 rounded-[2.5rem] border border-white shadow-2xl p-8 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-indigo-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-8">
                                <div class="w-10 h-10 rounded-xl bg-white/10 text-white flex items-center justify-center shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944V22m0-19.056c1.1 0 2.1.2 3 .6a11.955 11.955 0 018.618 3.04M12 2.944a11.955 11.955 0 00-8.618 3.04"></path></svg>
                                </div>
                                <h3 class="text-sm font-black text-white uppercase tracking-widest">Behavioral Traits</h3>
                            </div>

                            <div class="space-y-4">
                                @foreach(range(1, 4) as $i)
                                <div class="space-y-2">
                                    <label class="px-1 text-[8px] font-black text-slate-400 uppercase tracking-[0.3em] flex items-center gap-2">
                                        Trait {{ $i }}
                                        <span class="w-1 h-1 rounded-full bg-indigo-500"></span>
                                    </label>
                                    <input type="text" name="traits[{{ $i }}]" value="{{ $settings->template_config['traits'][$i] ?? '' }}" 
                                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm font-semibold text-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none placeholder-white/20"
                                           placeholder="Describe behavior trait...">
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Save Bar -->
            <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 flex items-center gap-4 p-4 bg-white/80 backdrop-blur-2xl rounded-[2.5rem] border border-white shadow-2xl animate-in slide-in-from-bottom-12 duration-500">
                <p class="hidden md:block px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-r border-slate-100 italic">
                    All modifications are permanent upon commitment.
                </p>
                <button type="submit" class="px-12 py-4 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-full hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-900/20 flex items-center gap-2 group">
                    Commit Interface Update
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
