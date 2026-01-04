<x-admin-layout title="Parent Management">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Parent Management</h1>
                <p class="text-slate-500 mt-1">Manage parent information and portal access</p>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white shadow-xl shadow-slate-200/50">
            <form action="{{ route('admin.guardians.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        class="block w-full pl-11 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 transition-all"
                        placeholder="Search by name, phone, email or student ID...">
                </div>
                <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                    <span>Search</span>
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.guardians.index') }}" class="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-2xl transition-all flex items-center justify-center">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Guardians Table -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                            <th class="py-6 px-8">Parent Name</th>
                            <th class="py-6 px-4">Contact</th>
                            <th class="py-6 px-4">Linked Student</th>
                            <th class="py-6 px-4">Status</th>
                            <th class="py-6 px-8 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($guardians as $guardian)
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="py-6 px-8">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl overflow-hidden bg-slate-100 flex-shrink-0 border-2 border-white shadow-sm">
                                            @if($guardian->photo)
                                                <img src="{{ Storage::url($guardian->photo) }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="h-full w-full flex items-center justify-center text-slate-400 font-black text-lg">
                                                    {{ substr($guardian->first_name, 0, 1) }}{{ substr($guardian->father_name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-700">{{ $guardian->full_name }}</div>
                                            <div class="text-xs text-slate-400 font-medium uppercase tracking-tight">{{ $guardian->relationship }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-4">
                                    <div class="text-sm font-semibold text-slate-600">{{ $guardian->phone }}</div>
                                    <div class="text-xs text-slate-400">{{ $guardian->email ?? 'No email' }}</div>
                                </td>
                                <td class="py-6 px-4">
                                    <div class="flex flex-wrap gap-2 max-w-md">
                                        @foreach($guardian->linked_students as $linkedStudent)
                                            <a href="{{ route('admin.students.show', $linkedStudent) }}" 
                                               class="group/link flex items-center gap-2 bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-100 px-3 py-1.5 rounded-xl transition-all">
                                                <div class="h-6 w-6 rounded-lg bg-indigo-100 flex items-center justify-center text-[10px] font-black text-indigo-600">
                                                    {{ substr($linkedStudent->first_name, 0, 1) }}
                                                </div>
                                                <div class="text-[10px]">
                                                    <div class="font-bold text-slate-700 group-hover/link:text-indigo-600 transition-colors">{{ $linkedStudent->full_name }}</div>
                                                    <div class="text-slate-400 font-medium uppercase tracking-tighter">
                                                        {{ $linkedStudent->student_id }} • {{ $linkedStudent->enrollments->first()->section->gradeLevel->name ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-6 px-4">
                                    @if($guardian->user_id)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Portal Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                            Read Only
                                        </span>
                                    @endif
                                </td>
                                <td class="py-6 px-8 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.guardians.show', $guardian) }}" 
                                            class="h-10 w-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm border border-indigo-100"
                                            title="View Profile">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.guardians.edit', $guardian) }}" 
                                            class="h-10 w-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all shadow-sm border border-amber-100"
                                            title="Edit Details">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="h-20 w-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mb-4">
                                            <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="17 20h5 v-2a3 3 0 00-5.356-1.857M17 20 H7 m10 0 v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20 v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-bold text-slate-700">No parents found</h3>
                                        <p class="text-slate-400 max-w-xs mt-2">We couldn't find any parent records matching your search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($guardians->hasPages())
                <div class="px-8 py-6 border-t border-slate-50 bg-slate-50/30">
                    {{ $guardians->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
