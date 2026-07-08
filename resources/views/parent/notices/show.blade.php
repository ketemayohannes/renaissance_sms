<x-parent-layout header="Announcement Details">
    <div class="space-y-6 max-w-4xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Announcements', 'url' => route('parent.notices.index')],
            ['label' => \Illuminate\Support\Str::limit($notice->title, 40), 'url' => '#']
        ]" />

        <!-- Back Button -->
        <a href="{{ route('parent.notices.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors group">
            <svg class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Announcements
        </a>

        <!-- Main Card Wrapper -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden relative">
            
            <!-- Cover color strip decoration -->
            <div class="h-2.5 bg-gradient-to-r from-violet-500 via-indigo-600 to-indigo-700"></div>

            <!-- Content Area -->
            <div class="p-6 lg:p-8 space-y-6">
                <!-- Metadata & Badge -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                        📢 Official Notice
                    </span>
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-500">
                        <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Published: {{ $notice->publish_date->format('F d, Y') }}</span>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-2xl lg:text-3xl font-black text-slate-800 dark:text-slate-100 font-heading leading-tight">
                    {{ $notice->title }}
                </h1>

                <!-- Author Profile line -->
                <div class="flex items-center gap-3 pb-6 border-b border-slate-50 dark:border-slate-800/60">
                    <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-300 flex items-center justify-center font-bold text-sm">
                        {{ substr($notice->postedBy->name ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <span class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ $notice->postedBy->name ?? 'School Administration' }}</span>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mt-0.5">Author</span>
                    </div>
                </div>

                <!-- Rich Text Content -->
                <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 text-sm leading-relaxed whitespace-pre-line font-medium">
                    {!! nl2br(e($notice->content)) !!}
                </div>

                <!-- Document Attachment Section if exists -->
                @if($notice->attachment)
                    <div class="pt-6 mt-6 border-t border-slate-50 dark:border-slate-800/60">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100/50 dark:border-slate-800 rounded-2xl">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="block text-xs font-black text-slate-800 dark:text-slate-200">Official Document Attachment</span>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase mt-0.5">Format: PDF / Image / Document</span>
                                </div>
                            </div>
                            <a href="/storage/{{ $notice->attachment }}" download class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-xs whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download Attachment
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-parent-layout>