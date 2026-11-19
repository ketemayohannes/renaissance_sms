<x-admin-layout>
    <x-slot name="header">Edit Academic Week</x-slot>

    <div class="max-w-5xl space-y-6">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Academic Calendar', 'url' => route('admin.academic-calendar.index', ['academic_year_id' => $week->academic_year_id])],
                ['label' => 'Week '.$week->ethiopian_range, 'url' => '#'],
            ]" />
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mt-2">{{ $week->isBreak() ? 'Break week' : 'Week '.$week->week_number }} · {{ $week->ethiopian_range }}</h1>
            <p class="text-sm font-semibold text-slate-500">{{ $week->academicYear->calendar_label }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <form method="POST" action="{{ route('admin.academic-calendar.weeks.update', $week) }}" class="lg:col-span-2 glass-panel p-6 space-y-5"
                  x-data="ethDates('{{ old('start_date', $week->start_date->toDateString()) }}', '{{ old('end_date', $week->end_date->toDateString()) }}')">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="term_id" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Quarter</label>
                        <select id="term_id" name="term_id" class="mt-1 w-full rounded-xl border-slate-200">
                            <option value="">— None (break) —</option>
                            @foreach($quarters as $q)
                                <option value="{{ $q->id }}" @selected((string) old('term_id', $week->term_id) === (string) $q->id)>{{ $q->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="week_number" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Week no.</label>
                        <input id="week_number" name="week_number" type="number" min="1" max="60" required value="{{ old('week_number', $week->week_number) }}" class="mt-1 w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label for="week_type" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Type</label>
                        <select id="week_type" name="week_type" class="mt-1 w-full rounded-xl border-slate-200">
                            <option value="instructional" @selected(old('week_type', $week->week_type) === 'instructional')>Instructional</option>
                            <option value="break" @selected(old('week_type', $week->week_type) === 'break')>Break</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="start_date" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Start date</label>
                        <input id="start_date" name="start_date" type="date" required x-model="start" @change="convert()" class="mt-1 w-full rounded-xl border-slate-200">
                        <p class="text-xs font-semibold text-indigo-600 mt-1" x-text="startEth"></p>
                        @error('start_date')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-xs font-black text-slate-500 uppercase tracking-widest">End date</label>
                        <input id="end_date" name="end_date" type="date" required x-model="end" @change="convert()" class="mt-1 w-full rounded-xl border-slate-200">
                        <p class="text-xs font-semibold text-indigo-600 mt-1" x-text="endEth"></p>
                        @error('end_date')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="working_days" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Published work days</label>
                        <input id="working_days" name="working_days" type="number" min="0" max="7" value="{{ old('working_days', $week->working_days) }}" class="mt-1 w-full rounded-xl border-slate-200">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Description</label>
                    <input id="description" name="description" type="text" maxlength="255" value="{{ old('description', $week->description) }}" class="mt-1 w-full rounded-xl border-slate-200">
                </div>
                <div>
                    <label for="review_note" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Review note</label>
                    <textarea id="review_note" name="review_note" rows="3" maxlength="2000" class="mt-1 w-full rounded-xl border-slate-200">{{ old('review_note', $week->review_note) }}</textarea>
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="needs_review" value="1" @checked(old('needs_review', $week->needs_review)) class="rounded border-slate-300 text-amber-600">
                        Still needs administrative review
                    </label>
                </div>

                <p class="text-xs text-slate-500">Changing week dates affects which days count as school days. Existing attendance and grades are not modified; anything affected is listed after saving.</p>
                <div class="flex justify-between">
                    <a href="{{ route('admin.academic-calendar.index', ['academic_year_id' => $week->academic_year_id]) }}" class="px-4 py-2 text-sm font-bold text-slate-600">Back</a>
                    <button class="px-6 py-2.5 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600">Save week</button>
                </div>
            </form>

            <aside class="space-y-6">
                @if($week->source_label)
                    <div class="glass-panel p-5 space-y-1 text-sm">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-2">Original record</h3>
                        <div><span class="font-bold text-slate-500">Dates:</span> {{ $week->source_label }}</div>
                        <div><span class="font-bold text-slate-500">Key:</span> <span class="font-mono text-xs">{{ $week->source_key }}</span></div>
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.academic-calendar.weeks.destroy', $week) }}" onsubmit="return confirm('Remove this week?')" class="glass-panel p-5">
                        @csrf @method('DELETE')
                        <button class="w-full px-4 py-2 bg-white border border-rose-200 text-rose-600 text-xs font-black uppercase tracking-widest rounded-xl">Remove week</button>
                    </form>
                @endif
                <div class="glass-panel p-5">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-3">History</h3>
                    @include('admin.academic-calendar.partials.history-list', ['logs' => $history])
                </div>
            </aside>
        </div>
    </div>

    @include('admin.academic-calendar.partials.eth-dates-script')
</x-admin-layout>
