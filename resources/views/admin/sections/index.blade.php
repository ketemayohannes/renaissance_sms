<x-admin-layout>
    <x-slot name="header">Sections Management</x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Sections', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Sections</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.sections.bulk-create') }}" class="px-6 py-3 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all border border-slate-200 shadow-sm flex items-center gap-2 group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Bulk Create
                </a>
                <a href="{{ route('admin.sections.import') }}" class="px-6 py-3 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all border border-slate-200 shadow-sm flex items-center gap-2 group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import
                </a>
                <a href="{{ route('admin.sections.create') }}" class="px-6 py-3 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200 flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Section
                </a>
            </div>
        </div>

        @include('admin.layouts.partials.school-structure-tabs')

        <!-- Table Panel -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Section Identity</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Grade & Division</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Academic Year</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Homeroom Teacher</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Capacity</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($sections as $section)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-xs shadow-sm border border-indigo-100/50 group-hover:scale-110 transition-transform">
                                            {{ substr($section->name, 0, 2) }}
                                        </div>
                                        <span class="font-bold text-slate-700">{{ $section->name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-sm font-semibold text-slate-500">
                                    {{ $section->gradeLevel->name }}
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $section->gradeLevel->division->name }}</span>
                                </td>
                                <td class="px-8 py-6 text-sm font-semibold text-slate-600">
                                    {{ $section->academicYear->name }}
                                </td>
                                <td class="px-8 py-6 text-sm font-semibold text-slate-600">
                                    @if($section->homeroomTeacher)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-200 border border-white"></div>
                                            {{ $section->homeroomTeacher->name }}
                                        </div>
                                    @else
                                        <span class="text-slate-300 italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-3 py-1 bg-slate-100 rounded-lg font-black text-slate-700 text-[10px]">{{ $section->capacity }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1 rounded-lg {{ $section->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} text-[10px] font-black uppercase tracking-widest">
                                        {{ $section->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.sections.edit', $section) }}" class="p-2 hover:bg-white rounded-xl text-slate-400 hover:text-indigo-600 transition-all hover:shadow-sm border border-transparent hover:border-slate-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('admin.sections.destroy', $section) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this section?">
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
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        </div>
                                        <p class="text-slate-400 font-bold tracking-tight">No sections available</p>
                                        <p class="text-slate-300 text-xs mt-1">Start by adding your first school section.</p>
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
