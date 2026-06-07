<x-teacher-layout header="Notice Board">
    <div class="space-y-6">
        <!-- Announcements Header Card -->
        <div class="relative bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 rounded-3xl p-6 lg:p-8 text-white overflow-hidden shadow-lg shadow-emerald-100 dark:shadow-none">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-teal-500/10 rounded-full blur-xl"></div>

            <div class="relative z-10 space-y-2">
                <span class="text-emerald-200 text-xs font-bold tracking-wider uppercase">Renaissance Announcement Board</span>
                <h2 class="text-2xl lg:text-3xl font-black font-heading tracking-tight leading-none">Important Notifications</h2>
                <p class="text-emerald-100 max-w-xl text-sm leading-relaxed pt-1">
                    Stay up-to-date with official events, staff meetings, academic policies, and essential notices published for teachers by the administration.
                </p>
            </div>
        </div>

        <!-- Notices Grid -->
        <div class="grid grid-cols-1 gap-6">
            @forelse($notices as $notice)
                <div class="group relative bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div class="space-y-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                                📢 Staff Notice
                            </span>
                            <h3 class="text-lg font-black text-slate-800 dark:text-slate-100 font-heading leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                {{ $notice->title }}
                            </h3>
                        </div>

                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-500">
                            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Published: {{ $notice->publish_date->format('M d, Y') }}</span>
                            @if($notice->postedBy)
                                <span>•</span>
                                <span>By: {{ $notice->postedBy->name }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Snippet Content -->
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6 font-medium">
                        {{ Str::limit(strip_tags($notice->content), 220) }}
                    </p>

                    <!-- Actions Bar -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-50 dark:border-slate-800/60">
                        <!-- Attachment Download if exists -->
                        <div>
                            @if($notice->attachment)
                                <a href="/storage/{{ $notice->attachment }}" download class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl text-xs font-bold transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download Attachment
                                </a>
                            @else
                                <span class="text-xs text-slate-400 font-medium">No attachments attached</span>
                            @endif
                        </div>

                        <!-- Read Full Announcement -->
                        <a href="{{ route('teacher.notices.show', $notice) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-xs">
                            Read Full Notice
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>

                    <!-- Hover left decorative accent line -->
                    <div class="absolute top-0 bottom-0 left-0 w-1 bg-emerald-600 rounded-l-2xl scale-y-0 group-hover:scale-y-100 transition-transform duration-350 origin-top"></div>
                </div>
            @empty
                <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm">
                    <svg class="w-16 h-16 text-slate-200 dark:text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2M9 5h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 font-heading">No notices available</h3>
                    <p class="text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto text-sm">There are currently no active notices targeting teachers.</p>
                </div>
            @endforelse
        </div>
        
        <div class="pt-4">
            {{ $notices->links() }}
        </div>
    </div>
</x-teacher-layout>
