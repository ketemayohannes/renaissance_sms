<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Assign Class & Subject</h2>
            <a href="{{ route('admin.teacher-assignments.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2 shadow-sm border-b-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Assignments
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-8" x-data="{ 
        assignments: {{ json_encode(old('assignments', $existingAssignments)) }},
        selectedDivision: '{{ old('division_id', '') }}',
        sectionsData: {{ json_encode($sectionsData) }}
    }">
        <x-breadcrumb :items="[
            ['label' => 'Teacher Assignments', 'url' => route('admin.teacher-assignments.index')],
            ['label' => 'Assign Class & Subject', 'url' => '#']
        ]" />

        <div class="glass-panel border-white shadow-2xl overflow-hidden rounded-[3rem]">
            <form action="{{ route('admin.teacher-assignments.store') }}" method="POST" class="p-8 space-y-10">
                @csrf

                <!-- Teacher Selector -->
                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Select Teacher</label>
                    <select name="teacher_user_id" required 
                            class="w-full bg-slate-50 border-slate-100 rounded-[1.8rem] py-5 px-8 focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-600 text-sm font-black shadow-inner uppercase tracking-tight">
                        <option value="">SELECT TEACHER</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->user_id }}" {{ old('teacher_user_id', request('teacher_user_id')) == $teacher->user_id ? 'selected' : '' }}>
                                {{ $teacher->full_name }} ({{ $teacher->employee_id }} - {{ $teacher->specialization ?? 'General' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Division Selector -->
                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Filter by Division (Optional)</label>
                    <select name="division_id" x-model="selectedDivision" 
                            class="w-full bg-slate-50 border-slate-100 rounded-[1.8rem] py-5 px-8 focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-600 text-sm font-black shadow-inner uppercase tracking-tight relative z-20">
                        <option value="">ALL DIVISIONS</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Assignment Repeater -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest">Sections & Subjects</h4>
                        <button type="button" @click="assignments.push({section_ids: [], subject_id: ''})" 
                                class="px-4 py-2 bg-indigo-600 text-white text-[10px] font-black rounded-xl hover:bg-indigo-700 transition-all gap-2 shadow-lg shadow-indigo-100 uppercase tracking-widest flex items-center border-b-4 border-indigo-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                            Add Row
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(assignment, index) in assignments" :key="index">
                            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative">
                                <!-- Delete Button -->
                                <button type="button" @click="assignments.splice(index, 1)" 
                                        class="absolute top-6 right-6 p-3 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-2xl transition-all" title="Remove Assignment">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>

                                <!-- Top part: Subject Selection -->
                                <div class="mb-8 pr-16 bg-white">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Subject</label>
                                    <select :name="'assignments['+index+'][subject_id]'" x-model="assignment.subject_id" required
                                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 text-sm shadow-inner focus:ring-4 focus:ring-indigo-600/5 transition-all outline-none">
                                        <option value="">Select Subject</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Bottom part: Sections List -->
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Assign to Classes & Sections</label>
                                    
                                    <div class="space-y-6">
                                        <template x-for="grade in [...new Set(sectionsData.filter(s => selectedDivision === '' || s.division_id == selectedDivision).map(s => s.grade_level_name))]" :key="grade">
                                            <div class="bg-slate-50/50 p-5 rounded-3xl border border-slate-100">
                                                <h5 class="text-xs font-black text-indigo-900 mb-4 uppercase tracking-widest" x-text="grade"></h5>
                                                <div class="flex flex-wrap gap-4">
                                                    <template x-for="section in sectionsData.filter(s => (selectedDivision === '' || s.division_id == selectedDivision) && s.grade_level_name === grade)" :key="section.id">
                                                        <label class="flex items-center gap-3 pr-4 cursor-pointer group">
                                                            <div class="relative flex items-center justify-center">
                                                                <input type="checkbox" :name="'assignments['+index+'][section_ids][]'" :value="section.id" x-model="assignment.section_ids"
                                                                       class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-600 transition-shadow bg-white peer relative z-10 cursor-pointer">
                                                            </div>
                                                            <span class="text-sm font-semibold text-slate-600 peer-checked:text-indigo-800 group-hover:text-indigo-600 transition-colors" x-text="'Section ' + section.name"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="sectionsData.filter(s => selectedDivision === '' || s.division_id == selectedDivision).length === 0" class="text-center py-6">
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">No sections match current division</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        
                        <template x-if="assignments.length === 0">
                            <div class="text-center py-16 bg-slate-50/50 rounded-[2.5rem] border-4 border-dashed border-slate-200">
                                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200 shadow-sm border border-slate-50">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">No Assignments Added</p>
                                <p class="text-xs text-slate-300 font-bold mt-2">Click "Add Row" to assign sections and subjects.</p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-8 flex items-center justify-end gap-4 border-t border-slate-50">
                    <a href="{{ route('admin.teacher-assignments.index') }}" class="px-8 py-4 bg-white border-2 border-slate-200 text-slate-500 text-[10px] font-black rounded-2xl hover:bg-slate-50 transition-all uppercase tracking-widest">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-4 bg-slate-900 text-white text-[10px] font-black rounded-2xl shadow-2xl shadow-slate-300 hover:bg-slate-800 transition-all uppercase tracking-widest border-b-4 border-slate-700">
                        Save Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
