<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Examination Interface') }}
        </h2>
    </x-slot>

    <div class="px-6 py-8" x-data="{ 
    timeLeft: {{ $activity->config['time_limit'] ?? 60 }} * 60,
    timerInterval: null,
    formatTime() {
        const h = Math.floor(this.timeLeft / 3600);
        const m = Math.floor((this.timeLeft % 3600) / 60);
        const s = this.timeLeft % 60;
        return `${h > 0 ? h + ':' : ''}${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    },
    startTimer() {
        this.timerInterval = setInterval(() => {
            if (this.timeLeft > 0) {
                this.timeLeft--;
                if (this.timeLeft < 300) { // 5 mins left warning
                    document.getElementById('timer-box').classList.add('animate-alert');
                }
            } else {
                this.autoSubmit();
            }
        }, 1000);
    },
    autoSubmit() {
        clearInterval(this.timerInterval);
        document.getElementById('exam-form').submit();
    }
}" x-init="startTimer()">
    
    <!-- Floating Focused Header -->
    <div class="fixed top-20 left-1/2 -translate-x-1/2 z-[100] w-full max-w-4xl px-6">
        <div class="bg-slate-900/90 backdrop-blur-xl rounded-full px-8 py-4 flex items-center justify-between shadow-2xl border border-slate-700/50">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                    E
                </div>
                <div>
                    <h2 class="text-white font-bold text-sm leading-tight line-clamp-1">{{ $activity->title }}</h2>
                    <p class="text-slate-400 text-[10px] uppercase font-bold tracking-widest">{{ $activity->teacherAssignment->subject->name }}</p>
                </div>
            </div>
            
            <div id="timer-box" class="flex items-center gap-3 px-6 py-2 bg-slate-800 rounded-full border border-slate-700 transition-all duration-300">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-white font-mono font-bold text-xl tabular-nums" x-text="formatTime()"></span>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto pt-24 pb-32">
        <form id="exam-form" action="{{ route('student.activities.exam.submit', $activity) }}" method="POST">
            @csrf
            
            <div class="space-y-12 mt-12">
                @foreach($activity->questions->sortBy('order') as $index => $question)
                <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] p-10 shadow-sm relative group overflow-hidden">
                    <!-- Question Marker -->
                    <div class="absolute top-0 left-0 w-2 h-full bg-slate-100 group-hover:bg-indigo-500 transition-colors"></div>
                    
                    <div class="flex items-start gap-6">
                        <span class="text-4xl font-extrabold text-slate-100 group-hover:text-indigo-50 transition-colors pointer-events-none mt-[-10px]">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-slate-900 leading-relaxed mb-8">
                                {{ $question->question_text }}
                            </h3>
                            
                            <!-- Answer Options -->
                            <div class="space-y-4">
                                @if($question->type === 'mcq')
                                    @foreach($question->options as $oIndex => $option)
                                    <label class="block relative cursor-pointer">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $oIndex }}" class="peer sr-only" required>
                                        <div class="p-5 rounded-3xl border-2 border-slate-50 bg-slate-50/50 hover:bg-white hover:border-indigo-100 transition-all peer-checked:border-indigo-500 peer-checked:bg-white peer-checked:ring-4 peer-checked:ring-indigo-50 flex items-center gap-4">
                                            <div class="w-8 h-8 rounded-xl border border-slate-200 bg-white flex items-center justify-center text-slate-400 font-bold text-xs peer-checked:border-indigo-500 peer-checked:text-indigo-600 transition-all">
                                                {{ chr(65 + $oIndex) }}
                                            </div>
                                            <span class="font-semibold text-slate-700">{{ $option }}</span>
                                        </div>
                                    </label>
                                    @endforeach
                                @elseif($question->type === 'tf')
                                    <div class="grid grid-cols-2 gap-4">
                                        @foreach(['True', 'False'] as $val)
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $val }}" class="peer sr-only" required>
                                            <div class="p-6 text-center rounded-[2rem] border-2 border-slate-50 bg-slate-50/50 hover:bg-white transition-all peer-checked:border-indigo-500 peer-checked:bg-white peer-checked:ring-4 peer-checked:ring-indigo-50">
                                                <span class="font-bold text-slate-700 text-lg uppercase tracking-widest">{{ $val }}</span>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                @elseif($question->type === 'short_answer' || $question->type === 'essay')
                                    <textarea name="answers[{{ $question->id }}]" rows="5" required
                                              class="w-full bg-slate-50/50 border-2 border-slate-50 rounded-[2rem] p-6 font-semibold text-slate-700 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 focus:bg-white transition-all"
                                              placeholder="Type your response here..."></textarea>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1 rounded-full">
                            Points: {{ $question->points }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Footer Navigation -->
            <div class="mt-16 flex items-center justify-between bg-white border border-slate-200 p-8 rounded-[3rem] shadow-xl shadow-slate-200/50">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Progress</span>
                    <p class="text-sm font-bold text-slate-900">Ensure all questions are answered before submitting.</p>
                </div>
                <button type="button" @click="window.confirmUI({message: 'Are you sure you want to finish the exam?', title: 'Submit Exam', type: 'warning', buttonText: 'Yes, Submit', callback: () => autoSubmit()})" 
                        class="px-12 py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[2rem] font-black text-lg shadow-2xl shadow-indigo-200 transition-all transform hover:-translate-y-1 active:scale-95">
                    Finish & Submit Examination
                </button>
            </div>
        </form>
    </div>

    <!-- Security Overlay (Optional/Future: Tab Blur Warning) -->
    <div x-data="{ blurred: false }" @window:blur="blurred = true" @window:focus="blurred = false"
         x-show="blurred" class="fixed inset-0 z-[200] bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-6 text-center">
        <div class="max-w-md bg-white rounded-[2.5rem] p-10 shadow-2xl">
            <div class="w-20 h-20 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Security Alert</h2>
            <p class="text-slate-500 font-medium">Switching tabs or windows is logged. Please return to the exam immediately to avoid automatic disqualification.</p>
        </div>
    </div>
</div>
</x-app-layout>
