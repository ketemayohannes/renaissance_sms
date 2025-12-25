<x-admin-layout>
    <x-slot name="header">Student Profile: {{ $student->full_name }}</x-slot>

    <div class="space-y-6">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Students', 'url' => route('admin.students.index')],
                ['label' => $student->full_name, 'url' => '#']
            ]" />
            
        <div class="card overflow-hidden">
            <div class="p-6" x-data="{ tab: 'overview' }">
                    
                    <!-- Header Section -->
                    <div class="flex flex-col md:flex-row gap-6 mb-6 border-b pb-6">
                        <!-- Photo & Basic Status -->
                        <div class="w-full md:w-1/4 flex flex-col items-center">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="Student Photo" class="h-32 w-32 rounded-full object-cover mb-4 shadow-md">
                            @else
                                <div class="h-32 w-32 bg-gray-200 rounded-full flex items-center justify-center text-4xl text-gray-500 font-bold mb-4 shadow-inner">
                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                                </div>
                            @endif
                            <h3 class="text-xl font-bold text-gray-900 text-center">{{ $student->full_name }}</h3>
                            <p class="text-gray-500 font-mono text-sm">{{ $student->student_id }}</p>
                            
                            <div class="mt-4 flex gap-2">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $student->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $student->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($student->trashed())
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-600 text-white">Deleted</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions & Quick Info -->
                        <div class="w-full md:w-3/4 flex flex-col justify-between">
                            <div class="flex justify-end gap-2 flex-wrap">
                                @if($student->trashed())
                                    <form action="{{ route('admin.students.restore', $student->id) }}" method="POST" class="inline confirm-form" data-confirm-message="Are you sure you want to restore this student?" data-confirm-type="success" data-confirm-title="Restore Student" data-confirm-button="Restore">
                                        @csrf
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm">
                                            Restore Student
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.students.edit', $student) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm">
                                        Edit
                                    </a>
                                    <a href="{{ route('admin.students.assign-electives', $student) }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded shadow-sm text-sm">
                                        Electives
                                    </a>
                                    <a href="{{ route('admin.students.transfer', $student) }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded shadow-sm text-sm">
                                        Transfer
                                    </a>
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded shadow-sm text-sm flex items-center">
                                            More <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-100" style="display: none;">
                                            <a href="{{ route('admin.students.status-history', $student) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Status History</a>
                                            <a href="{{ route('admin.students.id-card', $student) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Print ID Card</a>
                                            <a href="{{ route('admin.report-cards.pdf', $student) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Print Report Card</a>
                                            @if($student->is_active)
                                                <a href="{{ route('admin.students.withdraw', $student) }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Withdraw Student</a>
                                            @endif
                                            
                                            <form action="{{ route('admin.students.toggle-block', $student) }}" method="POST" class="border-t border-gray-100 confirm-form" 
                                                  data-confirm-message="Are you sure you want to {{ $student->is_active ? 'block' : 'unblock' }} access for this student?"
                                                  data-confirm-title="Confirm Change"
                                                  data-confirm-type="{{ $student->is_active ? 'danger' : 'success' }}">
                                                @csrf
                                                <button type="submit" class="w-full text-left px-4 py-2 text-sm {{ $student->is_active ? 'text-red-600' : 'text-green-600' }} hover:bg-gray-100">
                                                    {{ $student->is_active ? 'Block Access' : 'Unblock Access' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                                <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                    <span class="text-xs text-gray-500 uppercase block">Grade</span>
                                    <span class="font-bold text-gray-900">{{ $student->currentEnrollment ? $student->currentEnrollment->section->gradeLevel->name : 'N/A' }}</span>
                                </div>
                                <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                    <span class="text-xs text-gray-500 uppercase block">Section</span>
                                    <span class="font-bold text-gray-900">{{ $student->currentEnrollment ? $student->currentEnrollment->section->name : 'N/A' }}</span>
                                </div>
                                <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                    <span class="text-xs text-gray-500 uppercase block">Admission</span>
                                    <span class="font-bold text-gray-900">{{ $student->admission_date->format('M Y') }}</span>
                                </div>
                                <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                    <span class="text-xs text-gray-500 uppercase block">Gender</span>
                                    <span class="font-bold text-gray-900">{{ $student->gender == 'M' ? 'Male' : 'Female' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="tab = 'overview'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'overview', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'overview' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Overview
                            </button>
                            <button @click="tab = 'guardians'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'guardians', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'guardians' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Guardians
                            </button>
                            <button @click="tab = 'medical_transport'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'medical_transport', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'medical_transport' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Medical & Transport
                            </button>
                            <button @click="tab = 'enrollment'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'enrollment', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'enrollment' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Enrollment & Siblings
                            </button>
                            <button @click="tab = 'academic'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'academic', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'academic' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Academic
                            </button>
                            <button @click="tab = 'attendance'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'attendance', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'attendance' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Attendance
                            </button>
                            <button @click="tab = 'disciplinary'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'disciplinary', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'disciplinary' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Disciplinary
                            </button>
                            <button @click="tab = 'documents'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'documents', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'documents' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                                Documents
                                <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-gray-100 text-gray-600">{{ $student->documents->count() }}</span>
                            </button>
                        </nav>
                    </div>

                    <!-- OVERVIEW TAB -->
                    <div x-show="tab === 'overview'" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Personal Info -->
                            <div>
                                <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Personal Details
                                </h4>
                                <dl class="bg-gray-50 rounded-lg p-4 grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                                    <div class="col-span-2">
                                        <dt class="text-gray-500 font-medium">Full Name</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->full_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 font-medium">Date of Birth</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->date_of_birth->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 font-medium">Age</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->date_of_birth->age }} years</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 font-medium">Place of Birth</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->birth_city }}, {{ $student->birth_country }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 font-medium">Nationality</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->nationality }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 font-medium">Language</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->language_spoken }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Address & Contact -->
                            <div>
                                <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                    Address & Contact
                                </h4>
                                <dl class="bg-gray-50 rounded-lg p-4 grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm mb-6">
                                    <div>
                                        <dt class="text-gray-500 font-medium">Subcity</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->subcity ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 font-medium">Woreda</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->woreda ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 font-medium">House Number</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->house_number ?? '-' }}</dd>
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-gray-500 font-medium">Full Address</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->full_address }}</dd>
                                    </div>
                                    <div class="col-span-2 border-t pt-4">
                                        <dt class="text-gray-500 font-medium">Email</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->email }}</dd>
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-gray-500 font-medium">Phone</dt>
                                        <dd class="text-gray-900 mt-1">{{ $student->phone ?? 'N/A' }}</dd>
                                    </div>
                                </dl>

                                <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 19l-1 1-1 1-2-2-1-1-1-1l3.743-3.743A6 6 0 0120 8zm-4-4a5.978 5.978 0 01-1.383-1.618c-.428-.857-1.748-.857-2.176 0A5.986 5.986 0 0110 4m0 0a5 5 0 010 10m0-10a5 5 0 015 5m0 0a5 5 0 010 5m0-5h2"></path></svg>
                                    Portal Account
                                </h4>
                                <div class="bg-gray-50 rounded-lg p-4 text-sm">
                                    @if($student->user_id)
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                                <p class="text-gray-600 mt-1">{{ $student->user->email }}</p>
                                            </div>
                                            <form action="{{ route('admin.students.reset-password', $student) }}" method="POST" class="confirm-form" data-confirm-message="Reset password for this student?" data-confirm-title="Reset Password" data-confirm-type="warning" data-confirm-button="Reset">
                                                @csrf
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 text-xs font-bold underline">Reset Password</button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500">No portal account linked.</span>
                                            <form action="{{ route('admin.students.create-user', $student) }}" method="POST" class="confirm-form" data-confirm-message="Create a portal account for this student?" data-confirm-title="Create Account" data-confirm-type="info" data-confirm-button="Create">
                                                @csrf
                                                <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs">Create Account</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GUARDIANS TAB -->
                    <div x-show="tab === 'guardians'" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($student->guardians as $guardian)
                                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 relative">
                                    <div class="flex items-start">
                                        @if($guardian->photo)
                                            <img src="{{ asset('storage/' . $guardian->photo) }}" alt="Guardian Photo" class="h-16 w-16 rounded-full object-cover mr-4 ring-2 ring-white">
                                        @else
                                            <div class="h-16 w-16 bg-gray-300 rounded-full flex items-center justify-center text-xl text-gray-600 font-bold mr-4 ring-2 ring-white">
                                                {{ substr($guardian->first_name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="flex-grow">
                                            <h5 class="text-lg font-bold text-gray-900">{{ $guardian->full_name }}</h5>
                                            <p class="text-indigo-600 font-medium text-sm mb-2">{{ $guardian->relationship }} <span class="text-gray-400 mx-1">•</span> {{ ucfirst($guardian->guardian_type) }}</p>
                                            
                                            <div class="space-y-1 text-sm text-gray-600">
                                                <p class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> {{ $guardian->phone }}</p>
                                                @if($guardian->email)
                                                    <p class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> {{ $guardian->email }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($guardian->is_emergency_contact)
                                        <div class="absolute top-4 right-4">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                Emergency Contact
                                            </span>
                                        </div>
                                    @endif

                                    <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center bg-white -mx-5 -mb-5 p-3 rounded-b-lg">
                                        @if(!$guardian->user_id)
                                            <form action="{{ route('admin.guardians.create-user', $guardian) }}" method="POST" class="confirm-form" data-confirm-message="Create a portal account for this guardian?" data-confirm-title="Create Account" data-confirm-type="info" data-confirm-button="Create">
                                                @csrf
                                                <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold uppercase tracking-wide">
                                                    Create Portal Access
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-green-600 font-bold uppercase tracking-wide flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Portal Active
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- MEDICAL & TRANSPORT TAB -->
                    <div x-show="tab === 'medical_transport'" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Medical -->
                            <div>
                                <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                    Medical Information
                                </h4>
                                @if($student->medicalInfo)
                                    <dl class="bg-red-50 rounded-lg p-4 space-y-4 text-sm border border-red-100">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-gray-500 font-medium">Blood Group</dt>
                                                <dd class="text-gray-900 font-bold mt-1 text-lg">{{ $student->medicalInfo->blood_group ?? 'N/A' }}</dd>
                                            </div>
                                        </div>
                                        <div>
                                            <dt class="text-gray-500 font-medium">Emergency Contact</dt>
                                            <dd class="text-gray-900 mt-1">{{ $student->medicalInfo->emergency_contact ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-gray-500 font-medium">Medical Issues</dt>
                                            <dd class="text-gray-900 mt-1">{{ $student->medicalInfo->medical_issues ?? 'None reported' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-gray-500 font-medium">Allergies</dt>
                                            <dd class="text-gray-900 mt-1">{{ $student->medicalInfo->allergies ?? 'None reported' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-gray-500 font-medium">Current Medication</dt>
                                            <dd class="text-gray-900 mt-1">{{ $student->medicalInfo->current_medication ?? 'None' }}</dd>
                                        </div>
                                    </dl>
                                @else
                                    <div class="bg-gray-50 p-4 rounded text-center text-gray-500 text-sm">No medical information available.</div>
                                @endif
                            </div>

                            <!-- Transport -->
                            <div>
                                <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    Transportation
                                </h4>
                                @if($student->transportation && $student->transportation->driver_first_name)
                                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-100">
                                        <div class="flex items-center mb-4">
                                            @if($student->transportation->driver_photo)
                                                <img src="{{ asset('storage/' . $student->transportation->driver_photo) }}" alt="Driver" class="h-12 w-12 rounded-full object-cover mr-3">
                                            @else
                                                <div class="h-12 w-12 bg-yellow-200 rounded-full flex items-center justify-center text-yellow-700 font-bold mr-3">D</div>
                                            @endif
                                            <div>
                                                <h5 class="font-bold text-gray-900">{{ $student->transportation->driver_full_name }}</h5>
                                                <span class="text-xs text-yellow-700 font-bold uppercase tracking-wide">Assigned Driver</span>
                                            </div>
                                        </div>
                                        <dl class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <dt class="text-gray-500 font-medium">Route</dt>
                                                <dd class="text-gray-900 font-bold mt-1">{{ $student->transportation->route }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500 font-medium">Vehicle Plate</dt>
                                                <dd class="text-gray-900 mt-1">{{ $student->transportation->vehicle_plate }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500 font-medium">Driver Phone</dt>
                                                <dd class="text-gray-900 mt-1">{{ $student->transportation->driver_phone ?? '-' }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                @else
                                    <div class="bg-gray-50 p-4 rounded text-center text-gray-500 text-sm">No transportation assigned.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ENROLLMENT & SIBLINGS TAB -->
                    <div x-show="tab === 'enrollment'" style="display: none;">
                        <div class="flex flex-col gap-8">
                            <!-- Enrollment History -->
                            <div>
                                <h4 class="font-bold text-gray-800 mb-4">Enrollment History</h4>
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade & Section</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($enrollments as $enrollment)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $enrollment->academicYear->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $enrollment->section->gradeLevel->name }} - {{ $enrollment->section->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $enrollment->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                            {{ ucfirst($enrollment->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $enrollment->enrollment_date->format('M d, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Siblings -->
                            <div>
                                <h4 class="font-bold text-gray-800 mb-4 flex justify-between items-center">
                                    Siblings
                                    <span class="text-xs bg-gray-100 text-gray-600 py-1 px-3 rounded-full">{{ $student->siblings->count() }} Linked</span>
                                </h4>
                                
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <form action="{{ route('admin.students.siblings.link', $student) }}" method="POST" class="flex gap-2 items-center">
                                        @csrf
                                        <input type="text" name="sibling_id" placeholder="Enter Sibling Student ID" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm flex-grow">
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Link Sibling</button>
                                    </form>
                                </div>

                                @if($student->siblings->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($student->siblings as $sibling)
                                            <div class="flex items-center justify-between bg-white border border-gray-200 p-3 rounded-lg shadow-sm">
                                                <div class="flex items-center">
                                                    @if($sibling->photo)
                                                        <img src="{{ asset('storage/' . $sibling->photo) }}" class="h-10 w-10 rounded-full object-cover mr-3">
                                                    @else
                                                        <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center text-xs font-bold mr-3">{{ substr($sibling->first_name, 0, 1) }}</div>
                                                    @endif
                                                    <div>
                                                        <a href="{{ route('admin.students.show', $sibling) }}" class="font-medium text-gray-900 hover:underline">{{ $sibling->full_name }}</a>
                                                        <p class="text-xs text-gray-500">{{ $sibling->student_id }}</p>
                                                    </div>
                                                </div>
                                                <form action="{{ route('admin.students.siblings.unlink', ['student' => $student, 'sibling' => $sibling]) }}" method="POST" class="confirm-form" data-confirm-message="Are you sure you want to unlink these siblings?" data-confirm-title="Unlink Siblings" data-confirm-type="danger" data-confirm-button="Unlink">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Unlink</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ACADEMIC TAB -->
                    <div x-show="tab === 'academic'" style="display: none;">
                        <div class="flex flex-col gap-6">
                            <h4 class="font-bold text-gray-800 flex items-center mb-2">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                Academic History
                            </h4>
                            
                            @forelse($academicRecords->sortKeysDesc() as $year => $terms)
                                <div class="border border-gray-200 rounded-lg overflow-hidden mb-4" x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }">
                                    <div class="bg-gray-100 px-4 py-3 flex justify-between items-center cursor-pointer hover:bg-gray-200 transition" @click="expanded = !expanded">
                                        <h5 class="font-bold text-gray-800 text-lg">{{ $year }} Academic Year</h5>
                                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                    
                                    <div x-show="expanded" class="p-4 bg-white" style="display: none;">
                                        @foreach($terms as $termName => $marks)
                                            @php
                                                $termRecord = $termRecords[$year][$termName] ?? null;
                                            @endphp
                                            
                                            <div class="mb-4 last:mb-0 border border-gray-100 rounded-lg overflow-hidden" x-data="{ termOpen: {{ $loop->first ? 'true' : 'false' }} }">
                                                <div class="flex items-center justify-between px-3 py-2 bg-gray-50 cursor-pointer hover:bg-gray-100 transition" @click="termOpen = !termOpen">
                                                    <h6 class="font-bold text-indigo-700 text-md flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-indigo-400 transform transition-transform" :class="{ 'rotate-90': termOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                        {{ $termName }}
                                                    </h6>
                                                    <div class="flex items-center gap-3">
                                                        @if($termRecord)
                                                            <div class="text-xs flex gap-3">
                                                                @if($termRecord->average_score)
                                                                    <span class="font-bold text-gray-700">Avg: {{ number_format($termRecord->average_score, 2) }}</span>
                                                                @endif
                                                                @if($termRecord->rank)
                                                                    <span class="font-bold text-gray-700">Rank: {{ $termRecord->rank }} / {{ $termRecord->rank_out_of }}</span>
                                                                @endif
                                                                @if($termRecord->conduct_grade)
                                                                    <span class="font-bold text-gray-700">Conduct: {{ $termRecord->conduct_grade }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        @if($termRecord && $termRecord->term_id)
                                                            <a href="{{ route('admin.report-cards.pdf', ['student' => $student->id, 'term_id' => $termRecord->term_id, 'academic_year_id' => $termRecord->academic_year_id]) }}" 
                                                               target="_blank" 
                                                               class="text-indigo-600 hover:text-indigo-800 text-xs font-bold flex items-center"
                                                               @click.stop>
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                Report
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div x-show="termOpen" class="overflow-x-auto" style="display: none;">
                                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Subject</th>
                                                                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Assessment</th>
                                                                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Score</th>
                                                                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            @foreach($marks as $mark)
                                                                <tr class="hover:bg-gray-50">
                                                                    <td class="px-4 py-2 text-gray-900 font-medium">{{ $mark->subject->name }}</td>
                                                                    <td class="px-4 py-2 text-gray-500">{{ $mark->assessmentTemplate->name }}</td>
                                                                    <td class="px-4 py-2 font-bold {{ $mark->score < 50 ? 'text-red-600' : 'text-gray-900' }}">{{ $mark->score }}</td>
                                                                    <td class="px-4 py-2 text-gray-500">{{ $mark->created_at->format('M d') }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="bg-gray-50 p-8 rounded-lg text-center border-2 border-dashed border-gray-300">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z"></path></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No grades found</h3>
                                    <p class="mt-1 text-sm text-gray-500">No marks recorded for this student yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- ATTENDANCE TAB -->
                    <div x-show="tab === 'attendance'" style="display: none;">
                        <h4 class="font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Attendance Overview (Current Year)
                        </h4>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                            <div class="bg-green-50 p-4 rounded-lg border border-green-100 shadow-sm">
                                <span class="text-xs text-green-600 uppercase font-bold tracking-wider">Present</span>
                                <span class="block text-3xl font-extrabold text-green-700 mt-2">{{ $attendanceStats['present'] }}</span>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg border border-red-100 shadow-sm">
                                 <span class="text-xs text-red-600 uppercase font-bold tracking-wider">Absent</span>
                                 <span class="block text-3xl font-extrabold text-red-700 mt-2">{{ $attendanceStats['absent'] }}</span>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100 shadow-sm">
                                 <span class="text-xs text-yellow-600 uppercase font-bold tracking-wider">Late</span>
                                 <span class="block text-3xl font-extrabold text-yellow-700 mt-2">{{ $attendanceStats['late'] }}</span>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 shadow-sm">
                                 <span class="text-xs text-blue-600 uppercase font-bold tracking-wider">Attendance Rate</span>
                                 <span class="block text-3xl font-extrabold text-blue-700 mt-2">{{ $attendanceStats['percentage'] }}%</span>
                            </div>
                        </div>
                        
                        <!-- Recent Attendance Log -->
                        <div class="mt-6">
                            <h5 class="font-bold text-gray-700 mb-4">Recent Attendance Log</h5>
                            @if($recentAttendance->count() > 0)
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Date</th>
                                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Section</th>
                                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($recentAttendance as $entry)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-gray-900">{{ $entry->attendance_date->format('M d, Y') }}</td>
                                                    <td class="px-4 py-3">
                                                        <span class="px-2 py-1 rounded text-xs font-bold uppercase
                                                            {{ $entry->status == 'present' ? 'bg-green-100 text-green-800' : '' }}
                                                            {{ $entry->status == 'absent' ? 'bg-red-100 text-red-800' : '' }}
                                                            {{ $entry->status == 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                            {{ $entry->status == 'excused' ? 'bg-blue-100 text-blue-800' : '' }}
                                                        ">{{ ucfirst($entry->status) }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-500">{{ $entry->section->gradeLevel->name ?? '' }} - {{ $entry->section->name ?? '' }}</td>
                                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $entry->remarks ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="bg-gray-50 p-6 rounded-lg text-center border border-gray-200">
                                    <p class="text-gray-500 text-sm">No attendance records found.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- DISCIPLINARY TAB -->
                    <div x-show="tab === 'disciplinary'" style="display: none;">
                        <h4 class="font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Disciplinary History
                        </h4>

                        @if($disciplinaryRecords->count() > 0)
                            <div class="space-y-4">
                                @foreach($disciplinaryRecords as $record)
                                    <div class="bg-white border-l-4 {{ $record->severity == 'critical' || $record->severity == 'major' ? 'border-red-500' : 'border-yellow-500' }} rounded-r-lg p-4 shadow-sm relative">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                 <div class="flex items-center gap-2 mb-1">
                                                     <span class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $record->severity == 'critical' ? 'bg-red-100 text-red-800' : ($record->severity == 'major' ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                         {{ ucfirst($record->severity) }}
                                                     </span>
                                                     <span class="text-xs text-gray-500">{{ $record->incident_date->format('M d, Y') }}</span>
                                                 </div>
                                                 <h5 class="text-lg font-bold text-gray-900">{{ ucfirst($record->incident_type) }}</h5>
                                                 <p class="text-gray-700 mt-2">{{ $record->description }}</p>
                                                 
                                                 @if($record->action_taken)
                                                     <div class="mt-3 text-sm bg-gray-50 p-2 rounded">
                                                         <span class="font-bold text-gray-600">Action Taken:</span> {{ $record->action_taken }}
                                                     </div>
                                                 @endif
                                            </div>
                                            
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                             <div class="bg-green-50 p-8 rounded-lg text-center border border-green-200">
                                <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <h3 class="mt-2 text-sm font-medium text-green-800">Clean Record</h3>
                                <p class="mt-1 text-sm text-green-600">No disciplinary incidents recorded.</p>
                            </div>
                        @endif
                    </div>

                    <!-- DOCUMENTS TAB -->
                    <div x-show="tab === 'documents'" style="display: none;">
                        <div class="flex flex-col md:flex-row gap-8">
                            <!-- Upload Form -->
                            <div class="w-full md:w-1/3 order-2 md:order-1">
                                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                    <h4 class="font-bold text-gray-800 mb-4">Upload Document</h4>
                                    <form action="{{ route('admin.students.store-document', $student) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Document Title</label>
                                                <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                                <select name="document_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                                                    <option value="standard">Standard</option>
                                                    <option value="medical">Medical</option>
                                                    <option value="legal">Legal</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">File</label>
                                                <input type="file" name="file" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                                                <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm"></textarea>
                                            </div>
                                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm">Upload Document</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Documents List -->
                            <div class="w-full md:w-2/3 order-1 md:order-2">
                                <h4 class="font-bold text-gray-800 mb-4">Uploaded Documents</h4>
                                @if($student->documents->count() > 0)
                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach($student->documents as $doc)
                                            <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center justify-between shadow-sm hover:shadow transition-shadow">
                                                <div class="flex items-center">
                                                    <div class="h-10 w-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 mr-4">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z"></path></svg>
                                                    </div>
                                                    <div>
                                                        <h5 class="font-bold text-gray-900">{{ $doc->title }}</h5>
                                                        <p class="text-xs text-gray-500">
                                                            {{ ucfirst($doc->document_type) }} • Uploaded {{ $doc->created_at->format('M d, Y') }}
                                                        </p>
                                                        @if($doc->notes)
                                                            <p class="text-xs text-gray-600 mt-1 italic">{{ $doc->notes }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-gray-500 hover:text-indigo-600">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    </a>
                                                    <form action="{{ route('admin.students.delete-document', ['student' => $student, 'document' => $doc]) }}" method="POST" class="delete-form" data-confirm-message="Are you sure you want to delete this document?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-500 hover:text-red-600">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z"></path></svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
                                        <p class="mt-1 text-sm text-gray-500">Upload standard, medical, or legal documents here.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
    </div>
</x-admin-layout>
