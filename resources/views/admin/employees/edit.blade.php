<x-admin-layout>
    <div class="space-y-8 pb-12" x-data="{ staffCategory: '{{ old('staff_category', $employee->staff_category ?? 'administrative') }}' }">
        <!-- Modern Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.employees.show', $employee) }}" class="w-14 h-14 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-slate-50 transition-all shadow-xl active:scale-90">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <x-breadcrumb :items="[
                        ['label' => 'Staff Management', 'url' => route('admin.employees.index')],
                        ['label' => 'Modify Registry', 'url' => '#']
                    ]" />
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                        <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                        Reconfigure Entity
                    </h1>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="space-y-12">
            @csrf
            @method('PUT')

            <!-- Entity Identity Matrix -->
            <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl p-10 relative overflow-hidden group">
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-50/50 rounded-full blur-3xl transition-transform group-hover:scale-110"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-10 border-b border-slate-50 pb-8">
                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-indigo-200">
                             <span class="text-lg font-black italic">01</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Biometric Correction</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Modify foundational demographics for {{ $employee->employee_id }}设计</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        <!-- Identity Visual Upload -->
                        <div class="lg:col-span-3">
                            <div x-data="{ photoPreview: '{{ $employee->photo ? Storage::url($employee->photo) : null }}' }" class="relative group/photo">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block text-center">Entity Visualization</span>
                                <div class="w-full aspect-square rounded-[2.5rem] bg-slate-50 border-4 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition-all group-hover/photo:border-indigo-400 shadow-inner group/preview">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="w-full h-full object-cover shadow-2xl">
                                    </template>
                                    <template x-if="!photoPreview">
                                        <div class="text-center p-6">
                                            <svg class="w-16 h-16 text-slate-200 mb-4 mx-auto group-hover/preview:scale-110 group-hover/preview:text-indigo-200 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-relaxed">System requires valid biometric visual</p>
                                        </div>
                                    </template>
                                </div>
                                <input type="file" name="photo" id="photo" class="hidden" 
                                       @change="photoPreview = URL.createObjectURL($event.target.files[0])">
                                <label for="photo" class="absolute inset-0 flex items-center justify-center bg-slate-900/60 text-white rounded-[2.5rem] opacity-0 group-hover/photo:opacity-100 transition-all cursor-pointer backdrop-blur-sm mt-8">
                                    <span class="text-[10px] font-black uppercase tracking-widest bg-white/20 px-4 py-2 rounded-full border border-white/30 text-center">REPLACE ASSET</span>
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
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Paternal Hierarchy <span class="text-rose-500">*</span></label>
                                <input type="text" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}" required
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase tracking-tight italic">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ancestral Terminal <span class="text-rose-500">*</span></label>
                                <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase tracking-tight italic">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Biological Classification <span class="text-rose-500">*</span></label>
                                <select name="gender" required class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black appearance-none shadow-inner">
                                    <option value="M" {{ old('gender', $employee->gender) == 'M' ? 'selected' : '' }}>MALE OPERATOR</option>
                                    <option value="F" {{ old('gender', $employee->gender) == 'F' ? 'selected' : '' }}>FEMALE OPERATOR</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Temporal Origin (DOB) <span class="text-rose-500">*</span></label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth->format('Y-m-d')) }}" required
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Social Union Stand</label>
                                <select name="marital_status" class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black appearance-none shadow-inner">
                                    <option value="">SELECT STATUS</option>
                                    <option value="single" {{ old('marital_status', $employee->marital_status) == 'single' ? 'selected' : '' }}>SINGLE</option>
                                    <option value="married" {{ old('marital_status', $employee->marital_status) == 'married' ? 'selected' : '' }}>MARRIED</option>
                                    <option value="divorced" {{ old('marital_status', $employee->marital_status) == 'divorced' ? 'selected' : '' }}>DIVORCED</option>
                                    <option value="widowed" {{ old('marital_status', $employee->marital_status) == 'widowed' ? 'selected' : '' }}>WIDOWED</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Governance Registry (ID)</label>
                                <input type="text" name="national_id" value="{{ old('national_id', $employee->national_id) }}"
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase italic" placeholder="ET-000-000">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Taxation Node (TIN)</label>
                                <input type="text" name="tin" value="{{ old('tin', $employee->tin) }}"
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase italic" placeholder="TIN-REG-000">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Retirement Ledger No.</label>
                                <input type="text" name="pension_number" value="{{ old('pension_number', $employee->pension_number) }}"
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-black shadow-inner uppercase italic" placeholder="PEN-INIT-00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Communication & GIS Nodes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl p-10 group overflow-hidden">
                    <div class="flex items-center gap-4 mb-10 border-b border-slate-50 pb-8">
                        <div class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-emerald-200">
                             <span class="text-lg font-black italic">02</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Comms Protocol</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Primary and emergency connectivity channels设计</p>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Digital Mail (Immutable) <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $employee->email) }}" required readonly
                                   class="w-full bg-slate-100 border-transparent rounded-[1.4rem] py-4 px-6 focus:ring-emerald-600 focus:border-emerald-600 text-sm font-black shadow-inner lowercase italic text-slate-400 cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Terminal Link (Phone) <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" required
                                   class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-emerald-600 focus:border-emerald-600 text-sm font-black shadow-inner">
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Emergency POC</label>
                                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}"
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-emerald-600 focus:border-emerald-600 text-sm font-bold shadow-inner uppercase italic">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">POC Terminal</label>
                                <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}"
                                       class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-emerald-600 focus:border-emerald-600 text-sm font-bold shadow-inner">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl p-10 group overflow-hidden">
                    <div class="flex items-center gap-4 mb-10 border-b border-slate-50 pb-8">
                        <div class="w-12 h-12 bg-rose-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-rose-200">
                             <span class="text-lg font-black italic">03</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Geospatial Registry</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Operational location and residency metadata设计</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Administrative Region</label>
                            <input type="text" name="region" value="{{ old('region', $employee->region) }}"
                                   class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-rose-600 focus:border-rose-600 text-sm font-black shadow-inner uppercase italic">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Operational Zone</label>
                            <input type="text" name="zone" value="{{ old('zone', $employee->zone) }}"
                                   class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-rose-600 focus:border-rose-600 text-sm font-black shadow-inner uppercase italic">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Woreda / Sector</label>
                            <input type="text" name="woreda" value="{{ old('woreda', $employee->woreda) }}"
                                   class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-rose-600 focus:border-rose-600 text-sm font-black shadow-inner uppercase italic">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Registry Address</label>
                            <input type="text" name="address" value="{{ old('address', $employee->address) }}"
                                   class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-rose-600 focus:border-rose-600 text-sm font-bold shadow-inner italic">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment & Asset Config -->
            <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl p-10 relative group">
                <div class="flex items-center gap-4 mb-10 border-b border-slate-50 pb-8">
                    <div class="w-12 h-12 bg-amber-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-amber-200">
                         <span class="text-lg font-black italic">04</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Operational Parameters</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Contract details and financial asset routing设计</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Staff Division <span class="text-rose-500">*</span></label>
                        <select name="staff_category" x-model="staffCategory" required 
                                class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-amber-600 focus:border-amber-600 text-sm font-black appearance-none shadow-inner">
                            <option value="administrative" {{ old('staff_category', $employee->staff_category) == 'administrative' ? 'selected' : '' }}>ADMINISTRATIVE</option>
                            <option value="academic" {{ old('staff_category', $employee->staff_category) == 'academic' ? 'selected' : '' }}>ACADEMIC (FACULTY)</option>
                            <option value="support" {{ old('staff_category', $employee->staff_category) == 'support' ? 'selected' : '' }}>SUPPORT OPERATIONS</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Operational Role <span class="text-rose-500">*</span></label>
                        <input type="text" name="designation" value="{{ old('designation', $employee->designation) }}" required
                               class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-amber-600 focus:border-amber-600 text-sm font-black shadow-inner uppercase italic">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Division Assignment</label>
                        <select name="department" class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-amber-600 focus:border-amber-600 text-sm font-black appearance-none shadow-inner">
                            <option value="Academic" {{ old('department', $employee->department) == 'Academic' ? 'selected' : '' }}>ACADEMIC AFFAIRS</option>
                            <option value="Administration" {{ old('department', $employee->department) == 'Administration' ? 'selected' : '' }}>INSTITUTIONAL ADMIN</option>
                            <option value="Finance" {{ old('department', $employee->department) == 'Finance' ? 'selected' : '' }}>FINANCIAL OPS</option>
                            <option value="IT" {{ old('department', $employee->department) == 'IT' ? 'selected' : '' }}>INFRASTRUCTURE / IT</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Contract Type <span class="text-rose-500">*</span></label>
                        <select name="employment_type" required class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-amber-600 focus:border-amber-600 text-sm font-black appearance-none shadow-inner">
                            <option value="full_time" {{ old('employment_type', $employee->employment_type) == 'full_time' ? 'selected' : '' }}>FULL TIME CONTRACT</option>
                            <option value="part_time" {{ old('employment_type', $employee->employment_type) == 'part_time' ? 'selected' : '' }}>PART TIME / TACTICAL</option>
                            <option value="contract" {{ old('employment_type', $employee->employment_type) == 'contract' ? 'selected' : '' }}>SPECIFIC CONTRACT</option>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Registry Commencement <span class="text-rose-500">*</span></label>
                        <input type="date" name="joining_date" value="{{ old('joining_date', $employee->joining_date->format('Y-m-d')) }}" required
                               class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-amber-600 focus:border-amber-600 text-sm font-black shadow-inner">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Basic Asset Allocation (Salary) <span class="text-rose-500">*</span></label>
                        <div class="relative group">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-black">ETB</span>
                            <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" required
                                   class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] pl-16 py-4 px-6 focus:ring-amber-600 focus:border-amber-600 text-sm font-black shadow-inner">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Asset Routing Bank</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}"
                               class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-amber-600 focus:border-amber-600 text-sm font-bold shadow-inner">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Asset Node (Account)</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $employee->account_number) }}"
                               class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-amber-600 focus:border-amber-600 text-sm font-black shadow-inner">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Operational State <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full bg-slate-50 border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-amber-600 focus:border-amber-600 text-sm font-black appearance-none shadow-inner">
                            <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>OPERATIONAL / ACTIVE</option>
                            <option value="on_leave" {{ old('status', $employee->status) == 'on_leave' ? 'selected' : '' }}>ON SABBATICAL / LEAVE</option>
                            <option value="resigned" {{ old('status', $employee->status) == 'resigned' ? 'selected' : '' }}>RESIGNED / EXIT</option>
                            <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>DECOMMISSIONED</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Specialized Faculty Integration (Conditional) -->
            <div x-show="staffCategory === 'academic'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-indigo-900 rounded-[3rem] shadow-2xl p-10 relative overflow-hidden group">
                <div class="absolute -top-12 -right-12 w-64 h-64 bg-white/10 rounded-full blur-3xl transition-transform group-hover:scale-125"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-10 border-b border-white/10 pb-8">
                        <div class="w-12 h-12 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center text-white border border-white/20">
                             <span class="text-lg font-black italic">05</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white uppercase tracking-tight italic">Academic Competency</h3>
                            <p class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest mt-1 font-medium">Faculty credentials and strategic workload matrix设计</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest ml-1">Faculty Rank</label>
                            <input type="text" name="teacher_rank" value="{{ old('teacher_rank', $employee->teacher_rank) }}"
                                   class="w-full bg-white/5 border-white/10 rounded-[1.4rem] py-4 px-6 focus:ring-white focus:border-white text-sm font-black text-white shadow-xl placeholder:text-white/20 uppercase italic">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest ml-1">Credential Tier</label>
                            <select name="qualification_level" class="w-full bg-white/5 border-white/10 rounded-[1.4rem] py-4 px-6 focus:ring-white focus:border-white text-sm font-black text-white appearance-none shadow-xl">
                                <option value="" class="bg-indigo-950">SELECT TIER</option>
                                <option value="Diploma" {{ old('qualification_level', $employee->qualification_level) == 'Diploma' ? 'selected' : '' }}>DIPLOMA LEVEL</option>
                                <option value="Degree" {{ old('qualification_level', $employee->qualification_level) == 'Degree' ? 'selected' : '' }}>BACHELOR DEGREE</option>
                                <option value="Masters" {{ old('qualification_level', $employee->qualification_level) == 'Masters' ? 'selected' : '' }}>MASTER OF SCIENCE/ARTS</option>
                                <option value="PhD" {{ old('qualification_level', $employee->qualification_level) == 'PhD' ? 'selected' : '' }}>DOCTORATE / PhD</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest ml-1">Subject Major</label>
                            <input type="text" name="specialization" value="{{ old('specialization', $employee->specialization) }}"
                                   class="w-full bg-white/5 border-white/10 rounded-[1.4rem] py-4 px-6 focus:ring-white focus:border-white text-sm font-black text-white shadow-xl placeholder:text-white/20 uppercase italic">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest ml-1">Periods / Cycle</label>
                            <input type="number" name="periods_per_week" value="{{ old('periods_per_week', $employee->periods_per_week) }}"
                                   class="w-full bg-white/5 border-white/10 rounded-[1.4rem] py-4 px-6 focus:ring-white focus:border-white text-sm font-black text-white shadow-xl">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Terminal Operations -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-8 pt-10">
                <div class="flex items-center gap-6 bg-slate-50/50 p-4 pr-10 rounded-[2rem] border border-slate-100">
                    <div class="w-16 h-16 rounded-[1.5rem] bg-indigo-600 flex items-center justify-center text-white shadow-2xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Configuration Lock</p>
                        <p class="text-sm font-bold text-slate-600 italic">Updating this entity will recalibrate payroll and workload and sync with audit logs.</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 w-full md:w-auto">
                    <a href="{{ route('admin.employees.show', $employee) }}" class="flex-1 md:flex-none py-5 px-10 bg-white border border-slate-200 text-slate-400 font-black text-[10px] uppercase tracking-widest rounded-3xl hover:bg-rose-50 hover:text-rose-600 transition-all active:scale-95 text-center">
                        Abort Changes
                    </a>
                    <button type="submit" class="flex-1 md:flex-none py-5 px-16 bg-slate-900 text-white font-black text-[10px] uppercase tracking-[0.2em] rounded-3xl hover:bg-indigo-600 shadow-2xl shadow-indigo-200 transition-all active:scale-95 flex items-center justify-center gap-4 group">
                        <svg class="w-4 h-4 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        Push Modifications
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
