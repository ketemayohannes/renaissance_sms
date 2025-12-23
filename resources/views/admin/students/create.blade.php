<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Register New Student') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Students', 'url' => route('admin.students.index')],
                ['label' => 'Register New Student', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(!$activeYear)
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong>Warning!</strong> No active academic year found. Please activate an academic year before registering students.
                        </div>
                    @endif

                    <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Personal Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-3">
                                    <label for="student_photo" class="block text-sm font-medium text-gray-700">Student Photo</label>
                                    <input type="file" name="student_photo" id="student_photo" accept="image/*" class="mt-1 block w-full">
                                    @error('student_photo')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('first_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="father_name" class="block text-sm font-medium text-gray-700">Father Name *</label>
                                    <input type="text" name="father_name" id="father_name" value="{{ old('father_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('father_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="grandfather_name" class="block text-sm font-medium text-gray-700">Grandfather Name *</label>
                                    <input type="text" name="grandfather_name" id="grandfather_name" value="{{ old('grandfather_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('grandfather_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender *</label>
                                    <select name="gender" id="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select Gender</option>
                                        <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Male</option>
                                        <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('date_of_birth')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="birth_country" class="block text-sm font-medium text-gray-700">Birth Country</label>
                                    <input type="text" name="birth_country" id="birth_country" value="{{ old('birth_country') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('birth_country')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="birth_city" class="block text-sm font-medium text-gray-700">Birth City</label>
                                    <input type="text" name="birth_city" id="birth_city" value="{{ old('birth_city') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('birth_city')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="nationality" class="block text-sm font-medium text-gray-700">Nationality</label>
                                    <input type="text" name="nationality" id="nationality" value="{{ old('nationality', 'Ethiopian') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nationality')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="language_spoken" class="block text-sm font-medium text-gray-700">Language Spoken</label>
                                    <input type="text" name="language_spoken" id="language_spoken" value="{{ old('language_spoken') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('language_spoken')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
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
                                            <option value="{{ $subcity }}" {{ old('subcity') == $subcity ? 'selected' : '' }}>{{ $subcity }}</option>
                                        @endforeach
                                    </select>
                                    @error('subcity')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="woreda" class="block text-sm font-medium text-gray-700">Woreda</label>
                                    <input type="text" name="woreda" id="woreda" value="{{ old('woreda') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('woreda')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="house_number" class="block text-sm font-medium text-gray-700">House Number</label>
                                    <input type="text" name="house_number" id="house_number" value="{{ old('house_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('house_number')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-3">
                                    <label for="address" class="block text-sm font-medium text-gray-700">Additional Address Details</label>
                                    <textarea name="address" id="address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address') }}</textarea>
                                    @error('address')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Admission & Enrollment -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Admission & Enrollment</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="admission_number" class="block text-sm font-medium text-gray-700">Admission Number *</label>
                                    <input type="text" name="admission_number" id="admission_number" value="{{ old('admission_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('admission_number')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="admission_date" class="block text-sm font-medium text-gray-700">Admission Date *</label>
                                    <input type="date" name="admission_date" id="admission_date" value="{{ old('admission_date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('admission_date')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="section_id" class="block text-sm font-medium text-gray-700">Assign Section ({{ $activeYear->name ?? 'No Active Year' }}) *</label>
                                    <select name="section_id" id="section_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select Section</option>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }} 
                                                class="{{ $section->enrolled_count >= $section->capacity ? 'text-red-600 font-bold' : '' }}">
                                                {{ $section->gradeLevel->name }} - {{ $section->name }} 
                                                ({{ $section->enrolled_count }}/{{ $section->capacity }} filled)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('section_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address (Optional)</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <p class="text-xs text-gray-500 mt-1">If left blank, system will generate one.</p>
                                    @error('email')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('phone')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Guardians Information -->
                        <div x-data="{
                            guardians: [
                                { category: 'Primary', first_name: '', father_name: '', grandfather_name: '', phone: '', email: '', relationship: '', is_emergency: true, comm_prefs: [] },
                                { category: 'Secondary', first_name: '', father_name: '', grandfather_name: '', phone: '', email: '', relationship: '', is_emergency: false, comm_prefs: [] }
                            ],
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

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="md:col-span-3">
                                            <label :for="'guardian_photo_'+index" class="block text-sm font-medium text-gray-700">Guardian Photo</label>
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
                                                        <input type="checkbox" :name="'guardians['+index+'][communication_preferences][]'" value="sms" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <span class="text-sm text-gray-600">SMS</span>
                                                    </label>
                                                    <label class="flex items-center space-x-2 cursor-pointer">
                                                        <input type="checkbox" :name="'guardians['+index+'][communication_preferences][]'" value="email" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
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
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Medical Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="blood_group" class="block text-sm font-medium text-gray-700">Blood Group</label>
                                    <select name="blood_group" id="blood_group" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('blood_group')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="emergency_contact" class="block text-sm font-medium text-gray-700">Emergency Contact Number</label>
                                    <input type="text" name="emergency_contact" id="emergency_contact" value="{{ old('emergency_contact') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('emergency_contact')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label for="medical_issues" class="block text-sm font-medium text-gray-700">Medical Issues/Conditions</label>
                                    <textarea name="medical_issues" id="medical_issues" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="List any medical conditions or health issues">{{ old('medical_issues') }}</textarea>
                                    @error('medical_issues')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label for="current_medication" class="block text-sm font-medium text-gray-700">Current Medication</label>
                                    <textarea name="current_medication" id="current_medication" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="List any medications currently being taken">{{ old('current_medication') }}</textarea>
                                    @error('current_medication')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label for="allergies" class="block text-sm font-medium text-gray-700">Allergies</label>
                                    <textarea name="allergies" id="allergies" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="List any known allergies">{{ old('allergies') }}</textarea>
                                    @error('allergies')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Transportation (Optional) -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Transportation Information (Optional)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-3">
                                    <label for="driver_photo" class="block text-sm font-medium text-gray-700">Driver Photo</label>
                                    <input type="file" name="driver_photo" id="driver_photo" accept="image/*" class="mt-1 block w-full">
                                    @error('driver_photo')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="driver_id" class="block text-sm font-medium text-gray-700">Driver ID</label>
                                    <input type="text" name="driver_id" id="driver_id" value="{{ old('driver_id') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('driver_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="driver_first_name" class="block text-sm font-medium text-gray-700">Driver First Name</label>
                                    <input type="text" name="driver_first_name" id="driver_first_name" value="{{ old('driver_first_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('driver_first_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="driver_father_name" class="block text-sm font-medium text-gray-700">Driver Father Name</label>
                                    <input type="text" name="driver_father_name" id="driver_father_name" value="{{ old('driver_father_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('driver_father_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="driver_grandfather_name" class="block text-sm font-medium text-gray-700">Driver Grandfather Name</label>
                                    <input type="text" name="driver_grandfather_name" id="driver_grandfather_name" value="{{ old('driver_grandfather_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('driver_grandfather_name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="license_number" class="block text-sm font-medium text-gray-700">License Number</label>
                                    <input type="text" name="license_number" id="license_number" value="{{ old('license_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('license_number')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="vehicle_plate" class="block text-sm font-medium text-gray-700">Vehicle Plate Number</label>
                                    <input type="text" name="vehicle_plate" id="vehicle_plate" value="{{ old('vehicle_plate') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('vehicle_plate')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="route" class="block text-sm font-medium text-gray-700">Route</label>
                                    <input type="text" name="route" id="route" value="{{ old('route') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('route')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('admin.students.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Register Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
