@props(['compact' => false])

@php
    /*
     | Interactive academic calendar for dashboards. All dates, weeks and events come
     | from AcademicCalendarService (the single source of truth); nothing is hard-coded
     | here. Initial data is embedded so the first paint needs no extra request.
     */
    $calendarService = app(\App\Services\AcademicCalendarService::class);
    $today = \App\Support\EthiopianCalendar::today();
    $initial = [
        'summary' => $calendarService->getDashboardSummary($today),
        'grid' => $calendarService->getMonthGrid('gregorian', $today->year, $today->month, $today),
        'upcoming' => $calendarService->getUpcomingEvents(45, $today)->values(),
        'day' => [
            'context' => $calendarService->getAcademicContext($today),
            'events' => $calendarService->getEventsByDate($today)->values(),
            'gregorian' => $today->format('l, F j, Y'),
            'ethiopian' => \App\Support\EthiopianCalendar::format($today),
        ],
        'today' => $today->toDateString(),
        'routes' => [
            'month' => route('academic-calendar.month'),
            'day' => route('academic-calendar.day'),
            'upcoming' => route('academic-calendar.upcoming'),
        ],
    ];
    $summary = $initial['summary'];
    $ctx = $summary['context'];
@endphp

<section {{ $attributes->merge(['class' => 'dashboard-compact']) }} x-data="academicCalendar(@js($initial))" aria-label="Academic calendar">
    <!-- Summary strip: current period · next major event · progress -->
    <div class="glass-panel dark:bg-slate-900/60 grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100 dark:divide-slate-800">
        <div class="dash-card-sm min-w-0">
            <h3 class="dash-label mb-1">Current Academic Period</h3>
            @if($ctx['inAcademicYear'])
                <p class="text-[13px] font-black text-slate-900 dark:text-slate-100 leading-5 truncate">{{ $ctx['academicYearLabel'] }}</p>
                <div class="mt-1 flex flex-wrap gap-1 text-[10px] font-bold">
                    <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">{{ $ctx['semester'] ? 'Semester '.$ctx['semester'] : ($ctx['isSemesterBreak'] ? 'Semester break' : 'Between semesters') }}</span>
                    <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">{{ $ctx['quarter'] ? 'Quarter '.$ctx['quarter'] : 'No quarter' }}</span>
                    @if($ctx['academicWeek'])
                        <span class="px-1.5 py-0.5 rounded bg-slate-900 text-white dark:bg-white dark:text-slate-900">Week {{ $ctx['academicWeek'] }}</span>
                    @endif
                </div>
            @else
                <p class="text-xs font-semibold text-slate-500">Today is outside the academic year.</p>
            @endif
        </div>

        <div class="dash-card-sm min-w-0">
            <h3 class="dash-label mb-1">Next Major Event</h3>
            @if($summary['nextMajorEvent'])
                @php $next = $summary['nextMajorEvent']; @endphp
                <p class="text-[13px] font-black text-slate-900 dark:text-slate-100 leading-5 truncate" title="{{ $next['title'] }}">{{ $next['title'] }}</p>
                <p class="mt-1 text-[11px] font-semibold text-slate-500 leading-4 truncate">
                    {{ $next['ethiopian_range'] }} · {{ \Carbon\Carbon::parse($next['start_date'])->format('M j') }}
                    · <span class="font-black text-indigo-600 dark:text-indigo-400">{{ $next['days_until'] === 1 ? 'Tomorrow' : 'In '.$next['days_until'].' days' }}</span>
                </p>
            @else
                <p class="text-xs font-semibold text-slate-500">No upcoming milestones.</p>
            @endif
        </div>

        <div class="dash-card-sm min-w-0">
            <h3 class="dash-label mb-1">Academic Progress</h3>
            @if($summary['progress']['week'] && $summary['progress']['weeksInQuarter'])
                <div class="flex items-baseline justify-between gap-2">
                    <p class="text-[13px] font-black text-slate-900 dark:text-slate-100 leading-5">Week {{ $summary['progress']['week'] }} / {{ $summary['progress']['weeksInQuarter'] }}</p>
                    <span class="text-[11px] font-bold text-slate-500">Quarter {{ $ctx['quarter'] }}</span>
                </div>
                <div class="mt-1.5 h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $summary['progress']['percent'] }}" aria-label="Quarter progress">
                    <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $summary['progress']['percent'] }}%"></div>
                </div>
                @if(! is_null($summary['progress']['teachingDaysRemaining']))
                    <p class="mt-1 text-[11px] font-semibold text-slate-500 leading-4">{{ $summary['progress']['teachingDaysRemaining'] }} teaching day(s) left this quarter</p>
                @endif
            @else
                <p class="text-xs font-semibold text-slate-500">{{ $ctx['isSemesterBreak'] ? 'Semester break' : 'No active teaching week' }}</p>
            @endif
        </div>
    </div>

    <!-- Calendar + event panel (the panel takes the calendar's height on wide screens) -->
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,65fr)_minmax(0,35fr)] dash-grid">
        <!-- Calendar -->
        <div class="glass-panel dash-card dark:bg-slate-900/60 min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <div class="min-w-0">
                    <h3 class="text-[15px] font-black text-slate-900 dark:text-slate-100 tracking-tight leading-5" x-text="grid.title"></h3>
                    <p class="text-[11px] font-semibold text-slate-500 leading-4" x-text="grid.subtitle"></p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <div class="inline-flex rounded-lg border border-slate-200 dark:border-slate-700 p-0.5 text-[10px] font-black uppercase tracking-wider" role="group" aria-label="Calendar system">
                        <button type="button" @click="setSystem('gregorian')" :aria-pressed="grid.system === 'gregorian'"
                                :class="grid.system === 'gregorian' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500'" class="px-2 py-1 rounded-md">G.C.</button>
                        <button type="button" @click="setSystem('ethiopian')" :aria-pressed="grid.system === 'ethiopian'"
                                :class="grid.system === 'ethiopian' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500'" class="px-2 py-1 rounded-md">E.C.</button>
                    </div>
                    <button type="button" @click="go(grid.prev)" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-indigo-400" aria-label="Previous month">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" @click="goToday()" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 text-[10px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-200 hover:border-indigo-400">Today</button>
                    <button type="button" @click="go(grid.next)" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-indigo-400" aria-label="Next month">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <div class="relative" :class="loading ? 'opacity-60' : ''" :aria-busy="loading">
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="d in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']" :key="d">
                        <div class="text-center text-[10px] font-black text-slate-400 uppercase tracking-widest pb-1" x-text="d"></div>
                    </template>
                </div>
                <div class="grid grid-cols-7 gap-1" role="grid">
                    <template x-for="cell in grid.days" :key="cell.date">
                        <button type="button" role="gridcell" @click="select(cell.date)"
                                :aria-selected="selected === cell.date"
                                :aria-label="dayAria(cell)"
                                class="relative min-h-[3.25rem] sm:min-h-[3.75rem] rounded-lg p-1 text-left transition-all border focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                :class="[
                                    cell.in_month ? '' : 'opacity-40',
                                    selected === cell.date ? 'border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-500/40' : 'border-transparent hover:border-slate-200 dark:hover:border-slate-700',
                                    cell.is_school_day ? (bandClass(cell.band_color) || 'bg-white/70 dark:bg-slate-800/40') : 'bg-slate-50 dark:bg-slate-900/40',
                                ]">
                            <div class="flex items-center justify-between gap-1">
                                <span class="inline-flex items-center gap-1 min-w-0">
                                    <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full text-[11px] font-black"
                                          :class="cell.is_today ? 'bg-indigo-600 text-white' : (cell.is_school_day ? 'text-slate-800 dark:text-slate-100' : 'text-slate-400')"
                                          x-text="grid.system === 'ethiopian' ? cell.ethiopian_day : cell.gregorian_day"></span>
                                    <span x-show="cell.week_label" class="hidden sm:inline text-[8px] font-black text-slate-400 uppercase whitespace-nowrap" x-text="cell.week_label"></span>
                                </span>
                                <span class="hidden sm:inline text-[9px] font-semibold text-slate-400 leading-5 truncate"
                                      x-text="grid.system === 'ethiopian' ? cell.gregorian_label : cell.ethiopian_label"></span>
                            </div>
                            <div class="mt-0.5 space-y-0.5">
                                <template x-for="id in visibleHighlights(cell).slice(0, 2)" :key="id">
                                    <div class="truncate rounded px-1 text-[9px] sm:text-[10px] font-bold leading-[14px]" :class="chipClass(grid.events[id].color)" x-text="grid.events[id].title"></div>
                                </template>
                                <div x-show="visibleHighlights(cell).length > 2" class="text-[9px] font-bold text-slate-500 leading-3" x-text="'+' + (visibleHighlights(cell).length - 2) + ' more'"></div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[10px] font-bold text-slate-500" aria-label="Legend">
                <template x-for="item in legend" :key="item.label">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm" :class="dotClass(item.color)"></span><span x-text="item.label"></span></span>
                </template>
            </div>
        </div>

        <!-- Event panel -->
        <div class="relative min-w-0">
        <div class="glass-panel dash-card dark:bg-slate-900/60 flex flex-col gap-3 min-w-0 xl:absolute xl:inset-0 xl:overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between gap-2">
                <label for="cal-filter" class="dash-label">Show</label>
                <select id="cal-filter" x-model="filter" class="dash-select">
                    <option value="all">All events</option>
                    <option value="exams">Examinations</option>
                    <option value="revision">Revision</option>
                    <option value="breaks">Breaks & holidays</option>
                    <option value="milestones">Milestones</option>
                    <option value="teaching">Teaching activities</option>
                </select>
            </div>

            <!-- Selected day -->
            <div aria-live="polite">
                <p class="dash-label" x-text="selected === today ? 'Today' : 'Selected date'"></p>
                <div class="flex flex-wrap items-baseline gap-x-2">
                    <h4 class="text-sm font-black text-slate-900 dark:text-slate-100 leading-5" x-text="day.gregorian"></h4>
                    <p class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400" x-text="day.ethiopian + ' E.C.'"></p>
                </div>
                <div class="mt-1 flex flex-wrap gap-1 text-[10px] font-black uppercase tracking-wider">
                    <span x-show="day.context.quarter" class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300" x-text="'Q' + day.context.quarter + (day.context.academicWeek ? ' · Week ' + day.context.academicWeek : '')"></span>
                    <span class="px-1.5 py-0.5 rounded" :class="day.context.isSchoolDay ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800'"
                          x-text="dayTypeLabel(day.context)"></span>
                </div>

                <ul class="mt-2 space-y-1">
                    <template x-for="ev in filtered(day.events)" :key="ev.id">
                        <li>
                            <button type="button" @click="openEvent = (openEvent === ev.id ? null : ev.id)" :aria-expanded="openEvent === ev.id"
                                    class="w-full text-left rounded-lg border border-slate-100 dark:border-slate-800 px-2 py-1.5 hover:border-slate-200 dark:hover:border-slate-700">
                                <div class="flex items-start gap-2">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0" :class="dotClass(ev.color)"></span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-4" x-text="ev.title"></p>
                                        <p class="text-[10px] font-semibold text-slate-500 leading-4" x-text="ev.category_label + ' · ' + ev.ethiopian_range"></p>
                                    </div>
                                </div>
                                <div x-show="openEvent === ev.id" x-cloak class="mt-1 pl-3.5 text-[11px] text-slate-600 dark:text-slate-300 space-y-0.5">
                                    <p x-text="formatRange(ev.start_date, ev.end_date) + ' · ' + ev.days + ' day(s)'"></p>
                                    <p x-show="ev.description" x-text="ev.description"></p>
                                    <p x-show="ev.closes_school" class="font-semibold">School is closed.</p>
                                    <p x-show="ev.is_exam" class="font-semibold">School-wide examination window. Subject exam dates are set by teachers.</p>
                                </div>
                            </button>
                        </li>
                    </template>
                </ul>
                <p x-show="filtered(day.events).length === 0" class="mt-2 text-[11px] font-semibold text-slate-400"
                   x-text="day.context.isSchoolDay ? 'No school-wide events on this day. Regular classes.' : 'No events on this day.'"></p>
            </div>

            <!-- Next examination -->
            @if($summary['nextExam'])
                @php $exam = $summary['nextExam']; @endphp
                <div class="rounded-lg bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 px-2.5 py-2">
                    <div class="flex items-center justify-between gap-2">
                        <p class="dash-label !text-rose-500">{{ $exam['is_ongoing'] ? 'Examination in progress' : 'Next examination' }}</p>
                        <p class="text-[10px] font-black text-rose-600 dark:text-rose-400 shrink-0">{{ $exam['is_ongoing'] ? 'Ends '.\Carbon\Carbon::parse($exam['end_date'])->format('l') : ($exam['days_until'] === 1 ? 'Starts tomorrow' : 'Starts in '.$exam['days_until'].' days') }}</p>
                    </div>
                    <p class="text-xs font-black text-slate-900 dark:text-slate-100 leading-4">{{ $exam['title'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 leading-4">{{ $exam['ethiopian_range'] }} · {{ \Carbon\Carbon::parse($exam['start_date'])->format('M j') }}–{{ \Carbon\Carbon::parse($exam['end_date'])->format('M j') }}</p>
                </div>
            @endif

            <!-- Upcoming -->
            <div class="flex flex-col min-h-[8rem] xl:flex-1 xl:min-h-[7rem]">
                <p class="dash-label mb-1">Upcoming</p>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800 overflow-y-auto custom-scrollbar pr-1 {{ $compact ? '' : 'max-h-[18rem] xl:max-h-none xl:flex-1 xl:min-h-0' }}">
                    <template x-for="ev in filtered(upcoming).slice(0, {{ $compact ? 5 : 12 }})" :key="'u' + ev.id">
                        <li class="flex items-center gap-2 py-1">
                            <button type="button" @click="jumpTo(ev.start_date)" class="shrink-0 w-9 text-center rounded-md bg-slate-50 dark:bg-slate-800 py-0.5 hover:bg-indigo-50" :aria-label="'Show ' + ev.title + ' on the calendar'">
                                <span class="block text-[9px] font-black text-slate-400 uppercase leading-3" x-text="shortMonth(ev.start_date)"></span>
                                <span class="block text-[13px] font-black text-slate-800 dark:text-slate-100 leading-4" x-text="dayOf(ev.start_date)"></span>
                            </button>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate leading-4" x-text="ev.title"></p>
                                <p class="text-[10px] font-semibold text-slate-500 truncate leading-4" x-text="ev.ethiopian_range"></p>
                            </div>
                            <span class="shrink-0 text-[10px] font-black uppercase px-1.5 py-0.5 rounded" :class="chipClass(ev.color)" x-text="countdown(ev)"></span>
                        </li>
                    </template>
                </ul>
                <p x-show="filtered(upcoming).length === 0" class="text-[11px] font-semibold text-slate-400">Nothing scheduled in the next few weeks.</p>
            </div>
        </div>
        </div>
    </div>
</section>

@once
<script>
    function academicCalendar(initial) {
        // Full class strings so Tailwind keeps them in the build.
        const BAND = {
            blue: 'bg-blue-50/70 dark:bg-blue-500/10', purple: 'bg-purple-50/70 dark:bg-purple-500/10',
            amber: 'bg-amber-50/70 dark:bg-amber-500/10', red: 'bg-rose-50/70 dark:bg-rose-500/10',
            green: 'bg-emerald-50/70 dark:bg-emerald-500/10', teal: 'bg-teal-50/70 dark:bg-teal-500/10', gray: 'bg-slate-50 dark:bg-slate-800/60',
        };
        const CHIP = {
            blue: 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200', purple: 'bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-200',
            amber: 'bg-amber-100 text-amber-900 dark:bg-amber-500/20 dark:text-amber-200', red: 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-200',
            green: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200', teal: 'bg-teal-100 text-teal-800 dark:bg-teal-500/20 dark:text-teal-200',
            gray: 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
        };
        const DOT = {
            blue: 'bg-blue-500', purple: 'bg-purple-500', amber: 'bg-amber-500', red: 'bg-rose-500',
            green: 'bg-emerald-500', teal: 'bg-teal-500', gray: 'bg-slate-400',
        };
        const FILTERS = {
            all: () => true,
            exams: e => e.is_exam,
            revision: e => e.category === 'revision',
            breaks: e => ['semester_break', 'holiday', 'closure'].includes(e.category),
            milestones: e => ['milestone', 'parent_meeting'].includes(e.category) || e.priority >= 2,
            teaching: e => ['regular_class', 'continuous_assessment', 'project_work', 'worksheet'].includes(e.category),
        };
        const DAY_TYPES = {
            instructional: 'School day', examination: 'Examination day', revision: 'Revision day', event: 'School event',
            holiday: 'Holiday', closure: 'School closed', break: 'Semester break', weekend: 'Weekend',
            non_school_day: 'No classes', outside_year: 'Outside academic year',
        };

        return {
            grid: initial.grid,
            day: initial.day,
            upcoming: initial.upcoming,
            today: initial.today,
            selected: initial.today,
            routes: initial.routes,
            filter: 'all',
            loading: false,
            openEvent: null,
            legend: [
                { color: 'blue', label: 'Regular classes' }, { color: 'purple', label: 'Continuous assessment' },
                { color: 'amber', label: 'Projects & worksheets' }, { color: 'red', label: 'Examinations' },
                { color: 'teal', label: 'Revision' }, { color: 'green', label: 'Milestones' }, { color: 'gray', label: 'Breaks & holidays' },
            ],

            bandClass(c) { return c ? BAND[c] : ''; },
            chipClass(c) { return CHIP[c] || CHIP.gray; },
            dotClass(c) { return DOT[c] || DOT.gray; },
            filtered(list) { return (list || []).filter(FILTERS[this.filter] || FILTERS.all); },
            visibleHighlights(day) {
                const ids = this.filter === 'teaching' ? day.event_ids : (this.filter === 'all' ? day.highlight_ids : day.event_ids);
                return ids.filter(id => this.grid.events[id] && (FILTERS[this.filter] || FILTERS.all)(this.grid.events[id]));
            },
            dayTypeLabel(ctx) {
                const base = DAY_TYPES[ctx.dayType] || ctx.dayType;
                return ctx.dayTypeReason && !ctx.isSchoolDay && ctx.dayType !== 'weekend' ? base + ': ' + ctx.dayTypeReason : base;
            },
            dayAria(day) {
                const names = day.event_ids.map(id => this.grid.events[id]?.title).filter(Boolean);
                return `${day.gregorian_label}, ${day.ethiopian_label} E.C.` + (day.is_today ? ', today' : '') + (day.is_school_day ? '' : ', no school') + (names.length ? ': ' + names.join(', ') : '');
            },
            parse(d) { const [y, m, dd] = d.split('-').map(Number); return new Date(y, m - 1, dd); },
            shortMonth(d) { return this.parse(d).toLocaleString('en', { month: 'short' }); },
            dayOf(d) { return this.parse(d).getDate(); },
            formatRange(s, e) {
                const o = { weekday: 'short', month: 'short', day: 'numeric' };
                return s === e ? this.parse(s).toLocaleDateString('en', o) : this.parse(s).toLocaleDateString('en', o) + ' – ' + this.parse(e).toLocaleDateString('en', o);
            },
            countdown(ev) {
                if (ev.days_until <= 0) return 'Now';
                if (ev.days_until === 1) return 'Tomorrow';
                return ev.days_until + ' days';
            },

            async fetchJson(url) {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Request failed: ' + res.status);
                return res.json();
            },
            async load(system, year, month) {
                this.loading = true;
                try {
                    this.grid = await this.fetchJson(`${this.routes.month}?system=${system}&year=${year}&month=${month}`);
                } catch (e) { console.error(e); } finally { this.loading = false; }
            },
            go(target) { this.load(this.grid.system, target.year, target.month); },
            async setSystem(system) {
                if (system === this.grid.system) return;
                // Keep the selected date in view when switching calendars.
                const ctx = this.day.context;
                if (system === 'ethiopian') await this.load(system, ctx.ethiopianDate.year, ctx.ethiopianDate.month);
                else { const d = this.parse(this.selected); await this.load(system, d.getFullYear(), d.getMonth() + 1); }
            },
            async goToday() {
                this.selected = this.today;
                const t = this.parse(this.today);
                if (this.grid.system === 'gregorian') await this.load('gregorian', t.getFullYear(), t.getMonth() + 1);
                await this.select(this.today);
                if (this.grid.system === 'ethiopian') await this.load('ethiopian', this.day.context.ethiopianDate.year, this.day.context.ethiopianDate.month);
            },
            async select(date) {
                this.selected = date;
                this.openEvent = null;
                try {
                    this.day = await this.fetchJson(`${this.routes.day}?date=${date}`);
                } catch (e) { console.error(e); }
            },
            async jumpTo(date) {
                await this.select(date);
                if (this.grid.days.some(d => d.date === date && d.in_month)) return;
                const ctx = this.day.context;
                if (this.grid.system === 'ethiopian') await this.load('ethiopian', ctx.ethiopianDate.year, ctx.ethiopianDate.month);
                else { const d = this.parse(date); await this.load('gregorian', d.getFullYear(), d.getMonth() + 1); }
            },
        };
    }
</script>
@endonce
