<x-admin-layout>
    <x-slot name="header">Disciplinary Record Details</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Disciplinary Records', 'url' => route('admin.disciplinary.index')],
            ['label' => 'Details', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6 text-gray-900">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Incident Information</h3>
                            <dl class="space-y-2">
                                <div class="flex">
                                    <dt class="w-32 text-gray-500">Student:</dt>
                                    <dd class="font-medium">
                                        <a href="{{ route('admin.students.show', $disciplinary->student) }}" class="text-blue-600 hover:underline">
                                            {{ $disciplinary->student->full_name }}
                                        </a>
                                    </dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500">Date:</dt>
                                    <dd class="font-medium">{{ $disciplinary->incident_date->format('F j, Y') }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500">Type:</dt>
                                    <dd class="font-medium">{{ \App\Models\DisciplinaryRecord::incidentTypes()[$disciplinary->incident_type] ?? $disciplinary->incident_type }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500">Severity:</dt>
                                    <dd>
                                        @php
                                            $severityColors = [
                                                'minor' => 'bg-gray-100 text-gray-800',
                                                'moderate' => 'bg-yellow-100 text-yellow-800',
                                                'major' => 'bg-orange-100 text-orange-800',
                                                'critical' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $severityColors[$disciplinary->severity] ?? 'bg-gray-100' }}">
                                            {{ ucfirst($disciplinary->severity) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500">Status:</dt>
                                    <dd>
                                        @php
                                            $statusColors = [
                                                'reported' => 'bg-blue-100 text-blue-800',
                                                'under_review' => 'bg-yellow-100 text-yellow-800',
                                                'resolved' => 'bg-green-100 text-green-800',
                                                'escalated' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$disciplinary->status] ?? 'bg-gray-100' }}">
                                            {{ ucfirst(str_replace('_', ' ', $disciplinary->status)) }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Processing Information</h3>
                            <dl class="space-y-2">
                                <div class="flex">
                                    <dt class="w-32 text-gray-500">Reported By:</dt>
                                    <dd class="font-medium">{{ $disciplinary->reporter->name ?? 'System' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500">Handled By:</dt>
                                    <dd class="font-medium">{{ $disciplinary->handler->name ?? 'Not Assigned' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500">Resolution:</dt>
                                    <dd class="font-medium">{{ $disciplinary->resolution_date?->format('M j, Y') ?? 'Pending' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500">Parent Notified:</dt>
                                    <dd class="font-medium">{{ $disciplinary->notify_parent ? 'Yes' : 'No' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="font-medium text-gray-700 mb-2">Description</h4>
                        <p class="p-3 bg-gray-50 rounded border">{{ $disciplinary->description }}</p>
                    </div>

                    @if($disciplinary->action_taken)
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-700 mb-2">Action Taken</h4>
                            <p class="p-3 bg-gray-50 rounded border">{{ $disciplinary->action_taken }}</p>
                        </div>
                    @endif

                    @if($disciplinary->resolution_notes)
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-700 mb-2">Resolution Notes</h4>
                            <p class="p-3 bg-green-50 rounded border border-green-200">{{ $disciplinary->resolution_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($disciplinary->status !== 'resolved')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Update Status</h3>
                        <form action="{{ route('admin.disciplinary.update', $disciplinary) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                        <option value="reported" {{ $disciplinary->status == 'reported' ? 'selected' : '' }}>Reported</option>
                                        <option value="under_review" {{ $disciplinary->status == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                        <option value="resolved" {{ $disciplinary->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="escalated" {{ $disciplinary->status == 'escalated' ? 'selected' : '' }}>Escalated</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="resolution_notes" class="block text-sm font-medium text-gray-700">Resolution Notes</label>
                                <textarea name="resolution_notes" id="resolution_notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Notes about the resolution...">{{ old('resolution_notes', $disciplinary->resolution_notes) }}</textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition">
                                    Update Record
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>
</x-admin-layout>
