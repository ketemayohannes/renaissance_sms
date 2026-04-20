<x-admin-layout>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Management', 'url' => '#'],
                    ['label' => 'Timetable', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Timetable Management</h1>
                <p class="text-slate-500 font-semibold mt-1">Select a class section to build or view its weekly schedule.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-5 py-2.5 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                    <span class="text-xs font-black text-indigo-700 uppercase tracking-widest">Active Year: {{ $academicYear->name }}</span>
                </div>
            </div>
        </div>

        <!-- Section Selection Grid -->
        <div class="grid grid-cols-1 gap-12">
            @foreach($sections as $gradeLevel => $gradeSections)
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">{{ $gradeLevel }}</h2>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                        @foreach($gradeSections as $section)
                            <a href="{{ route('admin.timetable.builder', ['section_id' => $section->id]) }}" 
                               class="group bg-white/60 backdrop-blur-xl border border-white p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 hover:scale-[1.02] hover:shadow-2xl transition-all relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 group-hover:scale-110 transition-transform"></div>
                                
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight leading-none">Section {{ $section->name }}</h3>
                                    <p class="text-xs font-bold text-slate-400 mt-2 uppercase tracking-widest">{{ $section->gradeLevel->name }}</p>
                                    
                                    <div class="mt-6 flex items-center justify-between">
                                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest leading-none">Manage Schedule</span>
                                        <svg class="w-4 h-4 text-indigo-600 translate-x-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>
