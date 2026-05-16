<x-teacher-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 no-print">
            <span class="w-2 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
            <h2 class="font-heading font-black text-2xl text-slate-900 tracking-tight">
                {{ __('Result Analysis Report') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/60 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-10">
                <div class="mb-10">
                    <h3 class="text-2xl font-black text-slate-900 font-heading uppercase tracking-tight">Generate Result Analysis</h3>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Select a class and term to view or create a result analysis report.</p>
                </div>

                <form action="" id="selectionForm" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-2">
                            <label for="assignment_id" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Class / Subject</label>
                            <select name="assignment_id" id="assignment_id" required
                                    class="w-full rounded-2xl border-slate-100 bg-white/50 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all py-4 px-5 text-sm font-bold text-slate-700">
                                <option value="">-- Select Class --</option>
                                @foreach($assignments as $assignment)
                                    <option value="{{ $assignment->id }}">
                                        {{ $assignment->formatted_label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="term_id" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Term (Quarter)</label>
                            <select name="term_id" id="term_id" required
                                    class="w-full rounded-2xl border-slate-100 bg-white/50 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all py-4 px-5 text-sm font-bold text-slate-700">
                                <option value="">-- Select Term --</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 border-t border-slate-100">
                        <button type="button" onclick="goToReport()"
                                class="px-10 py-5 bg-indigo-600 text-white font-black text-xs uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 shadow-2xl shadow-indigo-200 transition-all active:scale-95 flex items-center gap-3">
                            Continue to Report
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function goToReport() {
            const assignmentId = document.getElementById('assignment_id').value;
            const termId = document.getElementById('term_id').value;

            if (!assignmentId || !termId) {
                alert('Please select both a class and a term.');
                return;
            }

            window.location.href = `{{ url('teacher/reports/result-analysis') }}/${assignmentId}?term_id=${termId}`;
        }
    </script>
</x-teacher-layout>
