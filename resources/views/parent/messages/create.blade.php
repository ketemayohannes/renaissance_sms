<x-parent-layout header="New Message">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('parent.messages.index') }}" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-black text-slate-800 dark:text-slate-100">New Message</h2>
        </div>

        <div x-data="{
            selectedStudentId: '{{ old('student_id', '') }}',
            children: @js($children->map(fn($c) => [
                'id'           => $c->id,
                'full_name'    => $c->full_name,
                'grade'        => $c->currentEnrollment?->section?->gradeLevel?->name ?? 'N/A',
                'section'      => $c->currentEnrollment?->section?->name ?? 'N/A',
                'teacher_name' => $c->currentEnrollment?->section?->homeroomTeacher?->name ?? null,
            ])),
            get selectedChild() {
                return this.children.find(c => c.id == this.selectedStudentId) || null;
            }
        }" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Teacher info card --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="h-20 bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-500"></div>
                    <div class="px-5 pb-5 relative">
                        <div class="absolute -top-8 left-5">
                            <template x-if="selectedChild && selectedChild.teacher_name">
                                <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-xl border-4 border-white dark:border-slate-900 shadow-lg uppercase" x-text="selectedChild.teacher_name.charAt(0)"></div>
                            </template>
                            <template x-if="!selectedChild || !selectedChild.teacher_name">
                                <div class="w-16 h-16 rounded-2xl bg-slate-200 dark:bg-slate-700 border-4 border-white dark:border-slate-900 shadow-lg flex items-center justify-center">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            </template>
                        </div>
                        <div class="pt-10">
                            <template x-if="selectedChild && selectedChild.teacher_name">
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-600 mb-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Homeroom Teacher
                                    </span>
                                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100" x-text="selectedChild.teacher_name"></h3>
                                    <p class="text-xs text-slate-500 mt-1" x-text="'Grade ' + selectedChild.grade + ' — Section ' + selectedChild.section"></p>
                                </div>
                            </template>
                            <template x-if="!selectedChild">
                                <p class="text-sm text-slate-400 font-semibold">Select a child to see their teacher.</p>
                            </template>
                            <template x-if="selectedChild && !selectedChild.teacher_name">
                                <p class="text-sm text-rose-500 font-bold">No homeroom teacher assigned to this section yet.</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compose form --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    @if(session('success'))
                        <div class="mb-4 px-4 py-3 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('parent.messages.store') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">Select Child <span class="text-rose-500">*</span></label>
                            <select name="student_id" x-model="selectedStudentId"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" required>
                                <option value="">Choose a child...</option>
                                @foreach($children as $child)
                                    <option value="{{ $child->id }}" {{ old('student_id') == $child->id ? 'selected' : '' }}>
                                        {{ $child->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">Subject <span class="text-rose-500">*</span></label>
                            <input type="text" name="subject" value="{{ old('subject') }}" placeholder="What is this about?"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 font-semibold text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" required>
                            @error('subject') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">Message <span class="text-rose-500">*</span></label>
                            <textarea name="body" rows="6" placeholder="Write your message here..."
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 font-medium text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all leading-relaxed" required>{{ old('body') }}</textarea>
                            @error('body') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                :disabled="!selectedChild || !selectedChild.teacher_name"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-parent-layout>
