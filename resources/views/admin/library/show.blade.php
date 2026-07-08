<x-admin-layout>
    <div class="space-y-8" x-data="{ editOpen: false, checkoutOpen: false }">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Library', 'url' => route('admin.library.index')],
                    ['label' => $book->title, 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">{{ $book->title }}</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">
                    {{ $book->author }}
                    @if($book->category) · {{ $book->category }} @endif
                    · <span class="uppercase">{{ $book->type }}</span>
                    @if($book->type === 'physical')
                        · {{ $book->available_copies }} of {{ $book->quantity }} available
                        @if($book->shelf_location) · Shelf {{ $book->shelf_location }} @endif
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if($book->type === 'physical')
                    @can('issue books')
                    <button @click="checkoutOpen = !checkoutOpen; editOpen = false" @disabled($book->available_copies < 1)
                        class="px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-widest transition-all {{ $book->available_copies < 1 ? 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-200 dark:shadow-none' }}">
                        Check Out
                    </button>
                    @endcan
                @else
                    @if($book->file_path)
                    <a href="{{ route('admin.library.download', $book) }}" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all">
                        Download {{ $book->file_format }}
                    </a>
                    @endif
                @endif
                @can('manage books')
                <button @click="editOpen = !editOpen; checkoutOpen = false" class="px-5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-black uppercase tracking-widest transition-all">Edit</button>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-emerald-700 dark:text-emerald-300 font-bold text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ $errors->first() }}</div>
        @endif

        <!-- Check-out form (physical) -->
        @if($book->type === 'physical')
        @can('issue books')
        <div x-show="checkoutOpen" x-collapse x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">Check Out a Copy</h2>
                <form method="POST" action="{{ route('admin.library.check-out', $book) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    @csrf
                    <div class="xl:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Borrower</label>
                        <select name="user_id" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                            <option value="">— Select —</option>
                            @foreach($borrowers as $borrower)
                                <option value="{{ $borrower->id }}">{{ $borrower->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Issued Date</label>
                        <input type="date" name="issued_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Remarks</label>
                        <input type="text" name="remarks" maxlength="500" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Confirm Check-Out</button>
                    </div>
                </form>
            </div>
        </div>
        @endcan
        @endif

        <!-- Edit form -->
        @can('manage books')
        <div x-show="editOpen" x-collapse x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">Edit Book</h2>
                <form method="POST" action="{{ route('admin.library.update', $book) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                    @csrf
                    @method('PUT')
                    <div class="xl:col-span-3">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Title</label>
                        <input type="text" name="title" required maxlength="255" value="{{ old('title', $book->title) }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-3">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Author</label>
                        <input type="text" name="author" required maxlength="255" value="{{ old('author', $book->author) }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Category</label>
                        <input type="text" name="category" maxlength="100" value="{{ old('category', $book->category) }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Publisher</label>
                        <input type="text" name="publisher" maxlength="255" value="{{ old('publisher', $book->publisher) }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">ISBN</label>
                        <input type="text" name="isbn" maxlength="50" value="{{ old('isbn', $book->isbn) }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold font-mono">
                    </div>

                    @if($book->type === 'physical')
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Total Copies</label>
                            <input type="number" name="quantity" min="0" required value="{{ old('quantity', $book->quantity) }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                            <p class="text-[10px] font-bold text-slate-400 mt-1">{{ $book->available_copies }} available now</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Shelf</label>
                            <input type="text" name="shelf_location" maxlength="100" value="{{ old('shelf_location', $book->shelf_location) }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                    @else
                        <div class="xl:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Replace File (PDF / EPUB, optional)</label>
                            <input type="file" name="file" accept=".pdf,.epub" class="w-full text-sm font-bold text-slate-600 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/60 dark:file:text-indigo-300">
                            @if($book->file_path)<p class="text-[10px] font-bold text-slate-400 mt-1">Current: {{ $book->file_format }}</p>@endif
                        </div>
                    @endif

                    <div class="xl:col-span-3">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Replace Cover (optional)</label>
                        <input type="file" name="cover_image" accept="image/*" class="w-full text-sm font-bold text-slate-600 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:bg-slate-100 file:text-slate-600 dark:file:bg-slate-800 dark:file:text-slate-300">
                    </div>
                    <div class="xl:col-span-6">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Description</label>
                        <textarea name="description" rows="2" maxlength="2000" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">{{ old('description', $book->description) }}</textarea>
                    </div>
                    <label class="flex items-center gap-2 xl:col-span-3">
                        <input type="checkbox" name="is_active" value="1" {{ $book->is_active ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                        <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Active (visible in catalog)</span>
                    </label>
                    <div class="xl:col-span-3 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        @endcan

        <!-- Details + cover / description -->
        @if($book->description || $book->cover_image)
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8 flex flex-col md:flex-row gap-8">
            @if($book->cover_image)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($book->cover_image) }}" alt="Cover" class="w-40 h-56 object-cover rounded-2xl border border-slate-100 dark:border-slate-800 flex-shrink-0">
            @endif
            @if($book->description)
                <div>
                    <h2 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-3">About</h2>
                    <p class="text-slate-600 dark:text-slate-300 font-semibold leading-relaxed">{{ $book->description }}</p>
                </div>
            @endif
        </div>
        @endif

        <!-- Borrowing history (physical) -->
        @if($book->type === 'physical' && $borrowings)
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Check-Out / Check-In Log</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-4">Borrower</th>
                            <th class="text-left px-4 py-4">Issued</th>
                            <th class="text-left px-4 py-4">Returned</th>
                            <th class="text-left px-4 py-4">Issued By</th>
                            <th class="text-center px-4 py-4">Status</th>
                            <th class="text-right px-6 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($borrowings as $borrowing)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                                <td class="px-6 py-4 font-extrabold text-slate-800 dark:text-slate-200">{{ $borrowing->borrower->name ?? '—' }}</td>
                                <td class="px-4 py-4 font-bold text-slate-500 dark:text-slate-400">{{ $borrowing->issued_date?->format('M j, Y') }}</td>
                                <td class="px-4 py-4 font-bold text-slate-500 dark:text-slate-400">{{ $borrowing->returned_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-4 py-4 text-slate-500 dark:text-slate-400">{{ $borrowing->issuedBy->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $borrowing->status === 'borrowed' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-100/50 dark:border-amber-900/30' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30' }}">{{ $borrowing->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($borrowing->returned_date === null)
                                        @can('return books')
                                        <form method="POST" action="{{ route('admin.library.check-in', $borrowing) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Check In</button>
                                        </form>
                                        @endcan
                                    @else
                                        <span class="text-[10px] font-bold text-slate-300 dark:text-slate-600 uppercase tracking-widest">Returned</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">No check-outs yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{ $borrowings->links() }}
        @endif
    </div>
</x-admin-layout>
