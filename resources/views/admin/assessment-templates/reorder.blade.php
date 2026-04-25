<x-admin-layout>
    <x-slot name="header">Reorder Assessments</x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Assessment Templates', 'url' => route('admin.assessment-templates.index')],
                    ['label' => 'Reorder', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Reorder Logic</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.assessment-templates.index') }}" class="px-6 py-3 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-xl border border-slate-200 hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2 group">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </a>
            </div>
        </div>

        <!-- Filter Context -->
        <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-200">
            <form action="{{ route('admin.assessment-templates.reorder') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div class="space-y-2">
                    <label for="academic_year_id" class="text-[10px] font-black text-indigo-200 uppercase tracking-widest ml-1">Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" class="w-full bg-white/10 border-white/20 rounded-2xl focus:ring-white focus:border-white font-bold text-sm py-3 px-4 transition-all text-white" onchange="this.form.submit()">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" class="text-slate-900" {{ $academicYearId == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="term_id" class="text-[10px] font-black text-indigo-200 uppercase tracking-widest ml-1">Academic Term</label>
                    <select name="term_id" id="term_id" class="w-full bg-white/10 border-white/20 rounded-2xl focus:ring-white focus:border-white font-bold text-sm py-3 px-4 transition-all text-white" onchange="this.form.submit()">
                        <option value="" class="text-slate-900">All Terms / Global</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" class="text-slate-900" {{ $termId == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="grade_level_id" class="text-[10px] font-black text-indigo-200 uppercase tracking-widest ml-1">Grade Level</label>
                    <select name="grade_level_id" id="grade_level_id" class="w-full bg-white/10 border-white/20 rounded-2xl focus:ring-white focus:border-white font-bold text-sm py-3 px-4 transition-all text-white" onchange="this.form.submit()">
                        <option value="" class="text-slate-900">All Grade Levels</option>
                        @foreach($gradeLevels as $grade)
                            <option value="{{ $grade->id }}" class="text-slate-900" {{ $gradeLevelId == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-[10px] font-medium text-indigo-100 italic pb-3">
                    Reordering templates here affects their display sequence in the Gradebook.
                </div>
            </form>
        </div>

        @if($templates->count() > 0)
        <form action="{{ route('admin.assessment-templates.update-order') }}" method="POST">
            @csrf
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr>
                                <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Assessment Template</th>
                                <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Type</th>
                                <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center w-40">Display Order</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($templates as $template)
                                <tr class="group hover:bg-slate-50/50 transition-all">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-xs group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                            </div>
                                            <span class="font-bold text-slate-700 text-lg">{{ $template->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="px-3 py-1 bg-slate-100 rounded-lg text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                            {{ $template->assessmentType->name }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <input type="number" name="orders[{{ $template->id }}]" value="{{ (int)$template->order }}" 
                                               class="w-24 bg-white border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-black text-center text-lg py-3 transition-all"
                                               min="0">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-8 bg-slate-50/50 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-10 py-4 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200 flex items-center gap-3 group">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Save Custom Order
                    </button>
                </div>
            </div>
        </form>
        @else
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-20 text-center">
            <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <p class="text-slate-400 font-bold text-xl">No templates found for this context</p>
            <p class="text-slate-300 text-sm mt-2">Try changing the academic year or term filter above.</p>
        </div>
        @endif
    </div>
</x-admin-layout>
