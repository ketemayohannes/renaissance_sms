<x-admin-layout title="Parent Profile - {{ $guardian->full_name }}">
    <div class="space-y-8">
        <!-- Breadcrumbs & Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.guardians.index') }}" class="h-12 w-12 bg-white rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:shadow-lg transition-all border border-slate-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Parent Profile</h1>
                    <p class="text-slate-500 mt-1">Detailed information and linked students</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.guardians.destroy', $guardian) }}" method="POST" class="confirm-form" data-confirm-message="Are you sure you want to delete this parent record? Any linked student info will remain but the link will be modified." data-confirm-title="Delete Parent" data-confirm-type="danger" data-confirm-button="Delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-3 bg-rose-50 text-rose-600 font-bold rounded-2xl border border-rose-100 transition-all shadow-sm flex items-center gap-2 hover:bg-rose-100">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        <span>Delete Record</span>
                    </button>
                </form>
                <a href="{{ route('admin.guardians.edit', $guardian) }}" class="px-6 py-3 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-2xl border border-slate-200 transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit Profile</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Primary Info -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Profile Card -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white shadow-xl shadow-slate-200/50">
                    <div class="flex flex-col items-center text-center">
                        <div class="relative group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-[2.5rem] blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative h-32 w-32 rounded-[2.5rem] overflow-hidden bg-slate-100 border-4 border-white shadow-inner">
                                @if($guardian->photo)
                                    <img src="{{ Storage::url($guardian->photo) }}" alt="Guardian photo" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full flex items-center justify-center text-slate-300 font-black text-4xl">
                                        {{ substr($guardian->first_name, 0, 1) }}{{ substr($guardian->father_name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <h2 class="mt-6 text-2xl font-black text-slate-800 italic tracking-tighter">{{ $guardian->full_name }}</h2>
                        <span class="mt-2 inline-flex px-4 py-1.5 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest">{{ $guardian->relationship }}</span>

                        <div class="w-full h-px bg-slate-100 my-8"></div>

                        <div class="w-full space-y-4">
                            <div class="flex items-center justify-between text-left">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Phone</span>
                                <span class="text-sm font-bold text-slate-700">{{ $guardian->phone }}</span>
                            </div>
                            <div class="flex items-center justify-between text-left">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Email</span>
                                <span class="text-sm font-bold text-slate-700">{{ $guardian->email ?? 'Not provided' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-left">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Emergency</span>
                                @if($guardian->is_emergency_contact)
                                    <span class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded font-black text-[10px] uppercase">Yes</span>
                                @else
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-400 rounded font-black text-[10px] uppercase">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Access Status Card -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white shadow-xl shadow-slate-200/50">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Portal Access</h3>
                    @if($guardian->user_id)
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-100">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040 L3 4v6.17 a11.955 11.955 0 005.474 9.42 l3.526 2.04 a11.955 11.955 0 0011-8.62 l-.382-7.05z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-slate-700">Account Active</div>
                                <div class="text-xs text-slate-400">Can access parent portal</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Username / Email</div>
                                <div class="text-sm font-bold text-slate-700">{{ $guardian->user->email ?? 'N/A' }}</div>
                            </div>
                            @if($guardian->user && $guardian->user->temp_password)
                            <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                                <div class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-1">Initial Password</div>
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-bold text-slate-700">{{ $guardian->user->temp_password }}</div>
                                    <button onclick="navigator.clipboard.writeText('{{ $guardian->user->temp_password }}')" class="p-1 px-2 bg-indigo-100 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-tighter transition-all">Copy</button>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="mt-6">
                            <form action="{{ route('admin.guardians.reset-password', $guardian) }}" method="POST" class="confirm-form" data-confirm-title="Reset Password" data-confirm-message="Are you sure you want to reset this parent's password?">
                                @csrf
                                <button type="submit" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1111 4.582V7m0 10a5 5 0 01-5-5h10a5 5 0 01-5 5z" />
                                    </svg>
                                    Reset Password
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10.5 7h3V5.5a1.5 1.5 0 00-3 0V7z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-slate-700">Account Disabled</div>
                                <div class="text-xs text-slate-400 tracking-tight">Access restricted for this parent</div>
                            </div>
                        </div>
                        <form action="{{ route('admin.guardians.create-account', $guardian) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Initial Password</label>
                                <input type="text" name="password" placeholder="guardian123" class="w-full px-4 py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700">
                                <p class="text-[9px] text-slate-400 italic">Default: guardian123</p>
                            </div>
                            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-indigo-200">
                                Enable Portal Access
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Right Column: Details & Linked Students -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Address & Information -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-10 border border-white shadow-xl shadow-slate-200/50">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-8 flex items-center gap-3">
                        <span class="h-6 w-1 bg-indigo-500 rounded-full"></span>
                        Guardian Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Address</span>
                            <div class="mt-2 text-slate-700 font-bold bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                                {{ $guardian->address ?? 'No address provided' }}
                            </div>
                        </div>
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Communication Preferences</span>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @forelse($guardian->communication_preferences ?? [] as $pref)
                                    <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-[10px] font-black uppercase border border-amber-100">{{ $pref }}</span>
                                @empty
                                    <span class="text-sm font-bold text-slate-400 italic">No preferences set</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Linked Students -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-10 border border-white shadow-xl shadow-slate-200/50">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-8 flex items-center gap-3">
                        <span class="h-6 w-1 bg-emerald-500 rounded-full"></span>
                        Associated Students
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Primary Child -->
                        <a href="{{ route('admin.students.show', $guardian->student) }}" class="group/card relative bg-white border border-slate-100 p-6 rounded-3xl hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="h-16 w-16 rounded-2xl overflow-hidden bg-slate-50 border-2 border-white shadow-sm flex-shrink-0">
                                    @if($guardian->student->photo)
                                        <img src="{{ Storage::url($guardian->student->photo) }}" alt="Student photo" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center text-slate-300 font-black text-xl">
                                            {{ substr($guardian->student->first_name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-black text-slate-800 tracking-tight group-hover/card:text-indigo-600 transition-colors">{{ $guardian->student->full_name }}</div>
                                    <div class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.15em] mt-1">
                                        {{ $guardian->student->student_id }}
                                    </div>
                                    <div class="text-xs text-slate-400 mt-1">
                                        {{ $guardian->student->enrollments->first()->section->gradeLevel->name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div class="absolute top-4 right-4 h-8 w-8 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 group-hover/card:bg-indigo-50 group-hover/card:text-indigo-600 transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </div>
                        </a>

                        <!-- Other Siblings (Matching by phone/email) -->
                        @foreach($otherStudents as $sibling)
                            <a href="{{ route('admin.students.show', $sibling) }}" class="group/card relative bg-slate-50/50 border border-slate-100 p-6 rounded-3xl hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border-dashed">
                                <div class="flex items-center gap-4">
                                    <div class="h-16 w-16 rounded-2xl overflow-hidden bg-white border-2 border-white shadow-sm flex-shrink-0">
                                        @if($sibling->photo)
                                            <img src="{{ Storage::url($sibling->photo) }}" alt="Sibling photo" class="h-full w-full object-cover">
                                        @else
                                            <div class="h-full w-full flex items-center justify-center text-slate-300 font-black text-xl">
                                                {{ substr($sibling->first_name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-800 tracking-tight group-hover/card:text-indigo-600 transition-colors">{{ $sibling->full_name }}</div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mt-1">
                                            {{ $sibling->student_id }}
                                        </div>
                                        <div class="text-xs text-slate-400 mt-1">
                                            {{ $sibling->enrollments->first()->section->gradeLevel->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="absolute top-4 right-4 h-8 w-8 bg-white rounded-xl flex items-center justify-center text-slate-300 group-hover/card:bg-indigo-50 group-hover/card:text-indigo-600 transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                                <div class="absolute -top-2 -left-2 px-2 py-0.5 bg-indigo-600 text-white rounded-lg text-[8px] font-black uppercase tracking-widest shadow-md">
                                    Implicit Link
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
