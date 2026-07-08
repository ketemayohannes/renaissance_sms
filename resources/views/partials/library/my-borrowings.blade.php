{{-- Personal borrowing history. Expects: $borrowings (Collection), $routePrefix. --}}
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <a href="{{ route($routePrefix.'.index') }}" class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest hover:text-indigo-600 dark:hover:text-indigo-400">&larr; Back to catalog</a>
            <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-3">My Borrowings</h1>
            <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Books currently with you, and everything you've returned.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-6 py-4">Book</th>
                        <th class="text-left px-4 py-4">Author</th>
                        <th class="text-left px-4 py-4">Issued</th>
                        <th class="text-left px-4 py-4">Returned</th>
                        <th class="text-center px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($borrowings as $borrowing)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                            <td class="px-6 py-4 font-extrabold text-slate-800 dark:text-slate-200">{{ $borrowing->book->title ?? '—' }}</td>
                            <td class="px-4 py-4 font-bold text-slate-500 dark:text-slate-400">{{ $borrowing->book->author ?? '—' }}</td>
                            <td class="px-4 py-4 font-bold text-slate-500 dark:text-slate-400">{{ $borrowing->issued_date?->format('M j, Y') }}</td>
                            <td class="px-4 py-4 font-bold text-slate-500 dark:text-slate-400">{{ $borrowing->returned_date?->format('M j, Y') ?? '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $borrowing->status === 'borrowed' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-100/50 dark:border-amber-900/30' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30' }}">{{ $borrowing->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">You have no borrowings yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
