<x-parent-layout header="Contact Teacher">
    <div x-data="{
        selectedStudentId: '{{ old('student_id', '') }}',
        children: @js($children->map(fn($c) => [
            'id' => $c->id,
            'full_name' => $c->full_name,
            'grade' => $c->currentEnrollment?->section?->gradeLevel?->name ?? 'N/A',
            'section' => $c->currentEnrollment?->section?->name ?? 'N/A',
            'teacher_name' => $c->currentEnrollment?->section?->homeroomTeacher?->name ?? null,
            'teacher_email' => $c->currentEnrollment?->section?->homeroomTeacher?->email ?? null,
        ])),
        get selectedChild() {
            return this.children.find(c => c.id == this.selectedStudentId) || null;
        },
        getInitials(name) {
            if (!name) return '??';
            let parts = name.split(' ');
            let initials = '';
            for (let i = 0; i < Math.min(parts.length, 2); i++) {
                if (parts[i]) initials += parts[i].charAt(0).toUpperCase();
            }
            return initials || '?';
        }
    }" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left Column: Teacher Info Card -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Dynamic Teacher Info -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/50 shadow-sm overflow-hidden transition-all duration-300">
                <div class="h-24 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
                <div class="px-6 pb-6 relative">
                    <!-- Avatar / Icon -->
                    <div class="absolute -top-12 left-6">
                        <template x-if="selectedChild && selectedChild.teacher_name">
                            <div class="w-20 h-20 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-2xl border-4 border-white dark:border-slate-800 shadow-md uppercase tracking-wider animate-fade-in" x-text="getInitials(selectedChild.teacher_name)"></div>
                        </template>
                        <template x-if="!selectedChild || !selectedChild.teacher_name">
                            <div class="w-20 h-20 rounded-2xl bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 flex items-center justify-center border-4 border-white dark:border-slate-800 shadow-md">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </template>
                    </div>

                    <!-- Teacher details -->
                    <div class="pt-12 mt-2">
                        <template x-if="selectedChild">
                            <div>
                                <template x-if="selectedChild.teacher_name">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 mb-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Homeroom Teacher
                                        </span>
                                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 font-heading leading-tight" x-text="selectedChild.teacher_name"></h3>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span x-text="selectedChild.teacher_email"></span>
                                        </p>
                                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/50 flex flex-wrap gap-2">
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded text-xs font-medium" x-text="'Student: ' + selectedChild.full_name"></span>
                                            <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded text-xs font-medium" x-text="'Grade ' + selectedChild.grade + ' - ' + selectedChild.section"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!selectedChild.teacher_name">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 mb-2">
                                            ⚠️ Not Assigned
                                        </span>
                                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-tight">No Teacher Assigned</h3>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                                            There is no homeroom teacher assigned to <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="selectedChild.full_name"></span>'s section (<span x-text="selectedChild.grade + ' - ' + selectedChild.section"></span>) yet.
                                        </p>
                                        <p class="text-xs text-rose-500 dark:text-rose-400/80 mt-3 bg-rose-50/50 dark:bg-rose-950/20 p-2.5 rounded-xl border border-rose-100 dark:border-rose-900/30 animate-pulse">
                                            You cannot send messages through the portal for this child. Please reach out to the school administration office.
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!selectedChild">
                            <div class="text-center py-6">
                                <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300">Select a Student</h3>
                                <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">
                                    Choose a child from the dropdown to display their homeroom teacher's details.
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Guidelines Card -->
            <div class="bg-gradient-to-br from-indigo-900 to-slate-950 text-white rounded-2xl p-6 shadow-sm border border-slate-800 relative overflow-hidden">
                <!-- Background decorative shapes -->
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl"></div>
                <div class="absolute -left-10 -top-10 w-40 h-40 bg-purple-500/10 rounded-full blur-2xl"></div>
                
                <h4 class="font-bold text-base font-heading mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Communication Guidelines
                </h4>
                <ul class="space-y-3 text-xs text-slate-300">
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-400 mt-0.5">•</span>
                        <span>Messages are delivered directly to the teacher's official school email inbox.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-400 mt-0.5">•</span>
                        <span>For emergency situations, school fee questions, or registration, please contact the administration office.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-400 mt-0.5">•</span>
                        <span>Please maintain a respectful and professional tone in all school communications.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-400 mt-0.5">•</span>
                        <span>Response times may vary. Teachers typically check messages outside of instruction hours.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Column: Form -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/50 shadow-lg p-6 lg:p-8 transition-all duration-300">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 font-heading">Send a Message</h2>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Fill out the form below to message your child's homeroom teacher.</p>
                </div>

                <form method="POST" action="{{ route('parent.contact.send') }}" class="space-y-6">
                    @csrf

                    <!-- Child Selection -->
                    <div>
                        <label for="student_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Select Child <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="student_id" 
                                    id="student_id" 
                                    x-model="selectedStudentId"
                                    style="background-image: none;"
                                    class="w-full pl-4 pr-10 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800 dark:text-slate-100 transition-all duration-200 appearance-none font-medium text-sm"
                                    required>
                                <option value="" class="text-slate-400">Choose a child...</option>
                                <template x-for="child in children" :key="child.id">
                                    <option :value="child.id" x-text="child.full_name + ' (' + child.grade + ' - Section ' + child.section + ')'"></option>
                                </template>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        @error('student_id')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subject <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="subject" 
                                   id="subject"
                                   value="{{ old('subject') }}"
                                   placeholder="What is your message about?"
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 transition-all duration-200 font-medium text-sm"
                                   required>
                        </div>
                        @error('subject')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Message Body -->
                    <div>
                        <label for="message" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Message <span class="text-rose-500">*</span></label>
                        <textarea name="message" 
                                  id="message" 
                                  rows="6" 
                                  placeholder="Write your message details here..."
                                  class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 transition-all duration-200 text-sm leading-relaxed"
                                  required>{{ old('message') }}</textarea>
                        @error('message')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="pt-2 flex items-center justify-between gap-4">
                        <span class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            All fields marked with <span class="text-rose-500 font-bold">*</span> are required.
                        </span>
                        <button type="submit" 
                                :disabled="!selectedChild || !selectedChild.teacher_name"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:from-slate-400 disabled:to-slate-400 disabled:cursor-not-allowed dark:disabled:from-slate-700 dark:disabled:to-slate-700 transition-all duration-200 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-parent-layout>