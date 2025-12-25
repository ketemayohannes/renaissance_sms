<x-admin-layout>
    <x-slot name="header">Promotion History</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-slate-200">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Promotion History</h2>
                <p class="text-sm text-slate-500">Review past promotion and retention records.</p>
            </div>
            <a href="{{ route('admin.promotions.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </a>
        </div>

        <x-breadcrumb :items="[
            ['label' => 'Promotions', 'url' => route('admin.promotions.index')],
            ['label' => 'History', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">
                    @if($promotions->isEmpty())
                        <p class="text-gray-500 italic">No promotion records found hideously.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Processed By</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($promotions as $promo)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $promo->student->full_name ?? 'N/A' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            {{ $promo->fromGradeLevel->name ?? '-' }}<br>
                                            <span class="text-xs text-gray-500">{{ $promo->fromAcademicYear->name ?? '' }}</span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            {{ $promo->toGradeLevel->name ?? '-' }}<br>
                                            <span class="text-xs text-gray-500">{{ $promo->toAcademicYear->name ?? '' }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            @if($promo->status === 'promoted')
                                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Promoted</span>
                                            @elseif($promo->status === 'retained')
                                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Retained</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Conditional</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">{{ $promo->remarks ?? '-' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $promo->processor->name ?? 'System' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $promo->created_at->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $promotions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
