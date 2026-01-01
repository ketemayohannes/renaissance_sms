<x-admin-layout>
    <x-slot name="header">Terms Management</x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Terms', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Terms</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.terms.create') }}" class="px-6 py-3 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200 flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Term
                </a>
            </div>
        </div>

        @include('admin.layouts.partials.academic-setup-tabs')

        <!-- Table Panel -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Academic Year</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Term Name</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Type</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Components</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Dates</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Grading Status</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($terms as $term)
                            <tr class="group hover:bg-slate-50/50 transition-all {{ $term->isSemester() ? 'bg-blue-50/10' : '' }}">
                                <td class="px-8 py-6 text-sm font-semibold text-slate-500 whitespace-nowrap">
                                    {{ $term->academicYear->name }}
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700">{{ $term->name }}</span>
                                        @if($term->semester)
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider mt-0.5 italic">Part of {{ $term->semester->name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1 rounded-lg {{ $term->isQuarter() ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600' }} text-[10px] font-black uppercase tracking-widest">
                                        {{ $term->isQuarter() ? 'Quarter' : 'Semester' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    @if($term->isSemester() && $term->quarters->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($term->quarters as $quarter)
                                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[9px] font-black text-slate-500 uppercase tracking-widest border border-slate-200/50">
                                                    {{ $quarter->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-[11px] font-semibold text-slate-600">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-slate-400 uppercase text-[9px] font-black tracking-widest">Start: {{ $term->start_date->format('M d, Y') }}</span>
                                        <span class="text-slate-400 uppercase text-[9px] font-black tracking-widest">End: {{ $term->end_date->format('M d, Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full {{ $term->is_grading_open ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></div>
                                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Subject: {{ $term->is_grading_open ? 'Open' : 'Closed' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full {{ $term->is_master_grading_open ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></div>
                                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Master: {{ $term->is_master_grading_open ? 'Open' : 'Closed' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.terms.edit', $term) }}" class="p-2 hover:bg-white rounded-xl text-slate-400 hover:text-indigo-600 transition-all hover:shadow-sm border border-transparent hover:border-slate-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('admin.terms.destroy', $term) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this term?">
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
                                <td colspan="7" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mb-4">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path></svg>
                                        </div>
                                        <p class="text-slate-400 font-bold tracking-tight">No terms available</p>
                                        <p class="text-slate-300 text-xs mt-1">Foundational data required for operation.</p>
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
