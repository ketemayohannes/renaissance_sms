<x-admin-layout>
    <x-slot name="header">Transfer Student: {{ $student->full_name }}</x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => $student->full_name, 'url' => route('admin.students.show', $student)],
            ['label' => 'Transfer', 'url' => '#']
        ]" />

        <!-- Profile Header Section (Decorative) -->
        <div class="relative mb-6">
            <div class="absolute inset-0 h-32 bg-gradient-to-r from-indigo-600 to-violet-600 rounded-[2.5rem] opacity-10 blur-2xl -z-10"></div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-6 flex flex-col md:flex-row items-center gap-6">
                <div class="w-20 h-20 rounded-2xl bg-indigo-50 border-2 border-white shadow-sm overflow-hidden">
                    @if($student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}" alt="Student Photo" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-indigo-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    @endif
                </div>
                <div class="text-center md:text-left">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Transfer Student</h1>
                    <p class="text-slate-500 font-semibold text-sm">{{ $student->full_name }} • {{ $student->student_id }}</p>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Current Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Current Enrollment</h3>
                    </div>

                    @if($currentEnrollment)
                        <div class="space-y-6">
                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Grade Level</span>
                                <span class="font-bold text-slate-700">{{ $currentEnrollment->section?->gradeLevel->name ?? 'Unassigned' }}</span>
                            </div>
                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Section</span>
                                <span class="font-bold text-slate-700">{{ $currentEnrollment->section->name ?? 'No section yet' }}</span>
                            </div>
                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Enrollment Date</span>
                                <span class="font-bold text-slate-700">{{ $currentEnrollment->enrollment_date }}</span>
                            </div>
                            <div class="p-4 bg-emerald-50/30 rounded-2xl border border-emerald-100/30">
                                <span class="text-[10px] text-emerald-500 font-black uppercase tracking-widest block mb-1">Student Status</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $student->is_active ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                                    {{ $student->is_active ? 'Active' : 'Blocked' }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-rose-50 text-rose-600 rounded-2xl border border-rose-100 text-center font-bold">
                            No active enrollment found.
                        </div>
                    @endif
                </div>

                <!-- Warning Notice -->
                <div class="bg-amber-50 rounded-[2.5rem] border border-amber-100/50 p-8">
                    <div class="flex items-center gap-3 mb-4 text-amber-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-lg font-bold">Important Notice</h3>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex gap-2 text-sm text-amber-800 font-medium">
                            <span class="text-amber-400">•</span>
                            A new enrollment record will be created.
                        </li>
                        <li class="flex gap-2 text-sm text-amber-800 font-medium">
                            <span class="text-amber-400">•</span>
                            Current enrollment will be closed.
                        </li>
                        <li class="flex gap-2 text-sm text-amber-800 font-medium">
                            <span class="text-amber-400">•</span>
                            This action cannot be easily undone.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right Column: Transfer Form -->
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm border border-amber-100/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Transfer Details</h3>
                    </div>

                    @if(session('error'))
                        <div class="bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl mb-6 font-bold text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(!$activeYear)
                        <div class="bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl mb-6 font-bold text-sm">
                            <strong>Warning!</strong> No active academic year found.
                        </div>
                    @endif

                    <form action="{{ route('admin.students.transfer.store', $student) }}" method="POST" class="space-y-8">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="new_section_id" class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">New Section *</label>
                                <select name="new_section_id" id="new_section_id" class="w-full bg-slate-50/50 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-slate-700 h-12" required>
                                    <option value="">Select Target Section</option>
                                    @foreach($sections->groupBy('gradeLevel.name') as $gradeName => $gradeSections)
                                        <optgroup label="{{ $gradeName }}">
                                            @foreach($gradeSections as $section)
                                                <option value="{{ $section->id }}" {{ old('new_section_id') == $section->id ? 'selected' : '' }}>
                                                    {{ $section->name }} ({{ $section->gradeLevel->division->name }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('new_section_id')<span class="text-rose-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>@enderror
                            </div>

                            <div>
                                <label for="transfer_date" class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Transfer Date *</label>
                                <input type="date" name="transfer_date" id="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" class="w-full bg-slate-50/50 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-slate-700 h-12" required>
                                @error('transfer_date')<span class="text-rose-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="reason" class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Reason for Transfer (Optional)</label>
                                <textarea name="reason" id="reason" rows="4" class="w-full bg-slate-50/50 border-slate-100 rounded-[2rem] focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-slate-700 p-4" placeholder="e.g., Parent request, academic performance, behavioral issues, etc.">{{ old('reason') }}</textarea>
                                @error('reason')<span class="text-rose-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-50">
                            <a href="{{ route('admin.students.show', $student) }}" class="px-8 py-4 bg-slate-100 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">
                                Cancel
                            </a>
                            <button type="submit" class="px-10 py-4 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-white hover:text-indigo-600 hover:ring-2 hover:ring-indigo-600 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                Transfer Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
