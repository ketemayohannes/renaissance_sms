<x-admin-layout>
    <div class="space-y-8" x-data="{ createOpen: false }">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Library', 'url' => route('admin.library.index')],
                    ['label' => 'Catalog', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Library Catalog</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Physical books and digital resources in one place.</p>
            </div>
            @can('manage books')
            <div class="flex items-center gap-3">
                <button @click="createOpen = !createOpen" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-200 dark:shadow-none">
                    + Add Book
                </button>
            </div>
            @endcan
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

        @can('manage books')
        <!-- Add book form -->
        <div x-show="createOpen" x-collapse x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8" x-data="{ type: 'physical' }">
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">Add a Book or Resource</h2>
                <form method="POST" action="{{ route('admin.library.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Type</label>
                        <select name="type" x-model="type" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                            <option value="physical">Physical Book</option>
                            <option value="digital">Digital Resource</option>
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Title</label>
                        <input type="text" name="title" required maxlength="255" value="{{ old('title') }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Author</label>
                        <input type="text" name="author" required maxlength="255" value="{{ old('author') }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Category</label>
                        <input type="text" name="category" maxlength="100" placeholder="Fiction, Science…" value="{{ old('category') }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Publisher</label>
                        <input type="text" name="publisher" maxlength="255" value="{{ old('publisher') }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">ISBN</label>
                        <input type="text" name="isbn" maxlength="50" value="{{ old('isbn') }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold font-mono">
                    </div>

                    <!-- Physical-only -->
                    <div x-show="type === 'physical'">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Copies</label>
                        <input type="number" name="quantity" min="0" value="{{ old('quantity', 1) }}" :required="type === 'physical'" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div x-show="type === 'physical'">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Shelf</label>
                        <input type="text" name="shelf_location" maxlength="100" placeholder="A-12" value="{{ old('shelf_location') }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>

                    <!-- Digital-only -->
                    <div x-show="type === 'digital'" class="xl:col-span-3">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">File (PDF / EPUB, max 50MB)</label>
                        <input type="file" name="file" accept=".pdf,.epub" :required="type === 'digital'" class="w-full text-sm font-bold text-slate-600 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/60 dark:file:text-indigo-300">
                    </div>

                    <div class="xl:col-span-3">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Cover Image (optional)</label>
                        <input type="file" name="cover_image" accept="image/*" class="w-full text-sm font-bold text-slate-600 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:bg-slate-100 file:text-slate-600 dark:file:bg-slate-800 dark:file:text-slate-300">
                    </div>
                    <div class="xl:col-span-6">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Description</label>
                        <textarea name="description" rows="2" maxlength="2000" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">{{ old('description') }}</textarea>
                    </div>
                    <div class="xl:col-span-6 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Save to Catalog</button>
                    </div>
                </form>
            </div>
        </div>
        @endcan

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

        <!-- Catalog table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-4">Title</th>
                            <th class="text-left px-4 py-4">Author</th>
                            <th class="text-left px-4 py-4">Category</th>
                            <th class="text-center px-4 py-4">Type</th>
                            <th class="text-center px-4 py-4">Availability</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($books as $book)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all cursor-pointer" onclick="window.location='{{ route('admin.library.show', $book) }}'">
                                <td class="px-6 py-4">
                                    <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $book->title }}</span>
                                    @unless($book->is_active)
                                        <span class="ml-2 px-2 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 uppercase">Inactive</span>
                                    @endunless
                                </td>
                                <td class="px-4 py-4 font-bold text-slate-500 dark:text-slate-400">{{ $book->author }}</td>
                                <td class="px-4 py-4 font-bold text-slate-500 dark:text-slate-400">{{ $book->category ?? '—' }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $book->type === 'digital' ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300 border-violet-100/50 dark:border-violet-900/30' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-100/50 dark:border-indigo-900/30' }}">{{ $book->type }}</span>
                                </td>
                                <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">
                                    @if($book->type === 'physical')
                                        {{ $book->available_copies }} <span class="text-[10px] font-bold text-slate-400">available / {{ $book->quantity }}</span>
                                    @elseif($book->is_active)
                                        <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-100/50 dark:border-emerald-900/30">Available</span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">Unavailable</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">No books in the catalog yet. Add the first one to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $books->links() }}
    </div>
</x-admin-layout>
