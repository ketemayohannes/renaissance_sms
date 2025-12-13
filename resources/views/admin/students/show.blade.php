<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Profile') }}: {{ $student->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Students', 'url' => route('admin.students.index')],
                ['label' => $student->full_name, 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Sidebar / Photo -->
                        <div class="w-full md:w-1/4 flex flex-col items-center">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="Student Photo" class="h-32 w-32 rounded-full object-cover mb-4">
                            @else
                                <div class="h-32 w-32 bg-gray-200 rounded-full flex items-center justify-center text-4xl text-gray-500 font-bold mb-4">
                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                                </div>
                            @endif
                            <h3 class="text-xl font-bold text-gray-900 text-center">{{ $student->full_name }}</h3>
                            <p class="text-gray-500">{{ $student->student_id }}</p>
                            <div class="mt-4 w-full">
                                <div class="bg-gray-50 p-3 rounded text-center">
                                    <span class="block text-xs text-gray-500 uppercase">Status</span>
                                    <span class="font-bold {{ $student->is_active ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $student->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Main Info -->
                        <div class="w-full md:w-3/4">
                            <!-- Personal Details -->
                            <div class="mb-6">
                                <h4 class="font-bold text-gray-700 border-b pb-2 mb-3">Personal Details</h4>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->full_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->gender == 'M' ? 'Male' : 'Female' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->date_of_birth->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Place of Birth</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->birth_city ?? 'N/A' }}, {{ $student->birth_country ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Nationality</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->nationality ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Language Spoken</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->language_spoken ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Admission Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->admission_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Admission Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->admission_date->format('M d, Y') }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Address -->
                            <div class="mb-6">
                                <h4 class="font-bold text-gray-700 border-b pb-2 mb-3">Address</h4>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Subcity</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->subcity ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Woreda</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->woreda ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">House Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->house_number ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Full Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->full_address ?: 'N/A' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Contact Info -->
                            <div class="mb-6">
                                <h4 class="font-bold text-gray-700 border-b pb-2 mb-3">Contact Information</h4>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->phone ?? 'N/A' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Guardians -->
                            <div class="mb-6">
                                <h4 class="font-bold text-gray-700 border-b pb-2 mb-3">Guardian Information</h4>
                                @foreach($student->guardians as $guardian)
                                    <div class="mb-4 p-4 bg-gray-50 rounded">
                                        <div class="flex items-center mb-2">
                                            @if($guardian->photo)
                                                <img src="{{ asset('storage/' . $guardian->photo) }}" alt="Guardian Photo" class="h-16 w-16 rounded-full object-cover mr-3">
                                            @else
                                                <div class="h-16 w-16 bg-gray-300 rounded-full flex items-center justify-center text-xl text-gray-600 font-bold mr-3">
                                                    {{ substr($guardian->first_name, 0, 1) }}{{ substr($guardian->father_name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <h5 class="font-semibold text-gray-900">{{ $guardian->full_name }}</h5>
                                                <p class="text-sm text-gray-600">{{ ucfirst($guardian->guardian_type) }} Guardian</p>
                                            </div>
                                        </div>
                                        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 mt-3">
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500">Relationship</dt>
                                                <dd class="text-sm text-gray-900">{{ $guardian->relationship ?? 'N/A' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500">Phone</dt>
                                                <dd class="text-sm text-gray-900">{{ $guardian->phone }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500">Email</dt>
                                                <dd class="text-sm text-gray-900">{{ $guardian->email ?? 'N/A' }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Medical Info -->
                            @if($student->medicalInfo)
                            <div class="mb-6">
                                <h4 class="font-bold text-gray-700 border-b pb-2 mb-3">Medical Information</h4>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Blood Group</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->medicalInfo->blood_group ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Emergency Contact</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->medicalInfo->emergency_contact ?? 'N/A' }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Medical Issues</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->medicalInfo->medical_issues ?? 'None reported' }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Current Medication</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->medicalInfo->current_medication ?? 'None' }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Allergies</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $student->medicalInfo->allergies ?? 'None reported' }}</dd>
                                    </div>
                                </dl>
                            </div>
                            @endif

                            <!-- Transportation -->
                            @if($student->transportation && $student->transportation->driver_first_name)
                            <div class="mb-6">
                                <h4 class="font-bold text-gray-700 border-b pb-2 mb-3">Transportation Information</h4>
                                <div class="p-4 bg-gray-50 rounded">
                                    <div class="flex items-center mb-2">
                                        @if($student->transportation->driver_photo)
                                            <img src="{{ asset('storage/' . $student->transportation->driver_photo) }}" alt="Driver Photo" class="h-16 w-16 rounded-full object-cover mr-3">
                                        @else
                                            <div class="h-16 w-16 bg-gray-300 rounded-full flex items-center justify-center text-xl text-gray-600 font-bold mr-3">
                                                {{ substr($student->transportation->driver_first_name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h5 class="font-semibold text-gray-900">{{ $student->transportation->driver_full_name }}</h5>
                                            <p class="text-sm text-gray-600">Driver</p>
                                        </div>
                                    </div>
                                    <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 mt-3">
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500">Driver ID</dt>
                                            <dd class="text-sm text-gray-900">{{ $student->transportation->driver_id ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500">License Number</dt>
                                            <dd class="text-sm text-gray-900">{{ $student->transportation->license_number ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500">Vehicle Plate</dt>
                                            <dd class="text-sm text-gray-900">{{ $student->transportation->vehicle_plate ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500">Route</dt>
                                            <dd class="text-sm text-gray-900">{{ $student->transportation->route ?? 'N/A' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                            @endif

                            <!-- Enrollment History -->
                            <div class="mt-8">
                                <h4 class="font-bold text-gray-700 border-b pb-2 mb-4 flex items-center justify-between">
                                    <span>Enrollment History</span>
                                    <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $enrollments->count() }} Records</span>
                                </h4>
                                
                                <div class="relative border-l-2 border-gray-200 ml-3 space-y-8">
                                    @foreach($enrollments as $enrollment)
                                        <div class="relative pl-8">
                                            <!-- Timeline Dot -->
                                            <span class="absolute top-0 left-[-9px] bg-white h-4 w-4 rounded-full border-2 {{ $enrollment->status == 'active' ? 'border-green-500' : ($enrollment->status == 'transferred' ? 'border-blue-500' : 'border-gray-400') }}"></span>
                                            
                                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                                                    <div>
                                                        <h5 class="text-lg font-bold text-gray-900">
                                                            {{ $enrollment->section->gradeLevel->name }} - {{ $enrollment->section->name }}
                                                        </h5>
                                                        <p class="text-sm text-gray-600 font-medium">{{ $enrollment->academicYear->name }}</p>
                                                    </div>
                                                    <span class="mt-2 sm:mt-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $enrollment->status == 'active' ? 'bg-green-100 text-green-800' : ($enrollment->status == 'transferred' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }} capitalize">
                                                        {{ $enrollment->status }}
                                                    </span>
                                                </div>
                                                
                                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-gray-500 block text-xs uppercase tracking-wide">Enrollment Date</span>
                                                        <span class="font-medium text-gray-900">
                                                            {{ \Carbon\Carbon::parse($enrollment->enrollment_date)->format('M d, Y') }}
                                                        </span>
                                                    </div>
                                                    
                                                    @if($enrollment->end_date)
                                                    <div>
                                                        <span class="text-gray-500 block text-xs uppercase tracking-wide">End Date</span>
                                                        <span class="font-medium text-gray-900">
                                                            {{ \Carbon\Carbon::parse($enrollment->end_date)->format('M d, Y') }}
                                                        </span>
                                                    </div>
                                                    @endif
                                                    
                                                    <div class="sm:col-span-2">
                                                        <span class="text-gray-500 block text-xs uppercase tracking-wide">Duration</span>
                                                        <span class="font-medium text-gray-900">
                                                            @if($enrollment->end_date)
                                                                {{ \Carbon\Carbon::parse($enrollment->enrollment_date)->diffForHumans(\Carbon\Carbon::parse($enrollment->end_date), ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }}
                                                            @else
                                                                {{ \Carbon\Carbon::parse($enrollment->enrollment_date)->diffForHumans(now(), ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }} (Current)
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Siblings -->
                            <div class="mt-8">
                                <h4 class="font-bold text-gray-700 border-b pb-2 mb-4 flex items-center justify-between">
                                    <span>Siblings</span>
                                    <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $student->siblings->count() }} Linked</span>
                                </h4>

                                <!-- Add Sibling Form -->
                                <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                                    <h5 class="text-sm font-bold text-gray-700 mb-2">Link a Sibling</h5>
                                    <form action="{{ route('admin.students.siblings.link', $student) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        <div class="flex-grow">
                                            <input type="text" name="sibling_id" placeholder="Enter Sibling Student ID (e.g., STU001)" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                            @error('sibling_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Link</button>
                                    </form>
                                    <p class="text-xs text-gray-500 mt-1">Enter the Student ID of the sibling you want to link.</p>
                                </div>

                                <!-- Siblings List -->
                                @if($student->siblings->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($student->siblings as $sibling)
                                            <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center justify-between shadow-sm">
                                                <div class="flex items-center">
                                                    @if($sibling->photo)
                                                        <img src="{{ asset('storage/' . $sibling->photo) }}" alt="Sibling Photo" class="h-10 w-10 rounded-full object-cover mr-3">
                                                    @else
                                                        <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center text-xs text-gray-500 font-bold mr-3">
                                                            {{ substr($sibling->first_name, 0, 1) }}{{ substr($sibling->father_name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <a href="{{ route('admin.students.show', $sibling) }}" class="text-sm font-bold text-gray-900 hover:text-indigo-600">
                                                            {{ $sibling->full_name }}
                                                        </a>
                                                        <p class="text-xs text-gray-500">
                                                            {{ $sibling->currentEnrollment() ? $sibling->currentEnrollment()->section->gradeLevel->name : 'No Grade' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <form action="{{ route('admin.students.siblings.unlink', ['student' => $student, 'sibling' => $sibling]) }}" method="POST" onsubmit="return confirm('Are you sure you want to unlink this sibling?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Unlink</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-gray-500 text-sm italic bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                        No siblings linked yet.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <a href="{{ route('admin.students.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</a>
                        
                        <!-- Block/Unblock Button -->
                        <form action="{{ route('admin.students.toggle-block', $student) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="{{ $student->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to {{ $student->is_active ? 'block' : 'unblock' }} this student?')">
                                {{ $student->is_active ? 'Block Student' : 'Unblock Student' }}
                            </button>
                        </form>
                        
                        <!-- Transfer Button -->
                        <a href="{{ route('admin.students.transfer', $student) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Transfer Section
                        </a>
                        
                        <a href="{{ route('admin.students.assign-electives', $student) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Assign Electives
                        </a>

                        <a href="{{ route('admin.students.edit', $student) }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Edit Student</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
