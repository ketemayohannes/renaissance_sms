<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-800 font-heading">Question Manager</h1>
    </x-slot>

    <div class="px-6 py-8" x-data="{ 
    questions: {{ json_encode($activity->questions ?? []) }},
    addQuestion() {
        this.questions.push({
            question_text: '',
            type: 'mcq',
            points: 1,
            order: this.questions.length,
            options: ['', '', '', ''],
            correct_answer: ''
        });
    },
    removeQuestion(index) {
        this.questions.splice(index, 1);
    }
}">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.activities.show', $activity) }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Activity
        </a>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 font-heading">Question Manager</h1>
                <p class="text-slate-500 mt-1">Configuring questions for: <span class="font-bold text-slate-700">{{ $activity->title }}</span></p>
            </div>
            <button @click="addQuestion()" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Question
            </button>
        </div>
    </div>

    <form action="{{ route('admin.activities.questions.store', $activity) }}" method="POST">
        @csrf
        <div class="space-y-6">
            <template x-for="(q, index) in questions" :key="index">
                <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] p-8 shadow-sm relative group">
                    <button type="button" @click="removeQuestion(index)" class="absolute top-6 right-6 w-10 h-10 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:bg-rose-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Question Text -->
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1" x-text="'Question ' + (index + 1)"></label>
                            <textarea :name="'questions['+index+'][question_text]'" x-model="q.question_text" required
                                      class="w-full bg-slate-50 border-0 rounded-2xl p-4 font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all"
                                      placeholder="Enter your question here..."></textarea>
                        </div>

                        <!-- Points & Type -->
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">Points</label>
                                <input type="number" :name="'questions['+index+'][points]'" x-model="q.points" step="0.5" required
                                       class="w-full h-12 bg-slate-50 border-0 rounded-xl px-4 font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">Type</label>
                                <select :name="'questions['+index+'][type]'" x-model="q.type" required
                                        class="w-full h-12 bg-slate-50 border-0 rounded-xl px-4 font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="mcq">Multiple Choice</option>
                                    <option value="tf">True / False</option>
                                    <option value="short_answer">Short Answer</option>
                                    <option value="essay">Essay</option>
                                </select>
                            </div>
                            <input type="hidden" :name="'questions['+index+'][order]'" :value="index">
                        </div>

                        <!-- Options for MCQ -->
                        <template x-if="q.type === 'mcq'">
                            <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <template x-for="(opt, oIndex) in q.options" :key="oIndex">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" :name="'questions['+index+'][correct_answer]'" :value="oIndex" x-model="q.correct_answer" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                        <input type="text" :name="'questions['+index+'][options]['+oIndex+']'" x-model="q.options[oIndex]" placeholder="Option text..."
                                               class="flex-1 h-12 bg-slate-50/50 border border-slate-100 rounded-xl px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500 transition-all">
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            @if($activity->questions->count() == 0)
            <div x-show="questions.length === 0" class="py-20 text-center bg-white/40 backdrop-blur rounded-[2.5rem] border border-dashed border-slate-300">
                <p class="text-slate-400 font-medium italic">No questions added yet. Get started by adding your first question.</p>
            </div>
            @endif

            <!-- Final Actions -->
            <div class="flex items-center justify-end gap-4 mt-12 pb-20">
                <button type="submit" class="px-12 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[2rem] font-bold shadow-xl shadow-emerald-100 transition-all transform hover:-translate-y-1 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save All Questions
                </button>
            </div>
        </div>
    </form>
</div>
</x-admin-layout>
