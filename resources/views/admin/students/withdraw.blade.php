<x-admin-layout>
    <x-slot name="header">Withdraw Student: {{ $student->full_name }}</x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => $student->full_name, 'url' => route('admin.students.show', $student)],
            ['label' => 'Withdraw', 'url' => '#']
        ]" />

        <!-- Profile Header Section (Decorative) -->
        <div class="relative mb-6">
            <div class="absolute inset-0 h-32 bg-gradient-to-r from-rose-600 to-orange-600 rounded-[2.5rem] opacity-10 blur-2xl -z-10"></div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-6 flex flex-col md:flex-row items-center gap-6">
                <div class="w-20 h-20 rounded-2xl bg-rose-50 border-2 border-white shadow-sm overflow-hidden">
                    @if($student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}" alt="Student Photo" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-rose-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    @endif
                </div>
                <div class="text-center md:text-left">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Withdraw Student</h1>
                    <p class="text-slate-500 font-semibold text-sm">{{ $student->full_name }} • {{ $student->student_id }}</p>
                </div>
            </div>
        </div>
        
        <div class="max-w-4xl mx-auto">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8 overflow-hidden relative">
                <!-- Warning Notice -->
                <div class="mb-8 p-6 bg-rose-600 rounded-[2rem] text-white shadow-lg shadow-rose-100 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -m-8 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="flex items-start gap-4 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-black text-lg mb-1 tracking-tight uppercase">Critical Warning</h4>
                            <p class="text-rose-50 text-sm font-medium leading-relaxed">
                                This action will mark the student as no longer active and close their current enrollment. 
                                This is a permanent status change only intended for students officially leaving the school.
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.students.withdraw.store', $student) }}" method="POST" class="space-y-8">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label for="new_status" class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1 ml-1">New Status *</label>
                            <select name="new_status" id="new_status" class="w-full bg-slate-50/50 border-slate-100 rounded-2xl focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all font-bold text-slate-700 h-12" required>
                                <option value="">Select Target Status</option>
                                <option value="withdrawn">Withdrawn</option>
                                <option value="graduated">Graduated</option>
                                <option value="transferred">Transferred</option>
                                <option value="dropped_out">Dropped Out</option>
                            </select>
                            @error('new_status')<span class="text-rose-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label for="reason" class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1 ml-1">Withdrawal Reason *</label>
                            <select name="reason" id="reason" class="w-full bg-slate-50/50 border-slate-100 rounded-2xl focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all font-bold text-slate-700 h-12" required>
                                <option value="">Select Reason</option>
                                @foreach($reasons as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('reason')<span class="text-rose-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="effective_date" class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1 ml-1">Effective Date *</label>
                            <input type="date" name="effective_date" id="effective_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50/50 border-slate-100 rounded-2xl focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all font-bold text-slate-700 h-12" required>
                            @error('effective_date')<span class="text-rose-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1 ml-1">Additional Notes</label>
                            <textarea name="notes" id="notes" rows="4" class="w-full bg-slate-50/50 border-slate-100 rounded-[2rem] focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all font-bold text-slate-700 p-4" placeholder="Optional notes about this withdrawal..."></textarea>
                            @error('notes')<span class="text-rose-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-50">
                        <a href="{{ route('admin.students.show', $student) }}" class="px-8 py-4 bg-slate-100 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">
                            Cancel
                        </a>
                        <button type="submit" class="px-10 py-4 bg-rose-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-white hover:text-rose-600 hover:ring-2 hover:ring-rose-600 transition-all shadow-lg shadow-rose-100 flex items-center gap-2 group">
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Confirm Withdrawal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
