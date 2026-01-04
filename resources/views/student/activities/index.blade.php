<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Academic Activities') }}
        </h2>
    </x-slot>

    <div class="px-6 py-8">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 font-heading">My Academic Activities</h1>
            <p class="text-slate-500 mt-1">Track your upcoming homework, assignments, and exams.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-2xl text-sm font-bold border border-indigo-100 italic">
                Focus on your goals!
            </span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        @php
            $pending = $activities->filter(fn($a) => !$a->submissions->where('student_id', Auth::user()->student->id)->count())->count();
            $completed = $activities->count() - $pending;
        @endphp
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 p-6 rounded-[2rem] text-white shadow-xl shadow-indigo-100">
            <p class="text-indigo-100 text-xs font-bold uppercase tracking-widest mb-1">Pending Tasks</p>
            <h3 class="text-4xl font-bold">{{ $pending }}</h3>
        </div>
        <div class="bg-white border border-slate-200 p-6 rounded-[2rem] shadow-sm">
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1 text-primary">Completed</p>
            <h3 class="text-4xl font-bold text-slate-800">{{ $completed }}</h3>
        </div>
        <div class="bg-white border border-slate-200 p-6 rounded-[2rem] shadow-sm">
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1 text-primary">Total Assigned</p>
            <h3 class="text-4xl font-bold text-slate-800">{{ $activities->count() }}</h3>
        </div>
    </div>

    <!-- Activities Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @forelse($activities as $activity)
        @php
            $submission = $activity->submissions->where('student_id', Auth::user()->student->id)->first();
            $isOverdue = !$submission && $activity->due_date->isPast();
            
            $typeColors = [
                'homework' => 'emerald',
                'assignment' => 'indigo',
                'exam' => 'rose',
            ];
            $color = $typeColors[$activity->type] ?? 'slate';
        @endphp
        <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] p-8 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
            <!-- Type Border -->
            <div class="absolute top-0 left-0 w-full h-2 bg-{{ $color }}-500"></div>

            <div class="flex items-center justify-between mb-6">
                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-{{ $color }}-100 text-{{ $color }}-700">
                    {{ $activity->type }}
                </span>
                @if($submission)
                <span class="flex items-center gap-1.5 text-emerald-600 font-bold text-xs uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Submitted
                </span>
                @elseif($isOverdue)
                <span class="flex items-center gap-1.5 text-rose-500 font-bold text-xs uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Overdue
                </span>
                @endif
            </div>

            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-900 line-clamp-1 group-hover:text-{{ $color }}-600 transition-colors">{{ $activity->title }}</h3>
                <p class="text-slate-400 text-sm mt-1 uppercase font-bold tracking-tighter">{{ $activity->teacherAssignment->subject->name }}</p>
            </div>

            <div class="space-y-4 mb-8">
                <div class="flex items-center gap-3 text-slate-500">
                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Due: {{ $activity->due_date->format('M d, h:i A') }}</span>
                </div>
                <div class="flex items-center gap-3 text-slate-500">
                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Points: {{ $activity->max_score }}</span>
                </div>
            </div>

            <a href="{{ route('student.activities.show', $activity) }}" 
               class="w-full py-4 bg-slate-50 hover:bg-{{ $color }}-600 hover:text-white text-slate-600 rounded-3xl font-bold transition-all flex items-center justify-center gap-2">
                {{ $submission ? 'View Feedback' : 'Open Activity' }}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>
        @empty
        <div class="col-span-full py-20 text-center">
            <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-primary">
                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900">All caught up!</h3>
            <p class="text-slate-500 mt-2">No active activities found for your section today.</p>
        </div>
        @endforelse
    </div>
</div>
</x-app-layout>
