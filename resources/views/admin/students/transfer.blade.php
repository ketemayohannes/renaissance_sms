<x-admin-layout>
    <x-slot name="header">Transfer Student: {{ $student->full_name }}</x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => $student->full_name, 'url' => route('admin.students.show', $student)],
            ['label' => 'Transfer', 'url' => '#']
        ]" />
        
        <div class="card overflow-hidden">
            <div class="p-6">
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(!$activeYear)
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong>Warning!</strong> No active academic year found.
                        </div>
                    @endif

                    <form action="{{ route('admin.students.transfer.store', $student) }}" method="POST">
                        @csrf

                        <!-- Current Enrollment Info -->
                        <div class="mb-8 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Current Enrollment</h3>
                            @if($currentEnrollment)
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Grade Level:</span>
                                        <span class="text-sm text-gray-900">{{ $currentEnrollment->section->gradeLevel->name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Section:</span>
                                        <span class="text-sm text-gray-900">{{ $currentEnrollment->section->name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Enrollment Date:</span>
                                        <span class="text-sm text-gray-900">{{ $currentEnrollment->enrollment_date }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Student Status:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $student->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $student->is_active ? 'Active' : 'Blocked' }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <p class="text-red-600">No active enrollment found for this student.</p>
                            @endif
                        </div>

                        <!-- Transfer Details -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Transfer Details</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="new_section_id" class="block text-sm font-medium text-gray-700">New Section *</label>
                                    <select name="new_section_id" id="new_section_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select Section</option>
                                        @foreach($sections->groupBy('gradeLevel.name') as $gradeName => $gradeSections)
                                            <optgroup label="{{ $gradeName }}">
                                                @foreach($gradeSections as $section)
                                                    <option value="{{ $section->id }}" {{ old('new_section_id') == $section->id ? 'selected' : '' }}>
                                                        {{ $section->name }} ({{ $section->gradeLevel->division->name }})
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error('new_section_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>

                                <div>
                                    <label for="transfer_date" class="block text-sm font-medium text-gray-700">Transfer Date *</label>
                                    <input type="date" name="transfer_date" id="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('transfer_date')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>

                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Transfer (Optional)</label>
                                    <textarea name="reason" id="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., Parent request, academic performance, behavioral issues, etc.">{{ old('reason') }}</textarea>
                                    @error('reason')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Warning Notice -->
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Important Notice</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>All academic records will remain with the student</li>
                                            <li>The current enrollment will be marked as "transferred"</li>
                                            <li>A new enrollment record will be created in the target section</li>
                                            <li>This action cannot be easily undone</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.students.show', $student) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Transfer Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
