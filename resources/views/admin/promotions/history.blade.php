<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Promotion History') }}
            </h2>
            <a href="{{ route('admin.promotions.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Promotions', 'url' => route('admin.promotions.index')],
                ['label' => 'History', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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
</x-app-layout>
