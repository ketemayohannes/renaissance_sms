<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Curriculum Reorder</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.subjects.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                    Back to Curriculum
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Breadcrumbs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-30">
            <x-breadcrumb :items="[
                ['label' => 'Subjects', 'url' => route('admin.subjects.index')],
                ['label' => 'Reorder', 'url' => '#']
            ]" />
        </div>
        
        <!-- Grade Level Filter Card -->
        <div class="glass-panel p-6 border border-white/40 shadow-2xl relative z-20">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Academic Grade Filter</h4>
                        <p class="text-xs text-slate-400">Choose a grade level to organize subjects</p>
                    </div>
                </div>
                <form id="filter-form" action="{{ route('admin.subjects.reorder') }}" method="GET" class="w-full md:w-auto">
                    <select name="grade_level_id" id="grade_level_selector" class="block w-full md:w-80 rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold text-slate-700 p-3.5 bg-slate-50 hover:bg-slate-100 transition-colors cursor-pointer" onchange="this.form.submit()">
                        <option value="">-- Choose a Grade Level --</option>
                        @foreach($gradeLevels as $grade)
                            <option value="{{ $grade->id }}" {{ $selectedGradeId == $grade->id ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        @if($selectedGradeId)
            <!-- Reorder List Card -->
            <div class="glass-panel p-6 border border-white/40 shadow-2xl relative z-10 animate-fade-in">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-wider">Subject Ordering Hierarchy</h3>
                        <p class="text-xs text-slate-400 mt-1">Drag and drop rows or use up/down buttons to set the display sequence for <strong>{{ $gradeLevels->find($selectedGradeId)->name }}</strong>.</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center rounded-xl bg-indigo-500/10 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-indigo-600 border border-indigo-500/20">
                            {{ count($subjects) }} Subjects Linked
                        </span>
                    </div>
                </div>

                <form action="{{ route('admin.subjects.update-order') }}" method="POST" id="reorder-form">
                    @csrf
                    <input type="hidden" name="grade_level_id" value="{{ $selectedGradeId }}">
                    
                    <div id="sortable-list" class="space-y-3 mb-8">
                        @forelse($subjects as $index => $subject)
                            <div class="sortable-item flex items-center justify-between p-4 bg-white border border-slate-200/80 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-300 transition-all group" draggable="true" data-id="{{ $subject->id }}">
                                <div class="flex items-center gap-4">
                                    <!-- Drag Handle -->
                                    <div class="drag-handle text-slate-300 hover:text-indigo-500 cursor-grab p-1.5 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path>
                                        </svg>
                                    </div>
                                    <!-- Number Indicator -->
                                    <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 text-slate-500 font-black text-xs index-indicator transition-colors">
                                        {{ $index + 1 }}
                                    </div>
                                    <!-- Subject Info -->
                                    <div class="flex items-center gap-3">
                                        <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors tracking-tight">{{ $subject->name }}</span>
                                        <span class="px-2.5 py-0.5 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $subject->code }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <!-- Reorder Controls -->
                                    <button type="button" class="btn-move-up p-2 bg-slate-50 hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 rounded-xl transition-all border border-slate-100 cursor-pointer disabled:opacity-30 disabled:pointer-events-none" title="Move Up">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="btn-move-down p-2 bg-slate-50 hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 rounded-xl transition-all border border-slate-100 cursor-pointer disabled:opacity-30 disabled:pointer-events-none" title="Move Down">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <!-- Hidden Input to post the sort order -->
                                    <input type="hidden" name="orders[{{ $subject->id }}]" value="{{ $subject->pivot->sort_order }}" class="sort-order-input">
                                </div>
                            </div>
                        @empty
                            <div class="py-16 text-center text-slate-500 bg-slate-50/50 border border-dashed border-slate-200 rounded-2xl p-8 max-w-lg mx-auto">
                                <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V4a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="font-black text-slate-800 uppercase tracking-widest block text-sm mb-1">No Subjects Assigned</span>
                                <span class="text-xs text-slate-400">Go to Grade Levels configuration to assign subjects first.</span>
                            </div>
                        @endforelse
                    </div>

                    @if($subjects->isNotEmpty())
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Changes are staged. Save to commit new order sequence.
                            </span>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.subjects.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                                    Cancel
                                </a>
                                <button type="submit" class="vibrant-btn-blue shadow-lg hover:shadow-indigo-500/20 active:scale-95 transition-all">
                                    Save Ordered Sequence
                                </button>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        @else
            <!-- Empty State Card -->
            <div class="py-16 text-center glass-panel bg-slate-50/50 border border-slate-100 rounded-3xl p-8 max-w-lg mx-auto">
                <div class="relative w-16 h-16 mx-auto mb-6 flex items-center justify-center bg-indigo-50 rounded-2xl text-indigo-500">
                    <div class="absolute inset-0 bg-indigo-500 rounded-2xl animate-ping opacity-15"></div>
                    <svg class="w-8 h-8 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-md font-black text-slate-800 uppercase tracking-widest mb-2">No Grade Level Selected</h3>
                <p class="text-sm text-slate-500 leading-relaxed max-w-sm mx-auto">Please select an academic grade level from the dropdown selector above to adjust subject layout orders.</p>
            </div>
        @endif
    </div>

    <style>
        .sortable-item {
            transition: transform 0.2s ease, border-color 0.2s ease, shadow 0.2s ease, opacity 0.2s ease;
        }
        .sortable-item.dragging {
            opacity: 0.4;
            transform: scale(0.98);
            border-style: dashed;
        }
    </style>

    @if($selectedGradeId && $subjects->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const list = document.getElementById('sortable-list');
            let dragEl = null;

            function updateOrders() {
                const items = list.querySelectorAll('.sortable-item');
                items.forEach((item, index) => {
                    // Update index indicator
                    const indicator = item.querySelector('.index-indicator');
                    if (indicator) {
                        indicator.textContent = index + 1;
                    }
                    // Update input sort order value (increments of 10 for clean spacing)
                    const input = item.querySelector('.sort-order-input');
                    if (input) {
                        input.value = (index + 1) * 10;
                    }
                    
                    // Enable/disable navigation buttons based on position
                    const btnUp = item.querySelector('.btn-move-up');
                    const btnDown = item.querySelector('.btn-move-down');
                    if (btnUp) btnUp.disabled = (index === 0);
                    if (btnDown) btnDown.disabled = (index === items.length - 1);
                });
            }

            // Drag Start
            list.addEventListener('dragstart', function (e) {
                const target = e.target.closest('.sortable-item');
                if (!target) return;
                
                dragEl = target;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', target.innerHTML);
                
                setTimeout(() => {
                    target.classList.add('dragging', 'border-indigo-400', 'bg-indigo-50/10');
                }, 0);
            });

            // Drag Over
            list.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                
                const target = e.target.closest('.sortable-item');
                if (target && target !== dragEl) {
                    const rect = target.getBoundingClientRect();
                    const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
                    list.insertBefore(dragEl, next ? target.nextSibling : target);
                }
            });

            // Drag End
            list.addEventListener('dragend', function (e) {
                if (dragEl) {
                    dragEl.classList.remove('dragging', 'border-indigo-400', 'bg-indigo-50/10');
                    dragEl = null;
                }
                updateOrders();
            });

            // Button Up / Down click handlers
            list.addEventListener('click', function (e) {
                const btnUp = e.target.closest('.btn-move-up');
                const btnDown = e.target.closest('.btn-move-down');
                
                if (btnUp) {
                    const item = btnUp.closest('.sortable-item');
                    const prev = item.previousElementSibling;
                    if (prev && prev.classList.contains('sortable-item')) {
                        list.insertBefore(item, prev);
                        updateOrders();
                    }
                }
                
                if (btnDown) {
                    const item = btnDown.closest('.sortable-item');
                    const next = item.nextElementSibling;
                    if (next && next.classList.contains('sortable-item')) {
                        list.insertBefore(next, item);
                        updateOrders();
                    }
                }
            });

            // Initial alignment run
            updateOrders();
        });
    </script>
    @endif
</x-admin-layout>
