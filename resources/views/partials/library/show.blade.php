{{-- Read-only book detail. Expects: $book, $routePrefix. --}}
<div class="space-y-8">
    <div>
        <a href="{{ route($routePrefix.'.index') }}" class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest hover:text-indigo-600 dark:hover:text-indigo-400">&larr; Back to catalog</a>
        <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-3">{{ $book->title }}</h1>
        <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">
            {{ $book->author }}
            @if($book->category) · {{ $book->category }} @endif
            · <span class="uppercase">{{ $book->type }}</span>
        </p>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8 flex flex-col md:flex-row gap-8">
        @if($book->cover_image)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($book->cover_image) }}" alt="Cover" class="w-40 h-56 object-cover rounded-2xl border border-slate-100 dark:border-slate-800 flex-shrink-0">
        @endif
        <div class="flex-1 space-y-6">
            <!-- Availability / access -->
            <div class="flex flex-wrap items-center gap-3">
                @if($book->type === 'physical')
                    <span class="px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-widest border {{ $book->available_copies > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-100/50 dark:border-rose-900/30' }}">
                        {{ $book->available_copies }} of {{ $book->quantity }} available
                    </span>
                    @if($book->shelf_location)
                        <span class="px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-widest bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Shelf {{ $book->shelf_location }}</span>
                    @endif
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500">Ask the librarian to check this out for you.</span>
                @else
                    @if($book->file_path)
                        <a href="{{ route($routePrefix.'.download', $book) }}" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all">
                            Download {{ $book->file_format }}
                        </a>
                    @else
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500">No file attached yet.</span>
                    @endif
                @endif
            </div>

            @if($book->publisher || $book->isbn)
                <div class="flex flex-wrap gap-x-8 gap-y-2 text-sm">
                    @if($book->publisher)
                        <div><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Publisher</span><span class="font-bold text-slate-700 dark:text-slate-200">{{ $book->publisher }}</span></div>
                    @endif
                    @if($book->isbn)
                        <div><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">ISBN</span><span class="font-bold font-mono text-slate-700 dark:text-slate-200">{{ $book->isbn }}</span></div>
                    @endif
                </div>
            @endif

            @if($book->description)
                <div>
                    <h2 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">About</h2>
                    <p class="text-slate-600 dark:text-slate-300 font-semibold leading-relaxed">{{ $book->description }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
