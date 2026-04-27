<x-admin-layout>
    <x-slot name="header">Matrix Subject Reordering</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
            ['label' => 'School Matrix', 'url' => '#'],
            ['label' => 'Subject Order', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Matrix Subject Configuration</h2>
                        <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-widest italic">Define the horizontal column order for the School Matrix report</p>
                    </div>
                    <div class="flex items-center gap-2 bg-indigo-50 px-4 py-2 rounded-xl border border-indigo-100">
                        <label for="grade_filter" class="text-xs font-black text-indigo-900 uppercase tracking-widest">Filter Grade:</label>
                        <select id="grade_filter" class="rounded-lg border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-bold py-1">
                            <option value="">ALL SUBJECTS</option>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-700 font-bold text-sm shadow-sm animate-fade-in">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.academic-reports.matrix-reorder.update') }}" method="POST">
                    @csrf
                    
                    <div id="subject_grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
                        @foreach($subjects->sortBy('name') as $subject)
                            <div class="subject-card group bg-slate-50 hover:bg-white p-4 rounded-2xl border border-slate-100 hover:border-indigo-400 hover:shadow-xl hover:shadow-indigo-50 transition-all duration-300" 
                                 data-grade-levels="{{ json_encode($subject->gradeLevels->pluck('id')) }}">
                                <div class="flex items-center gap-4">
                                    <div class="w-16">
                                        <input 
                                            name="subject_order[{{ $subject->id }}]" 
                                            type="number" 
                                            class="w-full text-center px-1 py-2 text-sm font-black text-indigo-600 bg-white border-2 border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-0 transition-colors" 
                                            value="{{ old('subject_order.' . $subject->id, $settings->display_options['matrix_subject_order'][$subject->id] ?? '') }}"
                                            placeholder="--"
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-black text-slate-900 uppercase tracking-tight truncate">{{ $subject->name }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $subject->code }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-8 border-t border-slate-100">
                        <a href="{{ url()->previous() }}" class="px-8 py-4 bg-slate-100 text-slate-500 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">Cancel</a>
                        <button type="submit" class="px-10 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all active:scale-95">
                            Update Matrix Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filter = document.getElementById('grade_filter');
            const cards = document.querySelectorAll('.subject-card');
            
            filter.addEventListener('change', function() {
                const gradeId = this.value;
                
                cards.forEach(card => {
                    if (!gradeId) {
                        card.style.display = 'block';
                    } else {
                        const gradeLevels = JSON.parse(card.dataset.gradeLevels);
                        if (gradeLevels.includes(parseInt(gradeId))) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            });
        });
    </script>
    @endpush
</x-admin-layout>
