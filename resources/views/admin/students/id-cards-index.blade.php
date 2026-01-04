<x-admin-layout>
    <x-slot name="header">ID Card Generation</x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col32. - **Impact**: The Promotion module is now stable and matches the premium design language.
33. 
34. ### 🪪 ID Card Generation Repair
35. Fixed an `Undefined variable $sections` crash on the ID Cards page. The controller was passing the wrong data, and the view had duplicated layout blocks.
36. - **Fix**: Corrected the route from `id-cards-print` to `bulk-id-cards` and synchronized the data flow.
 md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'ID Cards', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">ID Card Issuance</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.id-card-settings.index') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 rounded-2xl font-bold text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>
                <div class="px-4 py-2 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center gap-3 shadow-sm shadow-indigo-100/50">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-indigo-900 uppercase tracking-widest">Printer Ready</span>
                        <span class="block text-xs font-bold text-indigo-600/80 mt-0.5">Automated Formatting</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Selection Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($sections as $section)
                <a href="{{ route('admin.sections.bulk-id-cards', $section) }}" target="_blank" class="group relative bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white shadow-xl shadow-slate-200/50 hover:bg-slate-900 transition-all hover:-translate-y-2">
                    <!-- Icon/Indicator -->
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 mb-6 group-hover:bg-white/10 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5L12 4l-2 2z"></path></svg>
                    </div>

                    <!-- Content -->
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight group-hover:text-white transition-colors">{{ $section->name }}</h3>
                    <p class="text-xs font-black text-indigo-500 uppercase tracking-widest mt-1 group-hover:text-indigo-300 transition-colors">{{ $section->gradeLevel->name }}</p>
                    
                    <div class="mt-8 flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-slate-500 transition-colors">{{ $section->students_count ?? '0' }} Students</span>
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all transform group-hover:rotate-45">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                    </div>

                    <!-- Decorative Gradient (Hover) -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-500/10 to-transparent rounded-bl-[100px] pointer-events-none group-hover:opacity-0 transition-opacity"></div>
                </a>
            @endforeach
        </div>

        @if($sections->isEmpty())
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-20 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">No Sections Assigned</h2>
                <p class="text-slate-500 font-semibold mt-2">Active academic sections are required for ID card generation.</p>
            </div>
        @endif
    </div>
</x-admin-layout>
