<x-admin-layout>
    <x-slot name="header">Academic Calendar</x-slot>

    @php
        $levelStyles = [
            'error' => 'bg-rose-50 text-rose-700 ring-rose-200',
            'warning' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'review' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            'info' => 'bg-slate-100 text-slate-600 ring-slate-200',
        ];
        $quarters = $terms->where('type', 'quarter');
    @endphp

    <div class="space-y-8" x-data="{ tab: '{{ request('tab', 'weeks') }}' }">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <x-breadcrumb :items="[['label' => 'Academic Calendar', 'url' => '#']]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Academic Calendar</h1>
                @if($year)
                    <p class="text-sm font-semibold text-slate-500 mt-1">{{ $year->calendar_label }} · {{ $year->name }}
                        @if($year->is_active)<span class="ml-2 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest">Active</span>@endif
                    </p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" class="flex items-center gap-2">
                    <label for="year" class="sr-only">Academic year</label>
                    <select id="year" name="academic_year_id" onchange="this.form.submit()" class="rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($years as $y)
                            <option value="{{ $y->id }}" @selected($year && $year->id === $y->id)>{{ $y->name }}{{ $y->is_active ? ' (active)' : '' }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('admin.academic-calendar.history') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-xl hover:border-indigo-400 transition-all">Change History</a>
                @if($year)
                <a href="{{ route('admin.academic-calendar.events.create', ['academic_year_id' => $year->id]) }}" class="px-5 py-2.5 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Event
                </a>
                @endif
            </div>
        </div>

        @include('admin.layouts.partials.academic-setup-tabs')

        <!-- Change impacts (from the last edit) -->
        @if(session('calendar_impacts'))
            <div class="glass-panel p-5 border-l-8 border-amber-500" role="status">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-2">Review needed after this change</h3>
                <ul class="space-y-1 text-sm text-slate-700 list-disc pl-5">
                    @foreach(session('calendar_impacts') as $impact)
                        <li>{{ $impact['message'] }}</li>
                    @endforeach
                </ul>
                <p class="text-xs text-slate-500 mt-2">Calendar changes never modify submitted attendance, grades or assessments.</p>
            </div>
        @endif

        @if(session('import_issues'))
            <div class="glass-panel p-5 border-l-8 border-indigo-500">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-2">Import report</h3>
                <ul class="space-y-1.5 text-sm">
                    @foreach(session('import_issues') as $issue)
                        <li class="flex gap-2"><span class="shrink-0 px-2 py-0.5 h-fit rounded-md ring-1 ring-inset text-[10px] font-black uppercase {{ $levelStyles[$issue['level']] ?? $levelStyles['info'] }}">{{ $issue['level'] }}</span><span class="text-slate-700">{{ $issue['message'] }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(! $year)
            <div class="glass-panel p-10 text-center">
                <p class="text-slate-500 font-semibold">No academic years exist yet. Create one under Academic Years, or import an official calendar below.</p>
            </div>
        @else
            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="glass-panel p-4"><h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Teaching Weeks</h4><p class="text-2xl font-black text-slate-900">{{ $stats['weeks'] }}</p></div>
                <div class="glass-panel p-4"><h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">School Days</h4><p class="text-2xl font-black text-slate-900">{{ $stats['school_days'] ?? '—' }}</p></div>
                <div class="glass-panel p-4"><h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Events</h4><p class="text-2xl font-black text-slate-900">{{ $stats['events'] }}</p></div>
                <a href="{{ route('admin.academic-calendar.index', ['academic_year_id' => $year->id, 'review' => 1, 'tab' => 'events']) }}" class="glass-panel p-4 hover:-translate-y-0.5 transition-all"><h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Flagged for Review</h4><p class="text-2xl font-black {{ $stats['review'] ? 'text-amber-600' : 'text-slate-900' }}">{{ $stats['review'] }}</p></a>
            </div>

            <!-- Validation issues -->
            @if($issues)
                <details class="glass-panel p-5" {{ collect($issues)->whereIn('level', ['error', 'warning'])->isNotEmpty() ? 'open' : '' }}>
                    <summary class="cursor-pointer text-sm font-black text-slate-900 uppercase tracking-tight">
                        Schedule review ({{ count($issues) }} item{{ count($issues) === 1 ? '' : 's' }})
                    </summary>
                    <p class="text-xs text-slate-500 mt-2">Questionable dates and conflicts are listed here for an administrator to decide. Nothing is changed automatically; the source document stays authoritative.</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach($issues as $issue)
                            <li class="flex gap-2 items-start">
                                <span class="shrink-0 px-2 py-0.5 rounded-md ring-1 ring-inset text-[10px] font-black uppercase {{ $levelStyles[$issue['level']] ?? $levelStyles['info'] }}">{{ $issue['level'] }}</span>
                                <span class="text-slate-700">{{ $issue['message'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                    @if($termMismatch)
                        <form method="POST" action="{{ route('admin.academic-calendar.align-terms') }}" class="mt-4" onsubmit="return confirm('Move quarter and semester dates to match the calendar weeks? Marks and records stay linked to the same terms. This is recorded in the change history.')">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $year->id }}">
                            <button class="px-4 py-2 bg-indigo-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700">Align term dates with calendar</button>
                        </form>
                    @endif
                </details>
            @endif

            <!-- Tabs -->
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex flex-wrap gap-6" aria-label="Calendar sections" role="tablist">
                    @foreach(['weeks' => 'Weeks', 'events' => 'Events', 'days' => 'School Days', 'terms' => 'Semesters & Quarters', 'import' => 'Import'] as $key => $label)
                        <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                                class="py-3 px-1 border-b-2 text-sm font-semibold transition-colors">{{ $label }}</button>
                    @endforeach
                </nav>
            </div>

            <!-- Weeks -->
            <section x-show="tab === 'weeks'" x-cloak class="space-y-6">
                @forelse($weeks->groupBy(fn ($w) => $w->term?->name ?? ($w->isBreak() ? 'Breaks' : 'Unassigned')) as $group => $groupWeeks)
                    <div class="bg-white/80 backdrop-blur-xl rounded-3xl border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $group }}</h3>
                            <span class="text-xs font-semibold text-slate-400">{{ $groupWeeks->count() }} week(s)</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50">
                                        <th class="px-6 py-3">Week</th>
                                        <th class="px-6 py-3">Ethiopian dates</th>
                                        <th class="px-6 py-3">Gregorian dates</th>
                                        <th class="px-6 py-3 text-center">Work days</th>
                                        <th class="px-6 py-3">Activity (source)</th>
                                        <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($groupWeeks as $w)
                                        <tr class="hover:bg-slate-50/50 {{ $w->isBreak() ? 'bg-slate-50/70' : '' }}">
                                            <td class="px-6 py-3 font-black text-slate-700">{{ $w->isBreak() ? 'Break' : 'W'.$w->week_number }}</td>
                                            <td class="px-6 py-3 font-semibold text-slate-700">{{ $w->source_label ?: $w->ethiopian_range }}</td>
                                            <td class="px-6 py-3 text-slate-600">{{ $w->start_date->format('D M j') }} – {{ $w->end_date->format('D M j, Y') }}</td>
                                            <td class="px-6 py-3 text-center font-bold text-slate-700">{{ $w->working_days ?? '—' }}</td>
                                            <td class="px-6 py-3 text-slate-600">
                                                {{ $w->description }}
                                                @if($w->needs_review)
                                                    <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-black uppercase" title="{{ $w->review_note }}">Review</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-3 text-right">
                                                <a href="{{ route('admin.academic-calendar.weeks.edit', $w) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-black uppercase tracking-wider">Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="glass-panel p-8 text-center text-slate-500 font-semibold">
                        No academic weeks yet. Import an official calendar, or generate weeks from a quarter's dates below.
                    </div>
                @endforelse

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <form method="POST" action="{{ route('admin.academic-calendar.weeks.generate') }}" class="glass-panel p-5 space-y-3">
                        @csrf
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Generate weeks from a quarter</h3>
                        <p class="text-xs text-slate-500">Creates Monday–Friday weeks across the quarter's dates. Existing weeks are kept.</p>
                        <div class="flex gap-2">
                            <select name="term_id" required class="flex-1 rounded-xl border-slate-200 text-sm">
                                @foreach($quarters as $q)
                                    <option value="{{ $q->id }}">{{ $q->name }} ({{ $q->start_date?->format('M j') }} – {{ $q->end_date?->format('M j, Y') }})</option>
                                @endforeach
                            </select>
                            <button class="px-4 py-2 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600" @disabled($quarters->isEmpty())>Generate</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.academic-calendar.weeks.store') }}" class="glass-panel p-5 space-y-3">
                        @csrf
                        <input type="hidden" name="academic_year_id" value="{{ $year->id }}">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Add a single week</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="term_id" class="rounded-xl border-slate-200 text-sm" aria-label="Quarter">
                                <option value="">No quarter (break)</option>
                                @foreach($quarters as $q)<option value="{{ $q->id }}">{{ $q->name }}</option>@endforeach
                            </select>
                            <input type="number" name="week_number" min="1" max="60" required placeholder="Week no." class="rounded-xl border-slate-200 text-sm" aria-label="Week number">
                            <input type="date" name="start_date" required class="rounded-xl border-slate-200 text-sm" aria-label="Start date">
                            <input type="date" name="end_date" required class="rounded-xl border-slate-200 text-sm" aria-label="End date">
                            <select name="week_type" class="rounded-xl border-slate-200 text-sm" aria-label="Week type">
                                <option value="instructional">Instructional</option>
                                <option value="break">Break</option>
                            </select>
                            <input type="number" name="working_days" min="0" max="7" placeholder="Work days" class="rounded-xl border-slate-200 text-sm" aria-label="Working days">
                        </div>
                        <input type="text" name="description" maxlength="255" placeholder="Description (e.g. Regular classes)" class="w-full rounded-xl border-slate-200 text-sm">
                        <button class="px-4 py-2 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600">Add Week</button>
                    </form>
                </div>
            </section>

            <!-- Events -->
            <section x-show="tab === 'events'" x-cloak class="space-y-4">
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="academic_year_id" value="{{ $year->id }}">
                    <input type="hidden" name="tab" value="events">
                    <select name="category" class="rounded-xl border-slate-200 text-sm" aria-label="Category">
                        <option value="">All categories</option>
                        @foreach($categories as $key => $cat)<option value="{{ $key }}" @selected(request('category') === $key)>{{ $cat['label'] }}</option>@endforeach
                    </select>
                    <select name="status" class="rounded-xl border-slate-200 text-sm" aria-label="Status">
                        <option value="">Any status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                    <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="review" value="1" @checked(request()->boolean('review')) class="rounded border-slate-300 text-indigo-600"> Needs review only</label>
                    <button class="px-4 py-2 bg-white border border-slate-200 text-xs font-black uppercase tracking-widest rounded-xl hover:border-indigo-400">Filter</button>
                </form>

                <div class="bg-white/80 backdrop-blur-xl rounded-3xl border border-white shadow-xl shadow-slate-200/50 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50">
                                <th class="px-6 py-3">Event</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3">Ethiopian</th>
                                <th class="px-6 py-3">Gregorian</th>
                                <th class="px-6 py-3">Source</th>
                                <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($events as $e)
                                <tr class="hover:bg-slate-50/50 {{ $e->isCancelled() ? 'opacity-50' : '' }}">
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-slate-800 {{ $e->isCancelled() ? 'line-through' : '' }}">{{ $e->title }}</div>
                                        <div class="text-xs text-slate-400">{{ $e->term?->name }}{{ $e->priority === 2 ? ' · Major' : '' }}{{ $e->isCancelled() ? ' · Cancelled' : '' }}</div>
                                        @if($e->needs_review)
                                            <div class="text-xs text-amber-700 mt-1"><span class="font-black uppercase">Review:</span> {{ $e->review_note }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">@include('admin.academic-calendar.partials.badge', ['category' => $e->category])</td>
                                    <td class="px-6 py-3 font-semibold text-slate-700 whitespace-nowrap">{{ $e->ethiopian_range }}</td>
                                    <td class="px-6 py-3 text-slate-600 whitespace-nowrap">{{ $e->start_date->format('D M j') }}{{ $e->end_date->ne($e->start_date) ? ' – '.$e->end_date->format('D M j') : '' }}, {{ $e->end_date->format('Y') }}</td>
                                    <td class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">{{ $e->source }}</td>
                                    <td class="px-6 py-3 text-right whitespace-nowrap space-x-2">
                                        <a href="{{ route('admin.academic-calendar.events.edit', $e) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-black uppercase tracking-wider">Edit</a>
                                        @if($e->needs_review)
                                            <form method="POST" action="{{ route('admin.academic-calendar.events.review', $e) }}" class="inline">@csrf<input type="hidden" name="needs_review" value="0"><button class="text-emerald-600 hover:text-emerald-800 text-xs font-black uppercase tracking-wider">Mark reviewed</button></form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 font-semibold">No events match.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- School days -->
            <section x-show="tab === 'days'" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white/80 backdrop-blur-xl rounded-3xl border border-white shadow-xl shadow-slate-200/50 overflow-x-auto">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">School-day overrides</h3>
                        <p class="text-xs text-slate-500 mt-1">Normal working weekdays: {{ collect($year->workingWeekdays())->map(fn ($d) => ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'][$d])->implode(', ') }}. Holidays, breaks and closures are events; use an override for a single-day exception such as a Saturday make-up day.</p>
                    </div>
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-slate-50">
                            @forelse($overrides as $o)
                                <tr>
                                    <td class="px-6 py-3 font-semibold text-slate-700">{{ \App\Support\EthiopianCalendar::format($o->date) }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $o->date->format('D M j, Y') }}</td>
                                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ $o->is_school_day ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $o->is_school_day ? 'School day' : 'Closed' }}</span></td>
                                    <td class="px-6 py-3 text-slate-600">{{ $o->reason }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <form method="POST" action="{{ route('admin.academic-calendar.overrides.destroy', $o) }}" onsubmit="return confirm('Remove this override?')">@csrf @method('DELETE')<button class="text-rose-600 text-xs font-black uppercase">Remove</button></form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-6 py-10 text-center text-slate-400 font-semibold">No overrides.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <form method="POST" action="{{ route('admin.academic-calendar.overrides.store') }}" class="glass-panel p-5 space-y-3 h-fit">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $year->id }}">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Add override</h3>
                    <label class="block text-xs font-bold text-slate-500">Date<input type="date" name="date" required class="mt-1 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label class="block text-xs font-bold text-slate-500">Status
                        <select name="is_school_day" class="mt-1 w-full rounded-xl border-slate-200 text-sm">
                            <option value="0">Closed (no school)</option>
                            <option value="1">School day (e.g. make-up day)</option>
                        </select>
                    </label>
                    <label class="block text-xs font-bold text-slate-500">Reason<input type="text" name="reason" required maxlength="255" class="mt-1 w-full rounded-xl border-slate-200 text-sm" placeholder="e.g. Emergency closure"></label>
                    <button class="px-4 py-2 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600">Save</button>
                </form>
            </section>

            <!-- Terms -->
            <section x-show="tab === 'terms'" x-cloak class="bg-white/80 backdrop-blur-xl rounded-3xl border border-white shadow-xl shadow-slate-200/50 overflow-x-auto">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Semesters & quarters</h3>
                    <a href="{{ route('admin.terms.index') }}" class="text-xs font-black uppercase tracking-wider text-indigo-600">Manage terms</a>
                </div>
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-slate-50">
                        @foreach($terms as $t)
                            <tr>
                                <td class="px-6 py-3 font-bold text-slate-800">{{ $t->name }}</td>
                                <td class="px-6 py-3 text-xs uppercase font-black text-slate-400">{{ $t->type }}</td>
                                <td class="px-6 py-3 font-semibold text-slate-700">{{ $t->start_date && $t->end_date ? \App\Support\EthiopianCalendar::formatRange($t->start_date, $t->end_date) : '—' }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $t->start_date?->format('M j, Y') }} – {{ $t->end_date?->format('M j, Y') }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $t->start_date && $t->end_date ? app(\App\Services\AcademicCalendarService::class)->getWorkingDays($t->start_date, $t->end_date).' school days' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        <!-- Import -->
        <section x-show="tab === 'import' || {{ $year ? 'false' : 'true' }}" x-cloak class="glass-panel p-6 space-y-4">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Import an official calendar</h3>
            <p class="text-sm text-slate-600">Imports weeks, exam windows, revision weeks, breaks and milestones from a transcribed calendar file in <code class="text-xs bg-slate-100 px-1 rounded">database/data</code>. Safe to run more than once: existing records and anything an administrator edited are left unchanged, and existing term dates are never moved by the import.</p>
            <form method="POST" action="{{ route('admin.academic-calendar.import') }}" class="flex flex-wrap items-center gap-3">
                @csrf
                <select name="file" required class="rounded-xl border-slate-200 text-sm" aria-label="Calendar file">
                    @foreach($dataFiles as $file)<option value="{{ $file }}">{{ $file }}</option>@endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="dry_run" value="1" checked class="rounded border-slate-300 text-indigo-600"> Dry run (validate only)</label>
                <button class="px-4 py-2 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600" @disabled($dataFiles->isEmpty())>Run import</button>
            </form>
        </section>
    </div>
</x-admin-layout>
