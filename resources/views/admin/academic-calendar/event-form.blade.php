<x-admin-layout>
    <x-slot name="header">{{ $event->exists ? 'Edit Calendar Event' : 'New Calendar Event' }}</x-slot>

    <div class="max-w-5xl space-y-6">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Academic Calendar', 'url' => route('admin.academic-calendar.index', ['academic_year_id' => $year->id, 'tab' => 'events'])],
                ['label' => $event->exists ? $event->title : 'New event', 'url' => '#'],
            ]" />
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mt-2">{{ $event->exists ? $event->title : 'New Calendar Event' }}</h1>
            <p class="text-sm font-semibold text-slate-500">{{ $year->calendar_label }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <form method="POST" action="{{ $event->exists ? route('admin.academic-calendar.events.update', $event) : route('admin.academic-calendar.events.store') }}"
                  class="lg:col-span-2 glass-panel p-6 space-y-5"
                  x-data="ethDates('{{ old('start_date', $event->start_date?->toDateString()) }}', '{{ old('end_date', $event->end_date?->toDateString()) }}')">
                @csrf
                @if($event->exists) @method('PUT') @endif
                <input type="hidden" name="academic_year_id" value="{{ $year->id }}">

                <div>
                    <label for="title" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Title</label>
                    <input id="title" name="title" type="text" required maxlength="255" value="{{ old('title', $event->title) }}" class="mt-1 w-full rounded-xl border-slate-200">
                    @error('title')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Category</label>
                        <select id="category" name="category" class="mt-1 w-full rounded-xl border-slate-200">
                            @foreach($categories as $key => $cat)
                                <option value="{{ $key }}" @selected(old('category', $event->category) === $key)>{{ $cat['label'] }}{{ ($cat['closes_school'] ?? false) ? ' (closes school)' : '' }}</option>
                            @endforeach
                        </select>
                        @error('category')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="term_id" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Quarter</label>
                        <select id="term_id" name="term_id" class="mt-1 w-full rounded-xl border-slate-200">
                            <option value="">— None / school-wide —</option>
                            @foreach($quarters as $q)
                                <option value="{{ $q->id }}" @selected((string) old('term_id', $event->term_id) === (string) $q->id)>{{ $q->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Start date</label>
                        <input id="start_date" name="start_date" type="date" required x-model="start" @change="convert()" class="mt-1 w-full rounded-xl border-slate-200">
                        <p class="text-xs font-semibold text-indigo-600 mt-1" x-text="startEth" aria-live="polite"></p>
                        @error('start_date')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-xs font-black text-slate-500 uppercase tracking-widest">End date</label>
                        <input id="end_date" name="end_date" type="date" required x-model="end" @change="convert()" class="mt-1 w-full rounded-xl border-slate-200">
                        <p class="text-xs font-semibold text-indigo-600 mt-1" x-text="endEth" aria-live="polite"></p>
                        @error('end_date')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="priority" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Priority</label>
                        <select id="priority" name="priority" class="mt-1 w-full rounded-xl border-slate-200">
                            @foreach([0 => 'Low (background)', 1 => 'Normal', 2 => 'Major milestone'] as $v => $l)
                                <option value="{{ $v }}" @selected((int) old('priority', $event->priority ?? 1) === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2 pt-5">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_instructional" value="1" @checked(old('is_instructional', $event->is_instructional)) class="rounded border-slate-300 text-indigo-600">
                            Teaching takes place on these days
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="needs_review" value="1" @checked(old('needs_review', $event->needs_review)) class="rounded border-slate-300 text-amber-600">
                            Flag for administrative review
                        </label>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Description</label>
                    <textarea id="description" name="description" rows="3" maxlength="2000" class="mt-1 w-full rounded-xl border-slate-200">{{ old('description', $event->description) }}</textarea>
                </div>
                <div>
                    <label for="review_note" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Review note</label>
                    <textarea id="review_note" name="review_note" rows="2" maxlength="2000" class="mt-1 w-full rounded-xl border-slate-200">{{ old('review_note', $event->review_note) }}</textarea>
                </div>

                <p class="text-xs text-slate-500">Saving changes only the calendar. Attendance already taken, grades and assessments are never modified; you will see a list of anything affected.</p>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('admin.academic-calendar.index', ['academic_year_id' => $year->id, 'tab' => 'events']) }}" class="px-4 py-2 text-sm font-bold text-slate-600">Back</a>
                    <button class="px-6 py-2.5 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600">{{ $event->exists ? 'Save changes' : 'Create event' }}</button>
                </div>
            </form>

            <aside class="space-y-6">
                @if($event->exists)
                    <div class="glass-panel p-5 space-y-3">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Status</h3>
                        <p class="text-sm text-slate-600">{{ $event->isCancelled() ? 'Cancelled — kept for history.' : 'Active' }}</p>
                        @if($event->isCancelled())
                            <form method="POST" action="{{ route('admin.academic-calendar.events.restore', $event) }}">@csrf<button class="w-full px-4 py-2 bg-emerald-600 text-white text-xs font-black uppercase tracking-widest rounded-xl">Restore event</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.academic-calendar.events.cancel', $event) }}" onsubmit="return confirm('Cancel this event? It stays in the history and can be restored.')">@csrf<button class="w-full px-4 py-2 bg-amber-500 text-white text-xs font-black uppercase tracking-widest rounded-xl">Cancel event</button></form>
                        @endif
                        @if($event->source === 'manual')
                            <form method="POST" action="{{ route('admin.academic-calendar.events.destroy', $event) }}" onsubmit="return confirm('Delete this event permanently?')">@csrf @method('DELETE')<button class="w-full px-4 py-2 bg-white border border-rose-200 text-rose-600 text-xs font-black uppercase tracking-widest rounded-xl">Delete</button></form>
                        @endif
                    </div>

                    @if($event->source !== 'manual')
                        <div class="glass-panel p-5 space-y-2">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Original record</h3>
                            <dl class="text-sm space-y-1">
                                <div><dt class="inline font-bold text-slate-500">Source:</dt> <dd class="inline text-slate-700">{{ $event->source === 'inferred' ? 'Inferred during import' : 'Official calendar document' }}</dd></div>
                                @if($event->source_text)<div><dt class="inline font-bold text-slate-500">Wording:</dt> <dd class="inline text-slate-700">“{{ $event->source_text }}”</dd></div>@endif
                                @if($event->week?->source_label)<div><dt class="inline font-bold text-slate-500">Week:</dt> <dd class="inline text-slate-700">{{ $event->week->source_label }} ({{ $event->week->working_days }} work days)</dd></div>@endif
                                <div><dt class="inline font-bold text-slate-500">Key:</dt> <dd class="inline text-slate-500 font-mono text-xs">{{ $event->source_key }}</dd></div>
                            </dl>
                        </div>
                    @endif

                    <div class="glass-panel p-5">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-3">History</h3>
                        @include('admin.academic-calendar.partials.history-list', ['logs' => $history])
                    </div>
                @endif
            </aside>
        </div>
    </div>

    @include('admin.academic-calendar.partials.eth-dates-script')
</x-admin-layout>
