<x-admin-layout>
    <x-slot name="header">Promotion Management</x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Promotions', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Promotions</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.promotions.history') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    History
                </a>
                <a href="{{ route('admin.promotions.process') }}" class="px-6 py-3 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200 flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Process Promotions
                </a>
            </div>
        </div>

        <!-- Add/Edit Rule Form -->
        <div class="glass-panel p-8">
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                Configure Promotion Logic
            </h3>
            <form action="{{ route('admin.promotions.store-rule') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6 items-end">
                @csrf
                <div class="md:col-span-1">
                    <label for="from_grade_level_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Source Grade</label>
                    <select name="from_grade_level_id" id="from_grade_level_id" class="premium-select w-full" required>
                        <option value="">Select Grade</option>
                        @foreach($gradeLevels as $gl)
                            <option value="{{ $gl->id }}">{{ $gl->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label for="to_grade_level_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Target Grade</label>
                    <select name="to_grade_level_id" id="to_grade_level_id" class="premium-select w-full">
                        <option value="">None (Graduates)</option>
                        @foreach($gradeLevels as $gl)
                            <option value="{{ $gl->id }}">{{ $gl->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label for="min_average" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Min Average (%)</label>
                    <input type="number" step="0.01" name="min_average" id="min_average" value="50" class="premium-input w-full" required>
                </div>
                <div class="md:col-span-1">
                    <label for="max_failed_subjects" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Max Fails</label>
                    <input type="number" name="max_failed_subjects" id="max_failed_subjects" value="0" class="premium-input w-full" required>
                </div>
                <div class="lg:col-span-1 col-span-full md:col-span-1">
                    <button type="submit" class="vibrant-btn-blue w-full">
                        Save Rule
                    </button>
                </div>

                <!-- Advanced Customizable Rules Section -->
                <div class="col-span-full border-t border-slate-100 pt-6 mt-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Major Subjects Selection -->
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Major Subjects (No Fails Allowed)</label>
                        <div id="major-subjects-container" class="grid grid-cols-2 gap-3 bg-slate-50/50 p-4 rounded-2xl border border-slate-100 max-h-48 overflow-y-auto">
                            <p class="text-xs text-slate-400 italic col-span-2">Select a Source Grade to load subjects...</p>
                        </div>
                    </div>

                    <!-- Action & Conditional Rules -->
                    <div class="space-y-6">
                        <div>
                            <label for="failed_action" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Failed Action (If criteria not met)</label>
                            <select name="failed_action" id="failed_action" class="premium-select w-full">
                                <option value="retained" selected>Retain Student in same grade</option>
                                <option value="re_exam">Recommend Re-exam (Retake)</option>
                            </select>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Conditional Pass Rules (Non-Major Fails)</label>
                                <button type="button" id="add-conditional-rule" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">
                                    + Add Rule
                                </button>
                            </div>
                            <div id="conditional-rules-container" class="space-y-3">
                                <!-- Dynamically added rows -->
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Panel -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Active Rules ({{ $academicYear->name }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Source Grade</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Direction</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Target Grade</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Criteria & Customizable Policy</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($promotionRules as $rule)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-black text-xs shadow-sm border border-slate-200/50 group-hover:scale-110 transition-transform">
                                            {{ substr($rule->fromGradeLevel->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-700">{{ $rule->fromGradeLevel->name }}</span>
                                            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-wider">{{ $rule->fromGradeLevel->division->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-400 mx-auto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @if($rule->toGradeLevel)
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-xs shadow-sm border border-indigo-100/50 group-hover:scale-110 transition-transform">
                                                {{ substr($rule->toGradeLevel->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-700">{{ $rule->toGradeLevel->name }}</span>
                                                <span class="block text-[9px] font-black text-slate-400 uppercase tracking-wider">{{ $rule->toGradeLevel->division->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 font-black text-xs shadow-sm border border-emerald-100/50 group-hover:scale-110 transition-transform">
                                                GR
                                            </div>
                                            <div>
                                                <span class="font-bold text-emerald-700">Graduation</span>
                                                <span class="block text-[9px] font-black text-slate-400 uppercase tracking-wider">Completes School</span>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center gap-3">
                                            <div class="inline-flex flex-col items-start px-3 py-1 bg-slate-50 rounded-lg border border-slate-100">
                                                <span class="text-xs font-black text-slate-800 tracking-tighter">{{ $rule->min_average }}%</span>
                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider">Min Avg</span>
                                            </div>
                                            <div class="inline-flex flex-col items-start px-3 py-1 bg-slate-50 rounded-lg border border-slate-100">
                                                <span class="text-xs font-black text-slate-800 tracking-tighter">{{ $rule->max_failed_subjects }}</span>
                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider">Max Fails</span>
                                            </div>
                                            <span class="px-2 py-1 text-[9px] font-black uppercase tracking-wider rounded-lg border {{ $rule->failed_action === 're_exam' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-slate-50 text-slate-600 border-slate-100' }}">
                                                {{ $rule->failed_action === 're_exam' ? 'Re-exam' : 'Retain' }}
                                            </span>
                                        </div>
                                        
                                        @if($rule->major_subjects && count($rule->major_subjects) > 0)
                                            <div class="text-[10px] text-slate-500">
                                                <strong class="font-black text-slate-600">Major Subjects:</strong> 
                                                @php
                                                    $majorNames = $rule->fromGradeLevel->subjects
                                                        ->whereIn('id', $rule->major_subjects)
                                                        ->pluck('name')
                                                        ->toArray();
                                                @endphp
                                                {{ implode(', ', $majorNames) }}
                                            </div>
                                        @endif

                                        @if($rule->conditional_rules && count($rule->conditional_rules) > 0)
                                            <div class="flex flex-wrap gap-1.5 items-center">
                                                <strong class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Conditional Passes:</strong>
                                                @foreach($rule->conditional_rules as $cRule)
                                                    <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-100/50 text-indigo-700 rounded text-[9px] font-bold">
                                                        {{ $cRule['fails'] }} {{ Str::plural('Fail', $cRule['fails']) }} &rArr; &ge;{{ $cRule['avg'] }}%
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="editRule({{ json_encode($rule) }})" class="p-3 hover:bg-white rounded-xl text-slate-400 hover:text-indigo-600 transition-all hover:shadow-sm border border-transparent hover:border-slate-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <form action="{{ route('admin.promotions.delete-rule', $rule) }}" method="POST" class="inline delete-form" data-confirm-message="Are you sure you want to delete this rule?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-3 hover:bg-white rounded-xl text-slate-400 hover:text-rose-600 transition-all hover:shadow-sm border border-transparent hover:border-slate-100">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mb-4">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0l-3 3m3-3l-3-3M8 17H6a2 2 0 01-2-2V9a2 2 0 012-2h5m4 0h2a2 2 0 012 2v1m-7 7l-3-3m0 0l3-3m-3 3h8"></path></svg>
                                        </div>
                                        <p class="text-slate-400 font-bold tracking-tight uppercase text-xs tracking-widest">No promotion rules defined for {{ $academicYear->name }}</p>
                                        <p class="text-slate-300 text-[10px] mt-1 font-bold uppercase">Configure the rules above to begin.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const gradeLevels = @json($gradeLevels);
        const fromGradeSelect = document.getElementById('from_grade_level_id');
        const majorSubjectsContainer = document.getElementById('major-subjects-container');
        const addRuleBtn = document.getElementById('add-conditional-rule');
        const rulesContainer = document.getElementById('conditional-rules-container');

        // Load subjects when source grade changes
        fromGradeSelect.addEventListener('change', function() {
            const gradeId = this.value;
            majorSubjectsContainer.innerHTML = '';
            
            if (!gradeId) {
                majorSubjectsContainer.innerHTML = '<p class="text-xs text-slate-400 italic col-span-2">Select a Source Grade to load subjects...</p>';
                return;
            }

            const selectedGrade = gradeLevels.find(g => g.id == gradeId);
            if (selectedGrade && selectedGrade.subjects && selectedGrade.subjects.length > 0) {
                selectedGrade.subjects.forEach(subject => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center gap-2.5 p-1.5 hover:bg-white rounded-lg transition-all';
                    div.innerHTML = `
                        <input type="checkbox" name="major_subjects[]" value="${subject.id}" id="subject_${subject.id}" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                        <label for="subject_${subject.id}" class="text-xs font-semibold text-slate-700 cursor-pointer select-none">${subject.name}</label>
                    `;
                    majorSubjectsContainer.appendChild(div);
                });
            } else {
                majorSubjectsContainer.innerHTML = '<p class="text-xs text-slate-400 italic col-span-2">No subjects found for this grade.</p>';
            }
        });

        // Dynamic Conditional Rules
        let ruleIndex = 0;
        function addRuleRow(fails = '', avg = '') {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 bg-slate-50/50 p-3 rounded-xl border border-slate-100 rule-row animate-fade-in';
            row.innerHTML = `
                <div class="flex items-center gap-2 flex-1">
                    <span class="text-[10px] font-black text-slate-400 uppercase">Fails</span>
                    <input type="number" name="conditional_rules[${ruleIndex}][fails]" value="${fails}" min="1" max="10" placeholder="e.g. 1" class="premium-input py-1 text-xs w-full" required>
                </div>
                <div class="flex items-center gap-2 flex-1">
                    <span class="text-[10px] font-black text-slate-400 uppercase">Avg &ge;</span>
                    <input type="number" name="conditional_rules[${ruleIndex}][avg]" value="${avg}" min="0" max="100" step="0.01" placeholder="e.g. 51" class="premium-input py-1 text-xs w-full" required>
                </div>
                <button type="button" class="remove-rule-btn p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            `;
            rulesContainer.appendChild(row);
            ruleIndex++;

            row.querySelector('.remove-rule-btn').addEventListener('click', function() {
                row.remove();
            });
        }

        addRuleBtn.addEventListener('click', () => addRuleRow());

        // Edit logic implementation
        window.editRule = function(ruleData) {
            // Populate inputs
            document.getElementById('from_grade_level_id').value = ruleData.from_grade_level_id;
            document.getElementById('from_grade_level_id').dispatchEvent(new Event('change'));
            
            document.getElementById('to_grade_level_id').value = ruleData.to_grade_level_id || '';
            document.getElementById('min_average').value = ruleData.min_average;
            document.getElementById('max_failed_subjects').value = ruleData.max_failed_subjects;
            document.getElementById('failed_action').value = ruleData.failed_action || 'retained';

            // Wait for subjects to populate before selecting major subjects
            setTimeout(() => {
                if (ruleData.major_subjects) {
                    ruleData.major_subjects.forEach(subId => {
                        const checkbox = document.getElementById('subject_' + subId);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            }, 150);

            // Clear and add conditional rules
            rulesContainer.innerHTML = '';
            if (ruleData.conditional_rules) {
                ruleData.conditional_rules.forEach(rule => {
                    addRuleRow(rule.fails, rule.avg);
                });
            }
            
            // Scroll to form
            document.querySelector('.glass-panel').scrollIntoView({ behavior: 'smooth' });
        };
    });
    </script>
</x-admin-layout>
