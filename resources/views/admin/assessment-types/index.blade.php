<x-admin-layout>
    <x-slot name="header">Assessment Types</x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Assessment Types', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Assessment Types</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.assessment-types.create') }}" class="px-6 py-3 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200 flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add New Type
                </a>
            </div>
        </div>

        <!-- Table Panel -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Category Name</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">System Code</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Description</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Status</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($assessmentTypes as $type)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600 font-black text-xs shadow-sm border border-violet-100/50 group-hover:scale-110 transition-transform">
                                            {{ substr($type->name, 0, 1) }}
                                        </div>
                                        <span class="font-bold text-slate-700">{{ $type->name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1 bg-slate-100 rounded-lg text-[10px] font-black text-slate-500 uppercase tracking-widest border border-slate-200/50">
                                        {{ $type->code }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-sm font-semibold text-slate-500 italic max-w-xs truncate">
                                    {{ $type->description ?? '-' }}
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-3 py-1 rounded-lg {{ $type->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} text-[10px] font-black uppercase tracking-widest">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.assessment-types.edit', $type) }}" class="p-2 hover:bg-white rounded-xl text-slate-400 hover:text-indigo-600 transition-all hover:shadow-sm border border-transparent hover:border-slate-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('admin.assessment-types.destroy', $type) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this assessment type?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 hover:bg-white rounded-xl text-slate-400 hover:text-rose-600 transition-all hover:shadow-sm border border-transparent hover:border-slate-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mb-4">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                        </div>
                                        <p class="text-slate-400 font-bold tracking-tight">No assessment types registered</p>
                                        <p class="text-slate-300 text-xs mt-1">Define categories like Quizzes, Exams, or Projects.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
