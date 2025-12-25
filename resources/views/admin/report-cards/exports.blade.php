<x-admin-layout>
    <x-slot name="header">Background Exports ✨</x-slot>

    <div class="space-y-6">
        <div class="sm:flex sm:justify-between sm:items-center">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Background Exports ✨</h1>
                <p class="mt-1 text-sm text-slate-500">Monitor and download your bulk report card exports.</p>
            </div>
            
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.section-grades.index') }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Grade Entry
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-2 bg-emerald-100 border border-emerald-200 text-emerald-700 rounded-lg">
            {{ session('success') }}
        </div>
        @endif

        <!-- Table -->
        <div class="bg-white shadow-lg rounded-sm border border-slate-200">
            <header class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800">Export Requests <span class="text-slate-400 font-medium">{{ $exports->total() }}</span></h2>
            </header>
            <div class="p-3">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Requested At</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Details</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Status</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Completed</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-right">Action</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @forelse($exports as $export)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $export->created_at->format('M d, Y H:i') }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">
                                        {{ $export->params['section_name'] ?? 'N/A' }} - {{ $export->params['term_name'] ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-slate-400">Section Report Cards ZIP</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @if($export->status === 'pending')
                                        <div class="inline-flex font-medium bg-slate-100 text-slate-500 rounded-full text-center px-2.5 py-0.5">Pending</div>
                                    @elseif($export->status === 'processing')
                                        <div class="inline-flex font-medium bg-blue-100 text-blue-600 rounded-full text-center px-2.5 py-0.5 animate-pulse">Processing...</div>
                                    @elseif($export->status === 'completed')
                                        <div class="inline-flex font-medium bg-emerald-100 text-emerald-600 rounded-full text-center px-2.5 py-0.5">Ready</div>
                                    @else
                                        <div class="inline-flex font-medium bg-rose-100 text-rose-600 rounded-full text-center px-2.5 py-0.5" title="{{ $export->error_message }}">Failed</div>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-slate-500 text-xs">{{ $export->completed_at ? $export->completed_at->format('H:i:s') : '-' }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-right">
                                        @if($export->status === 'completed')
                                        <a href="{{ route('admin.report-cards.download-export', $export) }}" class="text-indigo-500 hover:text-indigo-600 font-medium">Download ZIP</a>
                                        @elseif($export->status === 'failed')
                                        <span class="text-slate-400 italic">Error</span>
                                        @else
                                        <button class="text-slate-400 cursor-not-allowed" disabled>Wait...</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-2 py-8 text-center text-slate-500 italic"> No export requests found. </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-8">
            {{ $exports->links() }}
        </div>

    </div>

    @push('scripts')
    @if($exports->whereIn('status', ['pending', 'processing'])->count() > 0)
    <script>
        // Simple auto-refresh for active jobs
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    </script>
    @endif
    @endpush
    </div>
</x-admin-layout>
