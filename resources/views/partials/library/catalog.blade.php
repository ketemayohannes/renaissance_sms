{{-- Read-only library catalog. Expects: $books, $categories, $routePrefix (e.g. 'teacher.library'). --}}
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight">Library</h1>
            <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Browse books and digital resources.</p>
        </div>
        <a href="{{ route($routePrefix.'.my-borrowings') }}" class="px-5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-black uppercase tracking-widest transition-all self-start">
            My Borrowings
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Type</label>
            <select name="type" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                <option value="">All</option>
                <option value="physical" {{ request('type') === 'physical' ? 'selected' : '' }}>Physical</option>
                <option value="digital" {{ request('type') === 'digital' ? 'selected' : '' }}>Digital</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Category</label>
            <select name="category" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                <option value="">All</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, author, ISBN…" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Filter</button>
    </form>

    <!-- Catalog grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($books as $book)
            <a href="{{ route($routePrefix.'.show', $book) }}" class="block bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6 hover:border-indigo-200 dark:hover:border-indigo-900/50 transition-all">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $book->type === 'digital' ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300 border-violet-100/50 dark:border-violet-900/30' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-100/50 dark:border-indigo-900/30' }}">{{ $book->type }}</span>
                    @if($book->type === 'physical')
                        <span class="text-[10px] font-black uppercase tracking-widest {{ $book->available_copies > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400' }}">
                            {{ $book->available_copies > 0 ? $book->available_copies.' available' : 'All out' }}
                        </span>
                    @else
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Available</span>
                    @endif
                </div>
                <h3 class="text-base font-black text-slate-800 dark:text-slate-100 leading-snug">{{ $book->title }}</h3>
                <p class="text-sm font-bold text-slate-500 dark:text-slate-400 mt-1">{{ $book->author }}</p>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 mt-3 uppercase tracking-wider">
                    {{ $book->category ?? 'Uncategorised' }}
                    @if($book->type === 'physical' && $book->shelf_location) · Shelf {{ $book->shelf_location }} @endif
                </p>
            </a>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">
                No books match your search.
            </div>
        @endforelse
    </div>

    {{ $books->links() }}
</div>
