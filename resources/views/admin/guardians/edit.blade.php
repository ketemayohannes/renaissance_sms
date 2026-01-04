<x-admin-layout title="Edit Parent - {{ $guardian->full_name }}">
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.guardians.show', $guardian) }}" class="h-12 w-12 bg-white rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-all border border-slate-100 shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Edit Parent</h1>
                <p class="text-slate-500 mt-1">Update information for {{ $guardian->full_name }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl text-sm font-bold flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>There were errors with your submission. Please check the form below.</span>
            </div>
        @endif

        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <form action="{{ route('admin.guardians.update', $guardian) }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-10">
                @csrf
                @method('PUT')

                <!-- Personal Info Section -->
                <section class="space-y-6">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                        <span class="h-1 w-1 rounded-full bg-indigo-500"></span>
                        Personal Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $guardian->first_name) }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-700 placeholder-slate-300">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Father Name</label>
                            <input type="text" name="father_name" value="{{ old('father_name', $guardian->father_name) }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-700 placeholder-slate-300">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Grandfather Name</label>
                            <input type="text" name="grandfather_name" value="{{ old('grandfather_name', $guardian->grandfather_name) }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-700 placeholder-slate-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Relationship to Student</label>
                            <select name="relationship" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-700 appearance-none">
                                <option value="Father" {{ old('relationship', $guardian->relationship) == 'Father' ? 'selected' : '' }}>Father</option>
                                <option value="Mother" {{ old('relationship', $guardian->relationship) == 'Mother' ? 'selected' : '' }}>Mother</option>
                                <option value="Guardian" {{ old('relationship', $guardian->relationship) == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                                <option value="Uncle" {{ old('relationship', $guardian->relationship) == 'Uncle' ? 'selected' : '' }}>Uncle</option>
                                <option value="Aunt" {{ old('relationship', $guardian->relationship) == 'Aunt' ? 'selected' : '' }}>Aunt</option>
                                <option value="Other" {{ old('relationship', $guardian->relationship) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Profile Photo</label>
                            <input type="file" name="photo" class="w-full px-5 py-2.5 bg-slate-50 border-none rounded-2xl file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-all">
                        </div>
                    </div>
                </section>

                <div class="h-px bg-slate-50"></div>

                <!-- Contact Info Section -->
                <section class="space-y-6">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                        <span class="h-1 w-1 rounded-full bg-emerald-500"></span>
                        Contact & Address
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $guardian->phone) }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-700 placeholder-slate-300">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $guardian->email) }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-700 placeholder-slate-300">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">Full Address</label>
                        <textarea name="address" rows="3" 
                            class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-700 placeholder-slate-300">{{ old('address', $guardian->address) }}</textarea>
                    </div>

                    <div class="flex items-center gap-4 p-5 bg-rose-50/50 rounded-2xl border border-rose-100/50">
                        <input type="checkbox" name="is_emergency_contact" value="1" id="emergency_check" 
                            {{ old('is_emergency_contact', $guardian->is_emergency_contact) ? 'checked' : '' }}
                            class="h-5 w-5 rounded-lg border-rose-200 text-rose-600 focus:ring-rose-500 transition-all">
                        <label for="emergency_check" class="text-sm font-bold text-rose-700 cursor-pointer">Set as primary emergency contact</label>
                    </div>
                </section>

                <!-- Form Actions -->
                <div class="pt-6 flex items-center justify-end gap-4">
                    <a href="{{ route('admin.guardians.show', $guardian) }}" class="px-8 py-4 text-slate-500 font-bold hover:text-slate-800 transition-all">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl transition-all shadow-xl shadow-indigo-200 uppercase tracking-widest text-xs">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
