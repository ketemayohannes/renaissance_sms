<x-admin-layout>
    <x-slot name="header">Edit Student: {{ $student->full_name }}</x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => $student->full_name, 'url' => route('admin.students.show', $student)],
            ['label' => 'Edit', 'url' => '#']
        ]" />
        
        <div class="card overflow-hidden">
            <div class="p-6">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong>Please fix the following errors:</strong>
                            <ul class="list-disc list-inside mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.students.update', $student) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Personal Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-3">
                                    <label for="photo" class="block text-sm font-medium text-gray-700">Student Photo</label>
                                    @if($student->photo)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $student->photo) }}" alt="Current Photo" class="h-20 w-20 object-cover rounded">
                                        </div>
                                    @endif
                                    <input type="file" name="photo" id="photo" accept="image/*" class="mt-1 block w-full">
                                    @error('photo')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $student->first_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('first_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="father_name" class="block text-sm font-medium text-gray-700">Father Name *</label>
                                    <input type="text" name="father_name" id="father_name" value="{{ old('father_name', $student->father_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('father_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="grandfather_name" class="block text-sm font-medium text-gray-700">Grandfather Name *</label>
                                    <input type="text" name="grandfather_name" id="grandfather_name" value="{{ old('grandfather_name', $student->grandfather_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('grandfather_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender *</label>
                                    <select name="gender" id="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="M" {{ old('gender', $student->gender) == 'M' ? 'selected' : '' }}>Male</option>
                                        <option value="F" {{ old('gender', $student->gender) == 'F' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('date_of_birth')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="birth_country" class="block text-sm font-medium text-gray-700">Birth Country</label>
                                    <input type="text" name="birth_country" id="birth_country" value="{{ old('birth_country', $student->birth_country) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('birth_country')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="birth_city" class="block text-sm font-medium text-gray-700">Birth City</label>
                                    <input type="text" name="birth_city" id="birth_city" value="{{ old('birth_city', $student->birth_city) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('birth_city')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="nationality" class="block text-sm font-medium text-gray-700">Nationality</label>
                                    <input type="text" name="nationality" id="nationality" value="{{ old('nationality', $student->nationality) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nationality')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="language_spoken" class="block text-sm font-medium text-gray-700">Language Spoken</label>
                                    <input type="text" name="language_spoken" id="language_spoken" value="{{ old('language_spoken', $student->language_spoken) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('language_spoken')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Admission Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Admission Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="student_id" class="block text-sm font-medium text-gray-700">Student ID</label>
                                    <input type="text" name="student_id" id="student_id" value="{{ $student->student_id }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm" readonly>
                                </div>
                                <div>
                                    <label for="admission_number" class="block text-sm font-medium text-gray-700">Admission Number *</label>
                                    <input type="text" name="admission_number" id="admission_number" value="{{ old('admission_number', $student->admission_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('admission_number')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="admission_date" class="block text-sm font-medium text-gray-700">Admission Date *</label>
                                    <input type="date" name="admission_date" id="admission_date" value="{{ old('admission_date', $student->admission_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('admission_date')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Address Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="subcity" class="block text-sm font-medium text-gray-700">Subcity</label>
                                    <select name="subcity" id="subcity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Subcity</option>
                                        @foreach(['Addis Ketema', 'Akaki Kality', 'Arada', 'Bole', 'Gullele', 'Kirkos', 'Kolfe Keranio', 'Lideta', 'Nifas Silk-Lafto', 'Yeka', 'Lemi Kura'] as $subcity)
                                            <option value="{{ $subcity }}" {{ old('subcity', $student->subcity) == $subcity ? 'selected' : '' }}>{{ $subcity }}</option>
                                        @endforeach
                                    </select>
                                    @error('subcity')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="woreda" class="block text-sm font-medium text-gray-700">Woreda</label>
                                    <input type="text" name="woreda" id="woreda" value="{{ old('woreda', $student->woreda) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('woreda')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="house_number" class="block text-sm font-medium text-gray-700">House Number</label>
                                    <input type="text" name="house_number" id="house_number" value="{{ old('house_number', $student->house_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('house_number')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('phone')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Guardians Information -->
                        @php
                            $guardians = $student->guardians->map(function($guardian, $index) {
                                return [
                                    'id' => $guardian->id,
                                    'category' => $index === 0 ? 'Primary' : ($index === 1 ? 'Secondary' : 'Additional'),
                                    'first_name' => $guardian->first_name,
                                    'father_name' => $guardian->father_name,
                                    'grandfather_name' => $guardian->grandfather_name,
                                    'phone' => $guardian->phone,
                                    'email' => $guardian->email,
                                    'relationship' => $guardian->relationship,
                                    'is_emergency' => (bool)$guardian->is_emergency_contact,
                                    'comm_prefs' => $guardian->communication_preferences ?? [],
                                    'photo_url' => $guardian->photo ? asset('storage/' . $guardian->photo) : null
                                ];
                            })->values()->toArray();

                            if (empty($guardians)) {
                                $guardians = [
                                    ['category' => 'Primary', 'first_name' => '', 'father_name' => '', 'grandfather_name' => '', 'phone' => '', 'email' => '', 'relationship' => '', 'is_emergency' => true, 'comm_prefs' => []]
                                ];
                            }
                        @endphp
                        <div x-data="{
                            guardians: {{ Js::from($guardians) }},
                            addGuardian() {
                                this.guardians.push({ category: 'Additional', first_name: '', father_name: '', grandfather_name: '', phone: '', email: '', relationship: '', is_emergency: false, comm_prefs: [] });
                            },
                            removeGuardian(index) {
                                if(this.guardians.length > 1) {
                                    this.guardians.splice(index, 1);
                                }
                            }
                        }" class="mb-8">
                            <div class="flex justify-between items-center border-b pb-2 mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Guardian Information</h3>
                                <button type="button" @click="addGuardian()" class="bg-indigo-600 text-white text-sm px-3 py-1 rounded hover:bg-indigo-700">
                                    + Add Guardian
                                </button>
                            </div>

                            <template x-for="(guardian, index) in guardians" :key="index">
                                <div class="bg-gray-50 p-4 rounded-lg mb-4 border border-gray-200 relative">
                                    <div class="flex justify-between mb-4">
                                        <h4 class="font-bold text-gray-700" x-text="index === 0 ? 'Primary Guardian *' : (index === 1 ? 'Secondary Guardian (Optional)' : 'Additional Guardian')"></h4>
                                        <button type="button" @click="removeGuardian(index)" x-show="index > 0" class="text-red-600 hover:text-red-800 text-sm font-bold">Remove</button>
                                    </div>

                                    <!-- Hidden input for ID to update existing records -->
                                    <input type="hidden" :name="'guardians['+index+'][id]'" x-model="guardian.id">

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="md:col-span-3">
                                            <label :for="'guardian_photo_'+index" class="block text-sm font-medium text-gray-700">Guardian Photo</label>
                                            <template x-if="guardian.photo_url">
                                                <div class="mb-2">
                                                    <img :src="guardian.photo_url" alt="Guardian Photo" class="h-20 w-20 object-cover rounded">
                                                </div>
                                            </template>
                                            <input type="file" :name="'guardians['+index+'][photo]'" :id="'guardian_photo_'+index" accept="image/*" class="mt-1 block w-full">
                                        </div>
                                        <div>
                                            <label :for="'guardian_first_name_'+index" class="block text-sm font-medium text-gray-700">First Name <span x-show="index===0">*</span></label>
                                            <input type="text" :name="'guardians['+index+'][first_name]'" x-model="guardian.first_name" :required="index===0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label :for="'guardian_father_name_'+index" class="block text-sm font-medium text-gray-700">Father Name <span x-show="index===0">*</span></label>
                                            <input type="text" :name="'guardians['+index+'][father_name]'" x-model="guardian.father_name" :required="index===0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label :for="'guardian_grandfather_name_'+index" class="block text-sm font-medium text-gray-700">Grandfather Name <span x-show="index===0">*</span></label>
                                            <input type="text" :name="'guardians['+index+'][grandfather_name]'" x-model="guardian.grandfather_name" :required="index===0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label :for="'guardian_phone_'+index" class="block text-sm font-medium text-gray-700">Phone Number <span x-show="index===0">*</span></label>
                                            <input type="text" :name="'guardians['+index+'][phone]'" x-model="guardian.phone" :required="index===0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label :for="'guardian_email_'+index" class="block text-sm font-medium text-gray-700">Email</label>
                                            <input type="email" :name="'guardians['+index+'][email]'" x-model="guardian.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label :for="'guardian_relationship_'+index" class="block text-sm font-medium text-gray-700">Relationship <span x-show="index===0">*</span></label>
                                            <select :name="'guardians['+index+'][relationship]'" x-model="guardian.relationship" :required="index===0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">Select Relationship</option>
                                                <option value="Father">Father</option>
                                                <option value="Mother">Mother</option>
                                                <option value="Grandparent">Grandparent</option>
                                                <option value="Uncle">Uncle</option>
                                                <option value="Aunt">Aunt</option>
                                                <option value="Sister">Sister</option>
                                                <option value="Brother">Brother</option>
                                                <option value="Guardian">Legal Guardian</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Preferences -->
                                        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4 mt-2">
                                            <div>
                                                <label class="flex items-center space-x-2 cursor-pointer">
                                                    <input type="checkbox" :name="'guardians['+index+'][is_emergency_contact]'" value="1" x-model="guardian.is_emergency" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                    <span class="text-sm font-medium text-red-700">Emergency Contact</span>
                                                </label>
                                            </div>
                                            <div>
                                                <span class="block text-sm font-medium text-gray-700 mb-2">Communication Preferences:</span>
                                                <div class="flex space-x-4">
                                                    <label class="flex items-center space-x-2 cursor-pointer">
                                                        <input type="checkbox" :name="'guardians['+index+'][communication_preferences][]'" value="sms" x-model="guardian.comm_prefs" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <span class="text-sm text-gray-600">SMS</span>
                                                    </label>
                                                    <label class="flex items-center space-x-2 cursor-pointer">
                                                        <input type="checkbox" :name="'guardians['+index+'][communication_preferences][]'" value="email" x-model="guardian.comm_prefs" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <span class="text-sm text-gray-600">Email</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Medical Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Medical Information (Optional)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="blood_type" class="block text-sm font-medium text-gray-700">Blood Type</label>
                                    <input type="text" name="blood_type" id="blood_type" value="{{ old('blood_type', $student->medicalInfo->blood_group ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('blood_type')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label for="allergies" class="block text-sm font-medium text-gray-700">Allergies</label>
                                    <textarea name="allergies" id="allergies" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('allergies', $student->medicalInfo->allergies ?? '') }}</textarea>
                                    @error('allergies')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-3">
                                    <label for="medical_conditions" class="block text-sm font-medium text-gray-700">Medical Conditions</label>
                                    <textarea name="medical_conditions" id="medical_conditions" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('medical_conditions', $student->medicalInfo->medical_issues ?? '') }}</textarea>
                                    @error('medical_conditions')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700">Emergency Contact Name</label>
                                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name', $student->medicalInfo->emergency_contact ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('emergency_contact_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700">Emergency Contact Phone</label>
                                    <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $student->medicalInfo->emergency_contact_phone ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('emergency_contact_phone')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Transportation Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Transportation Information (Optional)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="uses_school_transport" id="uses_school_transport" value="1" {{ old('uses_school_transport', $student->transportation->uses_school_transport ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">Uses School Transport</span>
                                    </label>
                                    @error('uses_school_transport')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="transport_route" class="block text-sm font-medium text-gray-700">Route</label>
                                    <input type="text" name="transport_route" id="transport_route" value="{{ old('transport_route', $student->transportation->route ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('transport_route')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label for="pickup_location" class="block text-sm font-medium text-gray-700">Pickup Location</label>
                                    <input type="text" name="pickup_location" id="pickup_location" value="{{ old('pickup_location', $student->transportation->pickup_location ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('pickup_location')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="driver_name" class="block text-sm font-medium text-gray-700">Driver Name</label>
                                    <input type="text" name="driver_name" id="driver_name" value="{{ old('driver_name', $student->transportation->driver_name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('driver_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="driver_phone" class="block text-sm font-medium text-gray-700">Driver Phone</label>
                                    <input type="text" name="driver_phone" id="driver_phone" value="{{ old('driver_phone', $student->transportation->driver_phone ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('driver_phone')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-3">
                                    <label for="driver_photo" class="block text-sm font-medium text-gray-700">Driver Photo</label>
                                    @if($student->transportation && $student->transportation->driver_photo)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $student->transportation->driver_photo) }}" alt="Driver Photo" class="h-20 w-20 object-cover rounded">
                                        </div>
                                    @endif
                                    <input type="file" name="driver_photo" id="driver_photo" accept="image/*" class="mt-1 block w-full">
                                    @error('driver_photo')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.students.show', $student) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancel</a>
                            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Update Student</button>
                        </div>
                    </form>
                </div>
            </div>
    </div>
</x-admin-layout>
