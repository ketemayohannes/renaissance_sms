<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Modify Staff Registry</h2>
                <p class="text-slate-500 text-sm mt-1">Update profile and records for {{ $employee->full_name }}.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.employees.show', $employee) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2 shadow-sm border-b-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 8 9 0 0118 0z"></path></svg>
                    Back to Profile
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6" 
         x-data="{ 
            staffCategory: '{{ old('staff_category', $employee->user->roles->first()->category ?? 'administrative') }}',
            rolesByCategory: {{ json_encode($rolesByCategory) }},
            assignments: {{ json_encode(old('assignments', $employee->user->teacherAssignments->map(function($a) {
                return ['section_id' => $a->section_id, 'subject_id' => $a->subject_id];
            }))) }},
            get currentRoles() {
                return this.staffCategory ? this.rolesByCategory[this.staffCategory] : [];
            }
         }">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Staff Management', 'url' => route('admin.employees.index')],
            ['label' => 'Edit Employee', 'url' => '#']
        ]" />

        <form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="space-y-12">
            @csrf
            @method('PUT')
            <input type="hidden" name="staff_category" :value="staffCategory">

            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-100 rounded-[2rem] p-6 mb-8 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 animate-pulse">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-rose-900 font-bold text-sm">Please Fix the Following Errors</h4>
                        <ul class="mt-2 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-rose-600 text-xs font-semibold flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-300"></span>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Section 1: Personal Information -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Personal Information</h3>
                            <p class="text-slate-500 text-sm">Update identity records for {{ $employee->employee_id }}.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        <!-- Identity Visual Upload -->
                        <div class="lg:col-span-3">
                            <div x-data="{ photoPreview: '{{ $employee->photo ? Storage::url($employee->photo) : null }}' }" class="relative group/photo">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block text-center">Profile Photo</span>
                                <div class="w-full aspect-square rounded-[2.5rem] bg-slate-50 border-4 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition-all group-hover/photo:border-indigo-400 shadow-inner group/preview">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" alt="Photo preview" class="w-full h-full object-cover shadow-2xl">
                                    </template>
                                    <template x-if="!photoPreview">
                                        <div class="text-center p-6">
                                            <svg class="w-16 h-16 text-slate-200 mb-4 mx-auto group-hover/preview:scale-110 group-hover/preview:text-indigo-200 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-relaxed">Upload a profile photo</p>
                                        </div>
                                    </template>
                                </div>
                                <input type="file" name="photo" id="photo" class="hidden" 
                                       @change="photoPreview = URL.createObjectURL($event.target.files[0])">
                                <label for="photo" class="absolute inset-0 flex items-center justify-center bg-slate-900/60 text-white rounded-[2.5rem] opacity-0 group-hover/photo:opacity-100 transition-all cursor-pointer backdrop-blur-sm mt-8">
                                    <span class="text-[10px] font-black uppercase tracking-widest bg-white/20 px-4 py-2 rounded-full border border-white/30 text-center">CHANGE PHOTO</span>
                                </label>
                            </div>
                        </div>

                        <!-- Biographical Core -->
                        <div class="lg:col-span-9 grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Legal First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase tracking-tight italic">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Father's Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}" required
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase tracking-tight italic">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Last Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase tracking-tight italic">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Gender <span class="text-rose-500">*</span></label>
                                <select name="gender" required class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black appearance-none shadow-inner">
                                    <option value="M" {{ old('gender', $employee->gender) == 'M' ? 'selected' : '' }}>Male</option>
                                    <option value="F" {{ old('gender', $employee->gender) == 'F' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Date of Birth <span class="text-rose-500">*</span></label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth->format('Y-m-d')) }}" required
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Marital Status</label>
                                <select name="marital_status" class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black appearance-none shadow-inner">
                                    <option value="">SELECT STATUS</option>
                                    <option value="single" {{ old('marital_status', $employee->marital_status) == 'single' ? 'selected' : '' }}>SINGLE</option>
                                    <option value="married" {{ old('marital_status', $employee->marital_status) == 'married' ? 'selected' : '' }}>MARRIED</option>
                                    <option value="divorced" {{ old('marital_status', $employee->marital_status) == 'divorced' ? 'selected' : '' }}>DIVORCED</option>
                                    <option value="widowed" {{ old('marital_status', $employee->marital_status) == 'widowed' ? 'selected' : '' }}>WIDOWED</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">National ID</label>
                                <input type="text" name="national_id" value="{{ old('national_id', $employee->national_id) }}"
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase italic" placeholder="ET-000-000">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">TIN Number</label>
                                <input type="text" name="tin" value="{{ old('tin', $employee->tin) }}"
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase italic" placeholder="TIN-REG-000">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pension Number</label>
                                <input type="text" name="pension_number" value="{{ old('pension_number', $employee->pension_number) }}"
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase italic" placeholder="PEN-INIT-00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Contact Information -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Contact Details</h3>
                            <p class="text-slate-500 text-sm">Update communication and residential information.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-2">
                            <label for="email" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Email Address <span class="text-rose-500 text-[10px] italic">(Read-only)</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" required readonly
                                   class="w-full px-4 py-3 bg-slate-100 border-transparent rounded-xl font-semibold text-slate-400 cursor-not-allowed">
                        </div>
                        <div class="md:col-span-2">
                            <label for="phone" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Phone Number <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $employee->phone) }}" required 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div class="md:col-span-2">
                            <label for="emergency_contact_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div class="md:col-span-2">
                            <label for="emergency_contact_phone" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="region" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Region</label>
                            <input type="text" name="region" id="region" value="{{ old('region', $employee->region) }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="zone" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Zone</label>
                            <input type="text" name="zone" id="zone" value="{{ old('zone', $employee->zone) }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="woreda" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Woreda / Sector</label>
                            <input type="text" name="woreda" id="woreda" value="{{ old('woreda', $employee->woreda) }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="address" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Full Address</label>
                            <input type="text" name="address" id="address" value="{{ old('address', $employee->address) }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Professional Details -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm border border-amber-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Employment Details</h3>
                            <p class="text-slate-500 text-sm">Update role, department, and contract information.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-1">
                            <label for="role" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Professional Role <span class="text-rose-500">*</span></label>
                            <select name="role" id="role" required 
                                    class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl appearance-none font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                                <option value="">Select Role</option>
                                <template x-for="roleName in currentRoles" :key="roleName">
                                    <option :value="roleName" x-text="roleName" :selected="roleName === '{{ old('role', $employee->user->roles->first()->name ?? '') }}'"></option>
                                </template>
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label for="division_id" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">School Division</label>
                            <select name="division_id" id="division_id" 
                                    class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl appearance-none font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                                <option value="">Global / All Divisions</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id', $employee->division_id) == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[9px] text-slate-400 font-bold uppercase mt-1 ml-1">Required for Principal/Supervisor roles</p>
                        </div>
                        <div class="md:col-span-1">
                            <label for="department" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Department</label>
                            <input type="text" name="department" id="department" value="{{ old('department', $employee->department) }}" placeholder="e.g. Science, HR, Finance"
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="employment_type" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Contract Type <span class="text-rose-500">*</span></label>
                            <select name="employment_type" id="employment_type" required 
                                    class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl appearance-none font-semibold text-slate-700">
                                <option value="full_time" {{ old('employment_type', $employee->employment_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                <option value="part_time" {{ old('employment_type', $employee->employment_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="contract" {{ old('employment_type', $employee->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                            </select>
                        </div>
                        <div>
                            <label for="joining_date" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Joining Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="joining_date" id="joining_date" value="{{ old('joining_date', $employee->joining_date->format('Y-m-d')) }}" required 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="basic_salary" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Basic Salary (ETB) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="basic_salary" id="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" required 
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl font-black text-slate-800">
                        </div>
                        <div>
                            <label for="bank_name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="account_number" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Account Number</label>
                            <input type="text" name="account_number" id="account_number" value="{{ old('account_number', $employee->account_number) }}" 
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="status" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Employment State <span class="text-rose-500">*</span></label>
                            <select name="status" id="status" required 
                                    class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl appearance-none font-bold text-slate-700">
                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active / Operational</option>
                                <option value="on_leave" {{ old('status', $employee->status) == 'on_leave' ? 'selected' : '' }}>On Leave / Sabbatical</option>
                                <option value="resigned" {{ old('status', $employee->status) == 'resigned' ? 'selected' : '' }}>Resigned / Exit</option>
                                <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Educational Qualifications (Shared) -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Educational Qualifications</h3>
                            <p class="text-slate-500 text-sm">Update academic background and verified certifications.</p>
                        </div>
                    </div>

                    @php
                        $details = $employee->academicDetails ?? $employee->administrativeDetails;
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-2">
                            <label for="institution" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Last Institution Attended</label>
                            <input type="text" name="institution" id="institution" value="{{ old('institution', $details->institution ?? '') }}" placeholder="e.g. Addis Ababa University"
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div class="md:col-span-1">
                            <label for="graduation_year" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Graduation Year</label>
                            <input type="number" name="graduation_year" id="graduation_year" value="{{ old('graduation_year', $details->graduation_year ?? '') }}" placeholder="YYYY"
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div class="md:col-span-1">
                            <label for="last_degree" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Last Degree Attained</label>
                            <input type="text" name="last_degree" id="last_degree" value="{{ old('last_degree', $details->last_degree ?? '') }}" placeholder="e.g. BSc in Math"
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                        <div>
                            <label for="qualification_level" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Qualification Tier</label>
                            <select name="qualification_level" id="qualification_level" 
                                    class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl appearance-none font-semibold text-slate-700">
                                <option value="">Select Tier</option>
                                <option value="Diploma" {{ old('qualification_level', $details->qualification_level ?? '') == 'Diploma' ? 'selected' : '' }}>Diploma Level</option>
                                <option value="Degree" {{ old('qualification_level', $details->qualification_level ?? '') == 'Degree' ? 'selected' : '' }}>Bachelor Degree</option>
                                <option value="Masters" {{ old('qualification_level', $details->qualification_level ?? '') == 'Masters' ? 'selected' : '' }}>Master's Degree</option>
                                <option value="PhD" {{ old('qualification_level', $details->qualification_level ?? '') == 'PhD' ? 'selected' : '' }}>Doctorate / PhD</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label for="specialization" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Area of Specialization</label>
                            <input type="text" name="specialization" id="specialization" value="{{ old('specialization', $details->specialization ?? '') }}" placeholder="e.g. Applied Mathematics & Pedagogy"
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Digital Dossier (Documents) -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Digital Dossier</h3>
                            <p class="text-slate-500 text-sm">Manage essential documents and legal certifications.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        @foreach(['resume' => 'Curriculum Vitae', 'degree' => 'Educational Degree', 'certificate' => 'Professional Cert.', 'other' => 'Other Support Docs.'] as $type => $label)
                            <div x-data="{ fileName: '' }" class="relative">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">{{ $label }}</label>
                                
                                <div class="relative group">
                                    <input type="file" name="documents[{{ $type }}]" @change="fileName = $event.target.files[0].name" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div class="w-full p-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center gap-2 group-hover:border-indigo-400 transition-all min-h-[100px]">
                                        @php $doc = $employee->documents->where('type', $type)->first(); @endphp
                                        
                                        @if($doc)
                                            <div class="flex flex-col items-center gap-2">
                                                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest text-center truncate w-full px-2">{{ $doc->name }}</span>
                                                <div class="flex gap-2">
                                                    <a href="{{ route('admin.employees.documents.download', $doc) }}" class="p-1.5 bg-white rounded-lg border border-slate-200 text-slate-400 hover:text-indigo-600 shadow-sm z-20">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    </a>
                                                    <button type="button" @click.prevent="window.confirmUI({message: 'Delete document?', callback: () => window.location.href='{{ route('admin.employees.documents.delete', $doc) }}'})" class="p-1.5 bg-white rounded-lg border border-slate-200 text-slate-400 hover:text-rose-600 shadow-sm z-20">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <svg class="w-6 h-6 text-slate-300 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest text-center truncate w-full px-2" x-text="fileName || 'Click to Upload'"></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Section 6: Instructional Assignments (Teachers Only) -->
            <div x-show="staffCategory === 'academic'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Workload & Rank</h3>
                            <p class="text-slate-500 text-sm">Update teaching assignments and rank.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                        <div class="md:col-span-2">
                            <label for="secondary_responsibilities" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Secondary Responsibilities</label>
                            <textarea name="secondary_responsibilities" id="secondary_responsibilities" rows="2" placeholder="e.g. Club Coordinator, Department Head Proxy"
                                      class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">{{ old('secondary_responsibilities', $employee->academicDetails->secondary_responsibilities ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Instructional Assignment Matrix -->
                    <div class="mt-10 border-t border-slate-100 pt-8">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Section & Subject Assignments</h4>
                                <p class="text-[10px] text-slate-400 font-bold uppercase mt-1 tracking-tight">Map teacher to specific sections and subjects</p>
                            </div>
                            <button type="button" @click="assignments.push({section_id: '', subject_id: ''})" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-[10px] font-black rounded-xl hover:bg-indigo-700 transition-all gap-2 shadow-lg shadow-indigo-100 uppercase tracking-widest">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                Add Target
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(assignment, index) in assignments" :key="index">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 bg-slate-50/50 p-4 rounded-2xl border border-slate-100 items-end">
                                    <div class="md:col-span-5">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Target Section</label>
                                        <select :name="'assignments['+index+'][section_id]'" x-model="assignment.section_id" required
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 text-sm">
                                            <option value="">Select Section</option>
                                            @foreach($sections as $section)
                                                <option value="{{ $section->id }}">{{ $section->gradeLevel->name }} - {{ $section->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-5">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Target Subject</label>
                                        <select :name="'assignments['+index+'][subject_id]'" x-model="assignment.subject_id" required
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 text-sm">
                                            <option value="">Select Subject</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <button type="button" @click="assignments.splice(index, 1)" 
                                                class="w-full py-3 bg-white border border-rose-100 text-rose-500 rounded-xl hover:bg-rose-50 transition-all flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            
                            <template x-if="assignments.length === 0">
                                <div class="text-center py-10 bg-slate-50/50 rounded-3xl border-2 border-dashed border-slate-200">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">No Assignments Defined</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Administrative Credentials (Conditional) -->
            <div x-show="staffCategory === 'administrative'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Administrative Governance</h3>
                            <p class="text-slate-500 text-sm">Update system access levels and operational responsibilities.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <div>
                            <label for="system_access_roles" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">System Module Access / Roles</label>
                            <textarea name="system_access_roles" id="system_access_roles" rows="3" placeholder="e.g. Student Information System (Read/Write), Finance Module (Read Only)"
                                      class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl font-semibold text-slate-700">{{ old('system_access_roles', $employee->administrativeDetails->system_access_roles ?? '') }}</textarea>
                            <p class="text-[9px] text-slate-400 font-bold uppercase mt-2 ml-1">Describe specialized system permissions required beyond the base role.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pb-12">
                <a href="{{ route('admin.employees.show', $employee) }}" class="px-8 py-4 bg-white border-2 border-slate-200 text-slate-500 text-sm font-bold rounded-2xl hover:bg-slate-50 transition-all">
                    Cancel Edits
                </a>
                <button type="submit" class="px-10 py-4 bg-slate-900 text-white text-sm font-bold rounded-2xl shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all">
                    Update Staff Profile
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
