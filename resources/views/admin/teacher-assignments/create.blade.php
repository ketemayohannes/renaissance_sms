<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Deploy Instruction</h2>
            <a href="{{ route('admin.teacher-assignments.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2 shadow-sm border-b-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Matrix
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-8" x-data="{ assignments: {{ json_encode(old('assignments', [])) }} }">
        <x-breadcrumb :items="[
            ['label' => 'Teacher Assignments', 'url' => route('admin.teacher-assignments.index')],
            ['label' => 'Deploy Instruction', 'url' => '#']
        ]" />

        <div class="glass-panel border-white shadow-2xl overflow-hidden rounded-[3rem]">
            <form action="{{ route('admin.teacher-assignments.store') }}" method="POST" class="p-8 space-y-10">
                @csrf

                <!-- Teacher Selector -->
                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Select Target Personnel</label>
                    <select name="teacher_user_id" required 
                            class="w-full bg-slate-50 border-slate-100 rounded-[1.8rem] py-5 px-8 focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-600 text-sm font-black shadow-inner uppercase tracking-tight">
                        <option value="">SELECT TEACHER</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->user_id }}" {{ old('teacher_user_id') == $teacher->user_id ? 'selected' : '' }}>
                                {{ $teacher->full_name }} ({{ $teacher->employee_id }} - {{ $teacher->specialization ?? 'General' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Assignment Repeater -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest">Load Configuration</h4>
                        <button type="button" @click="assignments.push({section_id: '', subject_id: ''})" 
                                class="px-4 py-2 bg-indigo-600 text-white text-[10px] font-black rounded-xl hover:bg-indigo-700 transition-all gap-2 shadow-lg shadow-indigo-100 uppercase tracking-widest flex items-center border-b-4 border-indigo-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                            Add Load Unit
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(assignment, index) in assignments" :key="index">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 bg-slate-50/50 p-6 rounded-[2rem] border border-slate-100 items-end">
                                <div class="md:col-span-5">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Grade / Section</label>
                                    <select :name="'assignments['+index+'][section_id]'" x-model="assignment.section_id" required
                                            class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl font-bold text-slate-700 text-xs shadow-sm focus:ring-4 focus:ring-indigo-600/5 transition-all">
                                        <option value="">Select Section</option>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->gradeLevel->name }} - {{ $section->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-5">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Academic Subject</label>
                                    <select :name="'assignments['+index+'][subject_id]'" x-model="assignment.subject_id" required
                                            class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl font-bold text-slate-700 text-xs shadow-sm focus:ring-4 focus:ring-indigo-600/5 transition-all">
                                        <option value="">Select Subject</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <button type="button" @click="assignments.splice(index, 1)" 
                                            class="w-full py-4 bg-white text-rose-500 rounded-2xl hover:bg-rose-50 transition-all flex items-center justify-center border border-rose-100 shadow-sm shadow-rose-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        
                        <template x-if="assignments.length === 0">
                            <div class="text-center py-16 bg-slate-50/50 rounded-[2.5rem] border-4 border-dashed border-slate-200">
                                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200 shadow-sm border border-slate-50">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Instructional Matrix Empty</p>
                                <p class="text-xs text-slate-300 font-bold mt-2">Initialize by adding specific subject-section loads.</p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-8 flex items-center justify-end gap-4 border-t border-slate-50">
                    <a href="{{ route('admin.teacher-assignments.index') }}" class="px-8 py-4 bg-white border-2 border-slate-200 text-slate-500 text-[10px] font-black rounded-2xl hover:bg-slate-50 transition-all uppercase tracking-widest">
                        Abort Mission
                    </a>
                    <button type="submit" class="px-10 py-4 bg-slate-900 text-white text-[10px] font-black rounded-2xl shadow-2xl shadow-slate-300 hover:bg-slate-800 transition-all uppercase tracking-widest border-b-4 border-slate-700">
                        Commit Deployment
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
