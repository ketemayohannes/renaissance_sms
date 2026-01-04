<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <div>
                    <h2 class="font-heading font-bold text-2xl text-slate-900 italic">Edit Student Profile</h2>
                    <p class="text-slate-500 text-sm font-medium">{{ $student->full_name }} • <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest">{{ $student->student_id }}</span></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.students.show', $student) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2 shadow-sm border-b-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    View Profile
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => $student->full_name, 'url' => route('admin.students.show', $student)],
            ['label' => 'Update Profile', 'url' => '#']
        ]" />

        <form action="{{ route('admin.students.update', $student) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- Section 1: Personal Information -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Personal Information</h3>
                            <p class="text-slate-500 text-sm">Update basic identity details and student identification.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <!-- Photo Upload -->
                        <div class="md:col-span-1" x-data="{ photoPreview: '{{ $student->photo ? asset('storage/' . $student->photo) : '' }}' }">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Student Photo</label>
                            <div class="relative group">
                                <div class="w-full aspect-square rounded-[2rem] bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition-all group-hover:border-indigo-300">
                                    <template x-if="!photoPreview">
                                        <div class="text-center p-6">
                                            <svg class="mx-auto h-12 w-12 text-slate-300" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                            <p class="mt-2 text-xs font-semibold text-slate-400">Update Photo</p>
                                        </div>
                                    </template>
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="w-full h-full object-cover">
                                    </template>
                                    <input type="file" name="photo" class="absolute inset-0 opacity-0 cursor-pointer" 
                                           @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => photoPreview = e.target.result; reader.readAsDataURL(file); }">
                                </div>
                                <template x-if="photoPreview">
                                    <div class="absolute -bottom-2 inset-x-0 flex justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <div class="bg-indigo-600 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-tighter shadow-lg">Change Image</div>
                                    </div>
                                </template>
                            </div>
                            @error('photo')<p class="mt-2 text-xs font-bold text-rose-500 uppercase tracking-tight">{{ $message }}</p>@enderror
                        </div>

                        <!-- Fields -->
                        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="first_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $student->first_name) }}" required 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                                @error('first_name')<p class="mt-1 text-[10px] font-black text-rose-500 uppercase">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="father_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Father Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="father_name" id="father_name" value="{{ old('father_name', $student->father_name) }}" required 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                                @error('father_name')<p class="mt-1 text-[10px] font-black text-rose-500 uppercase">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="grandfather_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Grandfather Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="grandfather_name" id="grandfather_name" value="{{ old('grandfather_name', $student->grandfather_name) }}" required 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                                @error('grandfather_name')<p class="mt-1 text-[10px] font-black text-rose-500 uppercase">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="gender" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Gender <span class="text-rose-500">*</span></label>
                                <select name="gender" id="gender" required 
                                        class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700 appearance-none">
                                    <option value="M" {{ old('gender', $student->gender) == 'M' ? 'selected' : '' }}>Male</option>
                                    <option value="F" {{ old('gender', $student->gender) == 'F' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div>
                                <label for="date_of_birth" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Date of Birth <span class="text-rose-500">*</span></label>
                                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" required 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                            </div>
                            <div>
                                <label for="nationality" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nationality</label>
                                <input type="text" name="nationality" id="nationality" value="{{ old('nationality', $student->nationality) }}" 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                            </div>
                            <div>
                                <label for="birth_city" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Birth City</label>
                                <input type="text" name="birth_city" id="birth_city" value="{{ old('birth_city', $student->birth_city) }}" 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                            </div>
                            <div class="md:col-span-2">
                                <label for="language_spoken" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Languages Spoken</label>
                                <input type="text" name="language_spoken" id="language_spoken" value="{{ old('language_spoken', $student->language_spoken) }}" 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Contact & Address -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Contact & Address</h3>
                            <p class="text-slate-500 text-sm">Update residence and contact information.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="subcity" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Subcity</label>
                            <select name="subcity" id="subcity" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                                <option value="">Select Subcity</option>
                                @foreach(['Addis Ketema', 'Akaki Kality', 'Arada', 'Bole', 'Gullele', 'Kirkos', 'Kolfe Keranio', 'Lideta', 'Nifas Silk-Lafto', 'Yeka', 'Lemi Kura'] as $subcity)
                                    <option value="{{ $subcity }}" {{ old('subcity', $student->subcity) == $subcity ? 'selected' : '' }}>{{ $subcity }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="woreda" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Woreda / Kebele</label>
                            <input type="text" name="woreda" id="woreda" value="{{ old('woreda', $student->woreda) }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="house_number" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">House No.</label>
                            <input type="text" name="house_number" id="house_number" value="{{ old('house_number', $student->house_number) }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Personal Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone) }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-black text-indigo-600 tracking-wider">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Admission Records -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm border border-amber-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Admission Records</h3>
                            <p class="text-slate-500 text-sm">System-generated records and admission details.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Internal UUID</label>
                            <div class="px-4 py-3 bg-slate-100/50 border border-slate-200 rounded-xl font-mono text-[10px] text-slate-500 select-all">{{ $student->student_id }}</div>
                        </div>
                        <div>
                            <label for="admission_number" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Admission No. <span class="text-rose-500">*</span></label>
                            <input type="text" name="admission_number" id="admission_number" value="{{ old('admission_number', $student->admission_number) }}" required
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl font-black text-slate-800 tracking-tighter text-lg">
                        </div>
                        <div>
                            <label for="admission_date" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Admission Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="admission_date" id="admission_date" value="{{ old('admission_date', $student->admission_date?->format('Y-m-d')) }}" required
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
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
            <!-- Section 4: Guardian Information -->
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
            }" class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between px-8 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Guardian Information</h3>
                            <p class="text-slate-500 text-sm">Manage parents and legal guardians.</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-4">
                        @if($student->siblings->count() > 0)
                        <label class="flex items-center gap-3 bg-white px-4 py-2 rounded-xl border border-indigo-100 cursor-pointer hover:bg-indigo-50 transition-all group shadow-sm">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="sync_siblings" value="1" 
                                       class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-indigo-200 checked:bg-indigo-600 checked:border-indigo-600 transition-all">
                                <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Sibling Sync</span>
                                <span class="text-[9px] text-slate-400 font-bold uppercase">Apply to {{ $student->siblings->count() }} siblings</span>
                            </div>
                        </label>
                        @endif

                        <button type="button" @click="addGuardian()" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all gap-2 shadow-lg shadow-indigo-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Add Guardian
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4">
                    <template x-for="(guardian, index) in guardians" :key="index">
                        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8 relative group transition-all hover:shadow-2xl hover:shadow-indigo-100">
                            <div class="flex justify-between items-center mb-8">
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100/50" 
                                      x-text="index === 0 ? 'Primary Guardian' : (index === 1 ? 'Secondary Guardian' : 'Additional Guardian')"></span>
                                <button type="button" @click="removeGuardian(index)" x-show="index > 0" 
                                        class="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>

                            <!-- Hidden input for ID to update existing records -->
                            <input type="hidden" :name="'guardians['+index+'][id]'" x-model="guardian.id">

                            <div class="space-y-6">
                                <!-- Guardian Photo Preview & Input -->
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 overflow-hidden border-2 border-white shadow-md">
                                        <template x-if="guardian.photo_url">
                                            <img :src="guardian.photo_url" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!guardian.photo_url">
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <label :for="'guardian_photo_'+index" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Update Photo</label>
                                        <input type="file" :name="'guardians['+index+'][photo]'" :id="'guardian_photo_'+index" accept="image/*" 
                                               @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => guardian.photo_url = e.target.result; reader.readAsDataURL(file); }"
                                               class="w-full text-[10px] text-slate-500 file:mr-4 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-slate-100 file:text-slate-600 hover:file:bg-slate-200 cursor-pointer">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">First Name <span x-show="index===0" class="text-rose-500">*</span></label>
                                        <input type="text" :name="'guardians['+index+'][first_name]'" x-model="guardian.first_name" :required="index===0" 
                                               class="w-full px-4 py-2 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Father Name <span x-show="index===0" class="text-rose-500">*</span></label>
                                        <input type="text" :name="'guardians['+index+'][father_name]'" x-model="guardian.father_name" :required="index===0" 
                                               class="w-full px-4 py-2 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Grandfather Name <span x-show="index===0" class="text-rose-500">*</span></label>
                                        <input type="text" :name="'guardians['+index+'][grandfather_name]'" x-model="guardian.grandfather_name" :required="index===0" 
                                               class="w-full px-4 py-2 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Phone <span x-show="index===0" class="text-rose-500">*</span></label>
                                        <input type="text" :name="'guardians['+index+'][phone]'" x-model="guardian.phone" :required="index===0" 
                                               class="w-full px-4 py-2 bg-slate-50/50 border border-slate-200 rounded-xl font-black text-indigo-600">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Relationship <span x-show="index===0" class="text-rose-500">*</span></label>
                                        <select :name="'guardians['+index+'][relationship]'" x-model="guardian.relationship" :required="index===0" 
                                                class="w-full px-4 py-2 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                                            <option value="">Select</option>
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
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Email</label>
                                        <input type="email" :name="'guardians['+index+'][email]'" x-model="guardian.email" 
                                               class="w-full px-4 py-2 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-slate-100 flex flex-wrap gap-6 items-center">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <div class="relative flex items-center">
                                            <input type="checkbox" :name="'guardians['+index+'][is_emergency_contact]'" value="1" x-model="guardian.is_emergency" 
                                                   class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-200 checked:bg-rose-500 checked:border-rose-500 transition-all">
                                            <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <span class="text-xs font-bold text-slate-600 uppercase tracking-widest group-hover:text-rose-500 transition-colors">Emergency Contact</span>
                                    </label>

                                    <div class="flex items-center gap-3 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mr-2">Notify via:</span>
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" :name="'guardians['+index+'][communication_preferences][]'" value="sms" x-model="guardian.comm_prefs" class="rounded text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-[10px] font-black text-slate-600 uppercase">SMS</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" :name="'guardians['+index+'][communication_preferences][]'" value="email" x-model="guardian.comm_prefs" class="rounded text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-[10px] font-black text-slate-600 uppercase">Email</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Section 5: Medical Information -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-sm border border-rose-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Medical Information</h3>
                            <p class="text-slate-500 text-sm">Update health records and emergency contacts.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="blood_type" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Blood Group</label>
                            <input type="text" name="blood_type" id="blood_type" value="{{ old('blood_type', $student->medicalInfo->blood_group ?? '') }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-black text-rose-600">
                        </div>
                        <div>
                            <label for="emergency_contact_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Backup Contact Name</label>
                            <input type="text" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name', $student->medicalInfo->emergency_contact ?? '') }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="emergency_contact_phone" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Backup Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $student->medicalInfo->emergency_contact_phone ?? '') }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-black text-indigo-600">
                        </div>
                        <div>
                            <label for="allergies" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Allergies</label>
                            <textarea name="allergies" id="allergies" rows="1" class="w-full px-4 py-3 bg-rose-50/30 border border-rose-100 rounded-xl font-semibold text-rose-700">{{ old('allergies', $student->medicalInfo->allergies ?? '') }}</textarea>
                        </div>
                        <div class="md:col-span-4">
                            <label for="medical_conditions" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Chronic Conditions / Medical Issues</label>
                            <textarea name="medical_conditions" id="medical_conditions" rows="2" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">{{ old('medical_conditions', $student->medicalInfo->medical_issues ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 6: Transportation Details -->
            <div x-data="{ hasTransport: {{ old('uses_school_transport', $student->transportation->uses_school_transport ?? false) ? 'true' : 'false' }} }" class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Transportation Details</h3>
                                <p class="text-slate-500 text-sm">Update school bus assignments and routes.</p>
                            </div>
                        </div>
                        
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="uses_school_transport" value="1" x-model="hasTransport" class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-3 text-sm font-black text-slate-700 uppercase tracking-widest">School Transport</span>
                        </label>
                    </div>

                    <div x-show="hasTransport" x-collapse x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 pt-6 border-t border-slate-100">
                            <div>
                                <label for="driver_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Driver Name</label>
                                <input type="text" name="driver_name" id="driver_name" value="{{ old('driver_name', $student->transportation->driver_name ?? '') }}" 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                            </div>
                            <div>
                                <label for="driver_phone" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Driver Phone</label>
                                <input type="text" name="driver_phone" id="driver_phone" value="{{ old('driver_phone', $student->transportation->driver_phone ?? '') }}" 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-black text-indigo-600">
                            </div>
                            <div class="md:col-span-2">
                                <label for="transport_route" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Pickup/Drop Route</label>
                                <input type="text" name="transport_route" id="transport_route" value="{{ old('transport_route', $student->transportation->route ?? '') }}" 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                            </div>
                            <div class="md:col-span-4">
                                <label for="pickup_location" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Specific Landmark / Directions</label>
                                <textarea name="pickup_location" id="pickup_location" rows="2" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">{{ old('pickup_location', $student->transportation->pickup_location ?? '') }}</textarea>
                            </div>
                            <div class="md:col-span-4" x-data="{ driverPreview: '{{ ($student->transportation && $student->transportation->driver_photo) ? asset('storage/' . $student->transportation->driver_photo) : '' }}' }">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Driver Photo (Optional)</label>
                                <div class="flex items-center gap-6">
                                    <div class="w-20 h-20 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden">
                                        <template x-if="driverPreview">
                                            <img :src="driverPreview" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!driverPreview">
                                            <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </template>
                                    </div>
                                    <input type="file" name="driver_photo" accept="image/*" class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer"
                                           @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => driverPreview = e.target.result; reader.readAsDataURL(file); }">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 pb-12">
                <a href="{{ route('admin.students.show', $student) }}" 
                   class="px-8 py-4 bg-white text-slate-500 font-bold rounded-2xl hover:bg-slate-50 transition-all uppercase tracking-widest text-xs border border-slate-200">
                    Cancel Changes
                </a>
                <button type="submit" 
                        class="px-12 py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-200 uppercase tracking-widest text-xs">
                    Update Student Records
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
