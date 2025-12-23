<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Disciplinary Records') }}
            </h2>
            <a href="{{ route('admin.disciplinary.create') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition">
                + New Record
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Disciplinary Records', 'url' => '#']
            ]" />
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex gap-4 flex-wrap">
                        <form action="{{ route('admin.disciplinary.index') }}" method="GET" class="flex gap-2 items-center">
                            <select name="severity" class="border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">All Severities</option>
                                @foreach(\App\Models\DisciplinaryRecord::severityLevels() as $key => $label)
                                    <option value="{{ $key }}" {{ request('severity') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">All Statuses</option>
                                <option value="reported" {{ request('status') == 'reported' ? 'selected' : '' }}>Reported</option>
                                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="escalated" {{ request('status') == 'escalated' ? 'selected' : '' }}>Escalated</option>
                            </select>
                            <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-1 px-3 rounded text-sm">Filter</button>
                        </form>
                    </div>

                    @if($records->isEmpty())
                        <p class="text-gray-500 italic">No disciplinary records found for {{ $academicYear->name }} hideously.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Severity</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported By</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($records as $record)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $record->incident_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="{{ route('admin.students.show', $record->student) }}" class="text-blue-600 hover:underline">
                                                {{ $record->student->full_name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ \App\Models\DisciplinaryRecord::incidentTypes()[$record->incident_type] ?? $record->incident_type }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @php
                                                $severityColors = [
                                                    'minor' => 'bg-gray-100 text-gray-800',
                                                    'moderate' => 'bg-yellow-100 text-yellow-800',
                                                    'major' => 'bg-orange-100 text-orange-800',
                                                    'critical' => 'bg-red-100 text-red-800',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $severityColors[$record->severity] ?? 'bg-gray-100' }}">
                                                {{ ucfirst($record->severity) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @php
                                                $statusColors = [
                                                    'reported' => 'bg-blue-100 text-blue-800',
                                                    'under_review' => 'bg-yellow-100 text-yellow-800',
                                                    'resolved' => 'bg-green-100 text-green-800',
                                                    'escalated' => 'bg-red-100 text-red-800',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$record->status] ?? 'bg-gray-100' }}">
                                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $record->reporter->name ?? 'System' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('admin.disciplinary.show', $record) }}" class="text-blue-600 hover:underline text-sm">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $records->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
