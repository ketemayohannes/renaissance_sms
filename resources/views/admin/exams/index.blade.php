<x-admin-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Exam Paper Review</h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">Review and approve exam papers submitted by teachers.</p>
        </div>
    </x-slot>

    <div class="py-12 space-y-8">
        <!-- List -->
        <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Teacher</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Paper Details</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Submitted</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($exams as $exam)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-xs uppercase tracking-tighter shadow-inner">
                                            {{ substr($exam->teacher->first_name, 0, 1) }}{{ substr($exam->teacher->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-slate-900 leading-tight">{{ $exam->teacher->full_name }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Staff ID: {{ $exam->teacher->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="text-sm font-black text-slate-900 leading-tight">{{ $exam->title }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $exam->gradeLevel->name }} • {{ $exam->subject->name }}</div>
                                </td>
                                <td class="px-6 py-6">
                                    @php
                                        $statusColors = [
                                            'submitted' => 'bg-amber-100 text-amber-600',
                                            'under_review' => 'bg-indigo-100 text-indigo-600',
                                            'approved' => 'bg-emerald-100 text-emerald-600',
                                            'rejected' => 'bg-rose-100 text-rose-600',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $statusColors[$exam->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ str_replace('_', ' ', $exam->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="text-xs font-bold text-slate-600">{{ $exam->submitted_at?->format('M d, Y') ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $exam->submitted_at?->format('H:i A') ?? '' }}</div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.exams.show', $exam) }}" class="px-4 py-2 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200">
                                            Review Paper
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-12 text-center text-slate-500 font-medium italic">No exam papers submitted for review.</td>
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
</x-admin-layout>
