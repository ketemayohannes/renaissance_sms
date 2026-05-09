<x-teacher-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Exam Management</h1>
                <p class="text-slate-500 text-sm mt-1 font-medium">Prepare and submit your official exam papers for review.</p>
            </div>
            <a href="{{ route('teacher.exams.create') }}" class="px-6 py-3 bg-indigo-600 text-white font-bold text-sm rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group">
                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Paper
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Status Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $stats = [
                    ['label' => 'Total Submissions', 'count' => $exams->total(), 'color' => 'indigo'],
                    ['label' => 'Approved', 'count' => $exams->where('status', 'approved')->count(), 'color' => 'emerald'],
                    ['label' => 'Under Review', 'count' => $exams->where('status', 'under_review')->count() + $exams->where('status', 'submitted')->count(), 'color' => 'amber'],
                    ['label' => 'Drafts', 'count' => $exams->where('status', 'draft')->count(), 'color' => 'slate'],
                ];
            @endphp

            @foreach($stats as $stat)
                <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/50 flex items-center gap-6">
                    <div class="w-14 h-14 rounded-2xl bg-{{ $stat['color'] }}-50 flex items-center justify-center text-{{ $stat['color'] }}-600 shadow-inner">
                        <span class="text-2xl font-black">{{ $stat['count'] }}</span>
                    </div>
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-widest leading-tight">{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>

        <!-- Exam List -->
        <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Paper Details</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Grade & Subject</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Last Action</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($exams as $exam)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-white group-hover:shadow-md transition-all">
                                            @if($exam->type === 'text')
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            @else
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.707 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-slate-900 leading-tight">{{ $exam->title }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $exam->term->name }} • {{ $exam->type === 'text' ? 'Online Editor' : 'File Upload' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="text-xs font-bold text-slate-700">{{ $exam->gradeLevel->name }}</div>
                                    <div class="text-[10px] font-black text-indigo-500 uppercase tracking-tighter mt-0.5">{{ $exam->subject->name }}</div>
                                </td>
                                <td class="px-6 py-6">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-slate-100 text-slate-600',
                                            'submitted' => 'bg-amber-100 text-amber-600',
                                            'under_review' => 'bg-indigo-100 text-indigo-600',
                                            'approved' => 'bg-emerald-100 text-emerald-600',
                                            'rejected' => 'bg-rose-100 text-rose-600',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $statusColors[$exam->status] }}">
                                        {{ str_replace('_', ' ', $exam->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="text-xs font-bold text-slate-600">{{ $exam->updated_at->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $exam->updated_at->format('H:i A') }}</div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($exam->file_path)
                                            <a href="{{ route('teacher.exams.download', $exam) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Download Paper">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>
                                        @endif
                                        
                                        @if(in_array($exam->status, ['draft', 'rejected']))
                                            <a href="{{ route('teacher.exams.edit', $exam) }}" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit Paper">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <form action="{{ route('teacher.exams.destroy', $exam) }}" method="POST" class="inline" id="delete-exam-{{ $exam->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" x-data @click="window.confirmUI({title: 'Delete Exam', message: 'Are you sure you want to permanently delete this exam paper?', type: 'danger', buttonText: 'Yes, Delete', callback: () => document.getElementById('delete-exam-{{ $exam->id }}').submit()})" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete Paper">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('teacher.exams.show', $exam) }}" class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-all" title="View Paper">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-12 text-center text-slate-500 font-medium italic">No exam papers found. Create your first one to begin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($exams->hasPages())
                <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
                    {{ $exams->links() }}
                </div>
            @endif
        </div>
    </div>
</x-teacher-layout>
