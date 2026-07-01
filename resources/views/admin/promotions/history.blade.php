<x-admin-layout>
    <x-slot name="header">Promotion History</x-slot>

    <div class="space-y-8">
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Promotions', 'url' => route('admin.promotions.index')],
                    ['label' => 'History', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Promotion History</h1>
                <p class="text-sm text-slate-500 mt-1 font-medium">Review and audit all past student promotion and retention records.</p>
            </div>
            <div>
                <a href="{{ route('admin.promotions.index') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Rules
                </a>
            </div>
        </div>

        <!-- Filters Panel -->
        <div class="glass-panel p-6">
            <form method="GET" action="{{ route('admin.promotions.history') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="flex flex-col gap-1.5 col-span-1 md:col-span-2 lg:col-span-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Search Student</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, ID, or Admission #..." class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                    </div>

                    <!-- From Academic Year -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">From Academic Year</label>
                        <select name="from_academic_year_id" class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('from_academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- To Academic Year -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">To Academic Year</label>
                        <select name="to_academic_year_id" class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('to_academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- From Grade Level -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">From Grade Level</label>
                        <select name="from_grade_level_id" class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                            <option value="">All Grade Levels</option>
                            @foreach($gradeLevels as $grade)
                                @if(!str_contains($grade->name, '(Social)'))
                                    <option value="{{ $grade->id }}" {{ request('from_grade_level_id') == $grade->id ? 'selected' : '' }}>{{ trim(preg_replace('/\s*\(.*?\)/', '', $grade->name)) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- To Grade Level -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">To Grade Level</label>
                        <select name="to_grade_level_id" class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                            <option value="">All Grade Levels</option>
                            @foreach($gradeLevels as $grade)
                                @if(!str_contains($grade->name, '(Social)'))
                                    <option value="{{ $grade->id }}" {{ request('to_grade_level_id') == $grade->id ? 'selected' : '' }}>{{ trim(preg_replace('/\s*\(.*?\)/', '', $grade->name)) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Decision Status -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Decision Status</label>
                        <select name="status" class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                            <option value="">All Decisions</option>
                            <option value="promoted" {{ request('status') === 'promoted' ? 'selected' : '' }}>Promoted</option>
                            <option value="retained" {{ request('status') === 'retained' ? 'selected' : '' }}>Retained</option>
                            <option value="re_exam" {{ request('status') === 're_exam' ? 'selected' : '' }}>Re-exam</option>
                            <option value="graduated" {{ request('status') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                        </select>
                    </div>

                    <!-- Enrollment Status -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Enrollment Status</label>
                        <select name="is_enrolled" class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                            <option value="">All Statuses</option>
                            <option value="0" {{ request('is_enrolled') === '0' ? 'selected' : '' }}>Pending</option>
                            <option value="1" {{ request('is_enrolled') === '1' ? 'selected' : '' }}>Enrolled</option>
                            <option value="graduated" {{ request('is_enrolled') === 'graduated' ? 'selected' : '' }}>Final / Graduated</option>
                        </select>
                    </div>

                    <!-- Filter / Reset buttons -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5 h-[42px] cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filter
                        </button>
                        @if(request()->anyFilled(['search', 'from_academic_year_id', 'to_academic_year_id', 'from_grade_level_id', 'to_grade_level_id', 'status', 'is_enrolled']))
                            <a href="{{ route('admin.promotions.history') }}" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl transition-all flex items-center justify-center h-[42px] cursor-pointer">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- History Records Wrapper -->
        <div class="glass-panel overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Processed Promotions</h2>
            </div>
            <div class="p-8">
                @if($promotions->isEmpty())
                    <div class="flex flex-col items-center py-20 text-center">
                        <div class="w-24 h-24 bg-indigo-50 border border-indigo-100 rounded-[2.5rem] flex items-center justify-center text-indigo-500 mb-6 shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <h3 class="text-slate-900 font-black text-lg uppercase tracking-tight">No Promotion Records</h3>
                        <p class="text-slate-400 text-sm mt-2 max-w-sm font-medium">No promotion processes have been executed yet in the system. Go to Process to begin.</p>
                        <a href="{{ route('admin.promotions.process') }}" class="vibrant-btn-blue mt-6">
                            Process Promotions
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th class="premium-table-header text-center w-12">
                                        <input type="checkbox" id="select-all-checkbox" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                                    </th>
                                    <th class="premium-table-header">Student</th>
                                    <th class="premium-table-header">From</th>
                                    <th class="premium-table-header text-center">Direction</th>
                                    <th class="premium-table-header">To</th>
                                    <th class="premium-table-header text-center">Status</th>
                                    <th class="premium-table-header text-center">Enrollment</th>
                                    <th class="premium-table-header">Processed By</th>
                                    <th class="premium-table-header">Date</th>
                                    <th class="premium-table-header text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($promotions as $promo)
                                    <tr class="premium-table-row">
                                        <td class="premium-table-cell text-center">
                                            <input type="checkbox" name="promotion_ids[]" value="{{ $promo->id }}" data-status="{{ $promo->status }}" data-enrolled="{{ $promo->is_enrolled ? 1 : 0 }}" class="row-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                                        </td>
                                        <td class="premium-table-cell font-bold text-slate-800">
                                            {{ $promo->student->full_name ?? 'N/A' }}
                                        </td>
                                        <td class="premium-table-cell">
                                            <div class="inline-flex flex-col">
                                                <span class="font-bold text-slate-700">{{ $promo->fromGradeLevel ? trim(preg_replace('/\s*\(.*?\)/', '', $promo->fromGradeLevel->name)) : '-' }}</span>
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">{{ $promo->fromAcademicYear->name ?? '' }}</span>
                                            </div>
                                        </td>
                                        <td class="premium-table-cell text-center">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mx-auto">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                            </div>
                                        </td>
                                        <td class="premium-table-cell">
                                            <div class="inline-flex flex-col">
                                                @if($promo->status === 'graduated')
                                                    <span class="font-bold text-emerald-700">Graduation</span>
                                                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-wider">Completes School</span>
                                                @else
                                                    <span class="font-bold text-slate-700">{{ $promo->toGradeLevel ? trim(preg_replace('/\s*\(.*?\)/', '', $promo->toGradeLevel->name)) : '-' }}</span>
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">{{ $promo->toSection->name ?? '' }} · {{ $promo->toAcademicYear->name ?? '' }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="premium-table-cell text-center">
                                            @if($promo->status === 'promoted')
                                                <span class="badge badge-success uppercase tracking-wider text-[9px] font-black px-2.5 py-1">Promoted</span>
                                            @elseif($promo->status === 'graduated')
                                                <span class="badge badge-success uppercase tracking-wider text-[9px] font-black px-2.5 py-1 bg-emerald-100 text-emerald-800">Graduated</span>
                                            @elseif($promo->status === 'retained')
                                                <span class="badge badge-danger uppercase tracking-wider text-[9px] font-black px-2.5 py-1">Retained</span>
                                            @elseif($promo->status === 're_exam')
                                                <span class="badge badge-warning uppercase tracking-wider text-[9px] font-black px-2.5 py-1">Re-exam</span>
                                            @else
                                                <span class="badge badge-info uppercase tracking-wider text-[9px] font-black px-2.5 py-1">Conditional</span>
                                            @endif
                                        </td>
                                        <td class="premium-table-cell text-center">
                                            @if($promo->status === 'graduated')
                                                <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-lg"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Final</span>
                                            @elseif($promo->is_enrolled)
                                                <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-lg"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Enrolled</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-amber-700 bg-amber-50 border border-amber-100 px-2.5 py-1 rounded-lg animate-pulse"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Pending</span>
                                            @endif
                                        </td>
                                        <td class="premium-table-cell">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[10px] font-black text-indigo-600">
                                                    {{ strtoupper(substr($promo->processor->name ?? 'S', 0, 1)) }}
                                                </div>
                                                <span class="font-bold text-slate-600 text-xs">{{ $promo->processor->name ?? 'System' }}</span>
                                            </div>
                                        </td>
                                        <td class="premium-table-cell text-slate-500 font-medium">
                                            <div class="flex items-center gap-1.5 text-xs font-semibold">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                {{ $promo->created_at->format('M j, Y') }}
                                            </div>
                                        </td>
                                        <td class="premium-table-cell text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                @if(!$promo->is_enrolled && $promo->status !== 'graduated')
                                                    <form action="{{ route('admin.promotions.enroll', $promo) }}" method="POST" onsubmit="confirmSingleAction(event, this, 'Confirm Enrollment', 'Enroll this student into {{ $promo->toGradeLevel->name ?? 'next grade' }}?', 'info')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                                            Enroll
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('admin.promotions.reverse', $promo) }}" method="POST" onsubmit="confirmSingleAction(event, this, 'Reverse Promotion', 'Are you sure you want to reverse this promotion? This will undo the enrollment and restore the student to their previous state.', 'danger')">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg bg-rose-500 text-white hover:bg-rose-600 transition-all shadow-sm hover:shadow-md">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                        Reverse
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        {{ $promotions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Hidden Bulk Action Forms -->
    <form id="bulk-enroll-form" action="{{ route('admin.promotions.bulk-enroll') }}" method="POST" class="hidden">
        @csrf
        <div id="bulk-enroll-inputs"></div>
    </form>
    <form id="bulk-reverse-form" action="{{ route('admin.promotions.bulk-reverse') }}" method="POST" class="hidden">
        @csrf
        <div id="bulk-reverse-inputs"></div>
    </form>

    <!-- Floating Bulk Actions Bar -->
    <div id="bulk-action-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 bg-white/95 backdrop-blur-xl border border-slate-200/80 shadow-[0_20px_50px_rgba(0,0,0,0.15)] rounded-2xl px-6 py-4 flex items-center gap-6 translate-y-32 opacity-0 pointer-events-none transition-all duration-300">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 bg-indigo-500 rounded-full animate-pulse"></span>
            <span class="text-xs font-black text-slate-700 uppercase tracking-widest"><span id="selected-count" class="text-indigo-600">0</span> Selected</span>
        </div>
        <div class="h-6 w-[1px] bg-slate-200"></div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="submitBulk('enroll')" id="bulk-enroll-btn" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all shadow-sm hover:shadow-md flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                Bulk Enroll
            </button>
            <button type="button" onclick="submitBulk('reverse')" class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all shadow-sm hover:shadow-md flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                Bulk Reverse
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const bulkActionBar = document.getElementById('bulk-action-bar');
            const selectedCountSpan = document.getElementById('selected-count');
            const bulkEnrollBtn = document.getElementById('bulk-enroll-btn');

            function updateBulkActionBar() {
                const checked = document.querySelectorAll('.row-checkbox:checked');
                const count = checked.length;
                selectedCountSpan.textContent = count;

                // Check if any of the selected are enrollable (pending promotions, not graduated)
                let hasEnrollable = false;
                checked.forEach(cb => {
                    const status = cb.getAttribute('data-status');
                    const enrolled = parseInt(cb.getAttribute('data-enrolled'));
                    if (status !== 'graduated' && enrolled === 0) {
                        hasEnrollable = true;
                    }
                });

                if (hasEnrollable) {
                    bulkEnrollBtn.style.display = 'inline-flex';
                } else {
                    bulkEnrollBtn.style.display = 'none';
                }

                if (count > 0) {
                    bulkActionBar.classList.remove('translate-y-32', 'opacity-0', 'pointer-events-none');
                } else {
                    bulkActionBar.classList.add('translate-y-32', 'opacity-0', 'pointer-events-none');
                }
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    rowCheckboxes.forEach(cb => {
                        cb.checked = selectAllCheckbox.checked;
                    });
                    updateBulkActionBar();
                });
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    // Update master checkbox state
                    if (selectAllCheckbox) {
                        const totalCount = rowCheckboxes.length;
                        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                        selectAllCheckbox.checked = (totalCount === checkedCount);
                        selectAllCheckbox.indeterminate = (checkedCount > 0 && checkedCount < totalCount);
                    }
                    updateBulkActionBar();
                });
            });

            let pendingAction = null;

            window.showConfirmModal = function (options) {
                const modal = document.getElementById('confirm-modal');
                const modalContent = modal.querySelector('.modal-container-box');
                const title = document.getElementById('modal-title');
                const message = document.getElementById('modal-message');
                const confirmBtn = document.getElementById('modal-confirm-btn');
                const iconContainer = document.getElementById('modal-icon-container');

                title.textContent = options.title || 'Are you sure?';
                message.textContent = options.message || '';
                
                if (options.type === 'danger') {
                    iconContainer.className = 'w-16 h-16 rounded-[1.5rem] bg-rose-50 border border-rose-100 flex items-center justify-center mb-6 shadow-sm text-rose-500';
                    iconContainer.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
                    confirmBtn.className = 'flex-1 py-3 bg-rose-500 hover:bg-rose-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all outline-none shadow-sm hover:shadow-md cursor-pointer';
                } else {
                    iconContainer.className = 'w-16 h-16 rounded-[1.5rem] bg-indigo-50 border border-indigo-100 flex items-center justify-center mb-6 shadow-sm text-indigo-500';
                    iconContainer.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    confirmBtn.className = 'flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all outline-none shadow-sm hover:shadow-md cursor-pointer';
                }

                pendingAction = options.onConfirm;

                modal.classList.remove('opacity-0', 'pointer-events-none');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            };

            window.closeConfirmModal = function () {
                const modal = document.getElementById('confirm-modal');
                const modalContent = modal.querySelector('.modal-container-box');
                modal.classList.add('opacity-0', 'pointer-events-none');
                modalContent.classList.remove('scale-100');
                modalContent.classList.add('scale-95');
                pendingAction = null;
            };

            document.getElementById('modal-confirm-btn').addEventListener('click', function() {
                if (typeof pendingAction === 'function') {
                    pendingAction();
                }
                closeConfirmModal();
            });

            window.confirmSingleAction = function (event, form, title, message, type) {
                event.preventDefault();
                showConfirmModal({
                    title: title,
                    message: message,
                    type: type,
                    onConfirm: function() {
                        form.submit();
                    }
                });
            };

            window.submitBulk = function (action) {
                const checked = document.querySelectorAll('.row-checkbox:checked');
                if (checked.length === 0) return;

                let title = '';
                let confirmMsg = '';
                let formId = '';
                let containerId = '';
                let type = 'info';

                if (action === 'enroll') {
                    let countToEnroll = 0;
                    checked.forEach(cb => {
                        const status = cb.getAttribute('data-status');
                        const enrolled = parseInt(cb.getAttribute('data-enrolled'));
                        if (status !== 'graduated' && enrolled === 0) {
                            countToEnroll++;
                        }
                    });

                    if (countToEnroll === 0) {
                        alert('None of the selected students can be enrolled (they are either graduated or already enrolled).');
                        return;
                    }

                    title = 'Confirm Bulk Enrollment';
                    confirmMsg = `Enroll ${countToEnroll} student(s) into their target section?`;
                    formId = 'bulk-enroll-form';
                    containerId = 'bulk-enroll-inputs';
                    type = 'info';
                } else if (action === 'reverse') {
                    title = 'Confirm Bulk Reversal';
                    confirmMsg = `Are you sure you want to reverse promotions for ${checked.length} student(s)? This will undo enrollment/graduation and restore their previous state.`;
                    formId = 'bulk-reverse-form';
                    containerId = 'bulk-reverse-inputs';
                    type = 'danger';
                }

                showConfirmModal({
                    title: title,
                    message: confirmMsg,
                    type: type,
                    onConfirm: function() {
                        const form = document.getElementById(formId);
                        const container = document.getElementById(containerId);
                        container.innerHTML = ''; // clear

                        checked.forEach(cb => {
                            const status = cb.getAttribute('data-status');
                            const enrolled = parseInt(cb.getAttribute('data-enrolled'));

                            if (action === 'enroll' && (status === 'graduated' || enrolled === 1)) {
                                return;
                            }

                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = cb.value;
                            container.appendChild(input);
                        });

                        form.submit();
                    }
                });
            };
        });
    </script>

    <!-- Custom Confirmation Modal Markup -->
    <div id="confirm-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
        <div class="modal-container-box bg-white border border-slate-100 shadow-2xl rounded-3xl max-w-md w-full p-8 mx-4 transform scale-95 transition-all duration-300 flex flex-col items-center text-center">
            <!-- Modal Icon -->
            <div id="modal-icon-container" class="w-16 h-16 rounded-[1.5rem] flex items-center justify-center mb-6 shadow-sm">
                <!-- Icon will be injected dynamically -->
            </div>
            
            <!-- Modal Title -->
            <h3 id="modal-title" class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">Confirmation Required</h3>
            
            <!-- Modal Message -->
            <p id="modal-message" class="text-slate-500 text-sm font-medium mb-8 leading-relaxed">Are you sure you want to perform this action?</p>
            
            <!-- Modal Buttons -->
            <div class="flex items-center justify-center gap-3 w-full">
                <button type="button" onclick="closeConfirmModal()" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl transition-all outline-none cursor-pointer">
                    Cancel
                </button>
                <button type="button" id="modal-confirm-btn" class="flex-1 py-3 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all outline-none shadow-sm hover:shadow-md cursor-pointer">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</x-admin-layout>
