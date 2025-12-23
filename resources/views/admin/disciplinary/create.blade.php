<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Report Disciplinary Incident') }}
            </h2>
            <a href="{{ route('admin.disciplinary.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Disciplinary Records', 'url' => route('admin.disciplinary.index')],
                ['label' => 'New Record', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.disciplinary.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="student_id" class="block text-sm font-medium text-gray-700">Student</label>
                            <select name="student_id" id="student_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Student</option>
                                @foreach($students as $s)
                                    <option value="{{ $s->id }}" {{ (old('student_id', $student?->id) == $s->id) ? 'selected' : '' }}>
                                        {{ $s->full_name }} ({{ $s->student_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="incident_date" class="block text-sm font-medium text-gray-700">Incident Date</label>
                                <input type="date" name="incident_date" id="incident_date" value="{{ old('incident_date', date('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label for="incident_type" class="block text-sm font-medium text-gray-700">Incident Type</label>
                                <select name="incident_type" id="incident_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    @foreach(\App\Models\DisciplinaryRecord::incidentTypes() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="severity" class="block text-sm font-medium text-gray-700">Severity</label>
                            <select name="severity" id="severity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                @foreach(\App\Models\DisciplinaryRecord::severityLevels() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required placeholder="Describe the incident in detail...">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="action_taken" class="block text-sm font-medium text-gray-700">Action Taken (Optional)</label>
                            <textarea name="action_taken" id="action_taken" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Any immediate actions taken...">{{ old('action_taken') }}</textarea>
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_parent" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-600">Notify Parent/Guardian</span>
                            </label>
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('admin.disciplinary.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded transition">
                                Cancel
                            </a>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-10 rounded shadow-lg transition">
                                Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
