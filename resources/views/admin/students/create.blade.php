<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Register New Student</h2>
                <p class="text-slate-500 text-sm mt-1">Enroll a new student into the system with initial academic records.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.students.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2 shadow-sm border-b-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => 'Register New Student', 'url' => '#']
        ]" />

        @if(!$activeYear)
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex gap-3 text-rose-800 animate-pulse">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div class="text-sm font-bold">No active academic year found. Please activate one before registering students.</div>
            </div>
        @endif

        <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <!-- Section 1: Personal Information -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Personal Information</h3>
                            <p class="text-slate-500 text-sm">Basic identity details and student identification.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <!-- Photo Upload -->
                        <div class="md:col-span-1" x-data="{ photoPreview: null }">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Student Photo</label>
                            <div class="relative group">
                                <div class="w-full aspect-square rounded-[2rem] bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition-all group-hover:border-indigo-300">
                                    <template x-if="!photoPreview">
                                        <div class="text-center p-6">
                                            <svg class="mx-auto h-12 w-12 text-slate-300" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                            <p class="mt-2 text-xs font-semibold text-slate-400">Click to upload photo</p>
                                        </div>
                                    </template>
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" alt="Photo preview" class="w-full h-full object-cover">
                                    </template>
                                    <input type="file" name="student_photo" class="absolute inset-0 opacity-0 cursor-pointer" 
                                           @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => photoPreview = e.target.result; reader.readAsDataURL(file); }">
                                </div>
                                <template x-if="photoPreview">
                                    <button type="button" @click="photoPreview = null; $refs.photoInput.value = ''" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full p-1.5 shadow-lg border-2 border-white hover:scale-110 transition-transform">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </template>
                            </div>
                            @error('student_photo')<p class="mt-2 text-xs font-bold text-rose-500 uppercase tracking-tight">{{ $message }}</p>@enderror
                        </div>

                        <!-- Fields -->
                        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="first_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-semibold text-slate-700">
                                @error('first_name')<p class="mt-1 text-[10px] font-black text-rose-500 uppercase">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="father_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Father Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="father_name" id="father_name" value="{{ old('father_name') }}" required 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-semibold text-slate-700">
                                @error('father_name')<p class="mt-1 text-[10px] font-black text-rose-500 uppercase">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="grandfather_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Grandfather Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="grandfather_name" id="grandfather_name" value="{{ old('grandfather_name') }}" required 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-semibold text-slate-700">
                                @error('grandfather_name')<p class="mt-1 text-[10px] font-black text-rose-500 uppercase">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="gender" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Gender <span class="text-rose-500">*</span></label>
                                <select name="gender" id="gender" required 
                                        class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-semibold text-slate-700 appearance-none">
                                    <option value="">Select Gender</option>
                                    <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Male</option>
                                    <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div>
                                <label for="date_of_birth" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Date of Birth <span class="text-rose-500">*</span></label>
                                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" required 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-semibold text-slate-700">
                                @error('date_of_birth')<p class="mt-1 text-[10px] font-black text-rose-500 uppercase">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="nationality" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nationality</label>
                                <input type="text" name="nationality" id="nationality" value="{{ old('nationality', 'Ethiopian') }}" 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-semibold text-slate-700">
                            </div>
                            <div>
                                <label for="birth_city" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Birth City</label>
                                <input type="text" name="birth_city" id="birth_city" value="{{ old('birth_city') }}" 
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-semibold text-slate-700">
                            </div>
                            <div class="md:col-span-2">
                                <label for="language_spoken" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Languages Spoken</label>
                                <input type="text" name="language_spoken" id="language_spoken" value="{{ old('language_spoken') }}" placeholder="e.g. Amharic, English"
                                       class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-semibold text-slate-700">
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
                            <p class="text-slate-500 text-sm">Where the student resides and how to reach them.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="subcity" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Subcity</label>
                            <select name="subcity" id="subcity" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl">
                                <option value="">Select Subcity</option>
                                @foreach(['Addis Ketema', 'Akaki Kality', 'Arada', 'Bole', 'Gullele', 'Kirkos', 'Kolfe Keranio', 'Lideta', 'Nifas Silk-Lafto', 'Yeka', 'Lemi Kura'] as $subcity)
                                    <option value="{{ $subcity }}" {{ old('subcity') == $subcity ? 'selected' : '' }}>{{ $subcity }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="woreda" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Woreda / Kebele</label>
                            <input type="text" name="woreda" id="woreda" value="{{ old('woreda') }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="house_number" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">House No.</label>
                            <input type="text" name="house_number" id="house_number" value="{{ old('house_number') }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Personal Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div class="md:col-span-2">
                            <label for="email" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Leave blank for auto-generation"
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div class="md:col-span-2">
                            <label for="address" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Specific Landmark / Directions</label>
                            <input type="text" name="address" id="address" value="{{ old('address') }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Admission & Enrollment -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm border border-amber-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Admission & Enrollment</h3>
                            <p class="text-slate-500 text-sm">Assign student to academic years and grades.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="admission_number" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Admission No. <span class="text-rose-500">*</span></label>
                            <input type="text" name="admission_number" id="admission_number" value="{{ old('admission_number') }}" required 
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all font-black text-slate-800 tracking-tighter text-lg">
                            @error('admission_number')<p class="mt-1 text-[10px] font-black text-rose-500 uppercase">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="admission_date" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Admission Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="admission_date" id="admission_date" value="{{ old('admission_date', date('Y-m-d')) }}" required 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="section_id" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Section Assignment <span class="text-rose-500">*</span></label>
                            <select name="section_id" id="section_id" required 
                                    class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all font-bold text-slate-700">
                                <option value="">Select Grade/Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}
                                            class="{{ $section->enrolled_count >= $section->capacity ? 'text-rose-600 font-black' : 'text-slate-900 font-bold' }}">
                                        {{ $section->gradeLevel->name }} - {{ $section->name }} 
                                        ({{ $section->enrolled_count }}/{{ $section->capacity }})
                                    </option>
                                @endforeach
                            </select>
                            @if($activeYear)
                                <p class="mt-2 text-[10px] font-bold text-amber-600 uppercase tracking-widest">Enrolling for {{ $activeYear->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Guardians Information -->
            <div x-data="{
                guardians: {{ old('guardians') ? json_encode(old('guardians')) : '[{ category: \'Primary\', first_name: \'\', father_name: \'\', grandfather_name: \'\', phone: \'\', email: \'\', relationship: \'\', is_emergency: true, comm_prefs: [] }, { category: \'Secondary\', first_name: \'\', father_name: \'\', grandfather_name: \'\', phone: \'\', email: \'\', relationship: \'\', is_emergency: false, comm_prefs: [] }]' }},
                addGuardian() {
                    this.guardians.push({ category: 'Additional', first_name: '', father_name: '', grandfather_name: '', phone: '', email: '', relationship: '', is_emergency: false, comm_prefs: [] });
                },
                removeGuardian(index) {
                    if(this.guardians.length > 1) {
                        this.guardians.splice(index, 1);
                    }
                }
            }" class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 px-4 mt-12 gap-4" 
                     x-data="{ 
                        searchQuery: '', 
                        searchResults: [], 
                        isSearching: false,
                        showSearch: false,
                        async performSearch() {
                            if (this.searchQuery.length < 3) return;
                            this.isSearching = true;
                            try {
                                const response = await fetch(`{{ route('admin.guardians.search') }}?q=${encodeURIComponent(this.searchQuery)}`);
                                this.searchResults = await response.json();
                            } catch (e) {
                                console.error('Search failed', e);
                            } finally {
                                this.isSearching = false;
                            }
                        },
                        selectGuardian(guardian, targetIndex) {
                            // Map existing guardian data to the form
                            this.guardians[targetIndex].first_name = guardian.first_name;
                            this.guardians[targetIndex].father_name = guardian.father_name;
                            this.guardians[targetIndex].grandfather_name = guardian.grandfather_name;
                            this.guardians[targetIndex].phone = guardian.phone;
                            this.guardians[targetIndex].email = guardian.email;
                            this.guardians[targetIndex].relationship = guardian.relationship;
                            this.showSearch = false;
                            this.searchQuery = '';
                        }
                     }">
                     <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Guardian Information</h3>
                            <p class="text-slate-500 text-sm">Main caretakers and emergency contacts for the student.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <button type="button" @click="showSearch = !showSearch" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                Find Existing Parent
                            </button>
                            
                            <!-- Search Modal/Dropdown -->
                            <div x-show="showSearch" @click.away="showSearch = false" class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 p-4 z-50">
                                <div class="space-y-4">
                                    <div class="relative">
                                        <input type="text" x-model="searchQuery" @input.debounce.500ms="performSearch()" placeholder="Name, Phone or Email..." 
                                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </div>
                                    
                                    <div class="max-h-60 overflow-y-auto space-y-1">
                                        <template x-if="isSearching">
                                            <div class="py-4 text-center text-xs text-slate-400">Searching...</div>
                                        </template>
                                        <template x-for="result in searchResults" :key="result.id">
                                            <button type="button" @click="selectGuardian(result, 0)" class="w-full text-left p-3 hover:bg-indigo-50 rounded-xl transition-all group">
                                                <div class="font-bold text-slate-700 text-xs group-hover:text-indigo-600 transition-colors" x-text="result.first_name + ' ' + result.father_name"></div>
                                                <div class="text-[10px] text-slate-400" x-text="result.phone"></div>
                                            </button>
                                        </template>
                                        <template x-if="searchResults.length === 0 && searchQuery.length >= 3 && !isSearching">
                                            <div class="py-4 text-center text-xs text-slate-400 italic">No results found</div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" @click="addGuardian()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-100 transition-all gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Guardian
                        </button>
                    </div>
                </div>

                <template x-for="(guardian, index) in guardians" :key="index">
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden transition-all hover:border-indigo-100/50"
                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="p-8">
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-bold" x-text="index + 1"></span>
                                    <h4 class="font-bold text-slate-800" x-text="index === 0 ? 'Primary Guardian (Required)' : (index === 1 ? 'Secondary Guardian (Optional)' : 'Additional Guardian')"></h4>
                                </div>
                                <button type="button" @click="removeGuardian(index)" x-show="index > 0" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">First Name <span x-show="index===0" class="text-rose-500">*</span></label>
                                    <input type="text" :name="'guardians['+index+'][first_name]'" x-model="guardian.first_name" :required="index===0"
                                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 font-semibold text-slate-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Father Name <span x-show="index===0" class="text-rose-500">*</span></label>
                                    <input type="text" :name="'guardians['+index+'][father_name]'" x-model="guardian.father_name" :required="index===0"
                                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 font-semibold text-slate-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Grandfather Name <span x-show="index===0" class="text-rose-500">*</span></label>
                                    <input type="text" :name="'guardians['+index+'][grandfather_name]'" x-model="guardian.grandfather_name" :required="index===0"
                                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 font-semibold text-slate-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Relationship <span x-show="index===0" class="text-rose-500">*</span></label>
                                    <select :name="'guardians['+index+'][relationship]'" x-model="guardian.relationship" :required="index===0"
                                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl appearance-none font-semibold text-slate-700">
                                        <option value="">Select...</option>
                                        <option value="Father">Father</option>
                                        <option value="Mother">Mother</option>
                                        <option value="Grandparent">Grandparent</option>
                                        <option value="Uncle">Uncle</option>
                                        <option value="Aunt">Aunt</option>
                                        <option value="Brother">Brother</option>
                                        <option value="Sister">Sister</option>
                                        <option value="Guardian">Legal Guardian</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Phone Number <span x-show="index===0" class="text-rose-500">*</span></label>
                                    <input type="text" :name="'guardians['+index+'][phone]'" x-model="guardian.phone" :required="index===0"
                                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-black text-indigo-600 tracking-wider">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Email</label>
                                    <input type="email" :name="'guardians['+index+'][email]'" x-model="guardian.email"
                                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-600">
                                </div>
                                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                                    <div class="flex items-center h-full pt-6">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" :name="'guardians['+index+'][is_emergency_contact]'" value="1" x-model="guardian.is_emergency" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500 tracking-widest text-[8px] font-black text-white pl-2 leading-6"></div>
                                            <span class="ml-3 text-xs font-bold text-slate-600 uppercase tracking-widest">Emergency Contact</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Comm. Prefs</label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                                                <input type="checkbox" :name="'guardians['+index+'][communication_preferences][]'" value="sms" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500/20">
                                                <span class="text-[10px] font-black uppercase text-slate-600">SMS</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                                                <input type="checkbox" :name="'guardians['+index+'][communication_preferences][]'" value="email" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500/20">
                                                <span class="text-[10px] font-black uppercase text-slate-600">Email</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Section 5: Medical Information -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-sm border border-rose-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86 1.406l-.443.354a2 2 0 01-2.532 0l-.443-.354a6 6 0 00-3.86-1.406l-2.387.477a2 2 0 00-1.022.547m0 0l2.146 2.146a2 2 0 002.828 0l.547-.547M18 10V4a2 2 0 00-2-2H8a2 2 0 00-2 2v6m12 0a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 012-2m12 0h-2M6 10H4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Medical Information</h3>
                            <p class="text-slate-500 text-sm">Vital health details to ensure student safety.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="blood_group" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Blood Group</label>
                            <select name="blood_group" id="blood_group" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-bold text-rose-600 appearance-none">
                                <option value="">Unknown</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                    <option value="{{ $bg }}" {{ old('blood_group') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label for="emergency_contact" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Back-up Emergency Contact</label>
                            <input type="text" name="emergency_contact" id="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="Outside of parents/guardians"
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div class="md:col-span-2">
                            <label for="allergies" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 text-rose-600">Allergies</label>
                            <textarea name="allergies" id="allergies" rows="2" class="w-full px-4 py-3 bg-rose-50/20 border border-rose-100 rounded-2xl focus:ring-4 focus:ring-rose-500/10 focus:border-rose-400 font-semibold text-slate-700">{{ old('allergies') }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label for="medical_issues" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Chronic Conditions</label>
                            <textarea name="medical_issues" id="medical_issues" rows="2" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl font-semibold text-slate-700">{{ old('medical_issues') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 6: Transportation -->
            <div x-data="{ hasTransport: {{ old('driver_id') ? 'true' : 'false' }} }">
                <div class="mb-4">
                    <label class="inline-flex items-center cursor-pointer bg-slate-800 text-white px-6 py-3 rounded-2xl shadow-lg border-2 border-slate-700 hover:bg-slate-700 transition-all">
                        <input type="checkbox" x-model="hasTransport" class="w-5 h-5 rounded border-slate-500 text-indigo-500 focus:ring-indigo-500 mr-3">
                        <span class="text-sm font-bold uppercase tracking-widest">Enabling Transportation Details</span>
                    </label>
                </div>
                
                <div x-show="hasTransport" x-collapse x-cloak class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden mb-8">
                    <div class="p-8">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600 shadow-sm border border-slate-100/50">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Transportation Details</h3>
                                <p class="text-slate-500 text-sm">Assign a driver and vehicle for the student.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label for="driver_first_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Driver First Name</label>
                                <input type="text" name="driver_first_name" id="driver_first_name" value="{{ old('driver_first_name') }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                            </div>
                            <div>
                                <label for="driver_father_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Driver Middle Name</label>
                                <input type="text" name="driver_father_name" id="driver_father_name" value="{{ old('driver_father_name') }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                            </div>
                            <div>
                                <label for="driver_id" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Driver ID</label>
                                <input type="text" name="driver_id" id="driver_id" value="{{ old('driver_id') }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-bold text-slate-900 tracking-tighter">
                            </div>
                            <div>
                                <label for="vehicle_plate" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Vehicle Plate</label>
                                <input type="text" name="vehicle_plate" id="vehicle_plate" value="{{ old('vehicle_plate') }}" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-black text-rose-600">
                            </div>
                            <div class="md:col-span-4">
                                <label for="route" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Pickup/Drop Route</label>
                                <input type="text" name="route" id="route" value="{{ old('route') }}" placeholder="e.g. Route A - Bole Area" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pb-12">
                <a href="{{ route('admin.students.index') }}" class="px-8 py-4 bg-white border-2 border-slate-200 text-slate-500 text-sm font-bold rounded-2xl hover:bg-slate-50 transition-all">
                    Cancel Registration
                </a>
                <button type="submit" class="px-10 py-4 bg-slate-900 text-white text-sm font-bold rounded-2xl shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all">
                    Confirm & Register Student
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
