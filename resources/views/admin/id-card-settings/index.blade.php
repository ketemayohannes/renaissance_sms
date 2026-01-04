<x-admin-layout>
    <x-slot name="header">ID Card Settings</x-slot>

    <div class="py-6" x-data="idCardConfig()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Header section -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">ID Card Configuration</h1>
                    <p class="text-slate-500 font-medium">Customize the layout and content of student ID cards.</p>
                </div>
                <button type="submit" form="config-form" class="px-8 py-4 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 active:scale-95">
                    Save Configuration
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Configuration Form -->
                <div class="lg:col-span-1 space-y-6">
                    <form id="config-form" action="{{ route('admin.id-card-settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- School Identity -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                                School Identity
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">School Name</label>
                                    <input type="text" name="school_name" x-model="config.school_name" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200">
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">School Logo</label>
                                    <div class="mt-1 flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center border border-slate-100 shadow-sm overflow-hidden">
                                            <template x-if="config.logo_url">
                                                <img :src="config.logo_url" class="w-full h-full object-contain p-1">
                                            </template>
                                            <template x-if="!config.logo_url">
                                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </template>
                                        </div>
                                        <input type="file" name="logo" @change="handleLogoUpload" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition-all cursor-pointer">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visual Customization -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                                Visual Identity
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Primary Color (Background)</label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="primary_color" x-model="config.primary_color" class="w-12 h-12 rounded-xl border-0 p-1 bg-slate-100 cursor-pointer shadow-inner">
                                        <input type="text" x-model="config.primary_color" class="flex-1 bg-slate-50 border-slate-100 rounded-xl font-mono text-xs uppercase font-bold text-slate-600 focus:ring-slate-200">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Secondary Color (Gradient)</label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="secondary_color" x-model="config.secondary_color" class="w-12 h-12 rounded-xl border-0 p-1 bg-slate-100 cursor-pointer shadow-inner">
                                        <input type="text" x-model="config.secondary_color" class="flex-1 bg-slate-50 border-slate-100 rounded-xl font-mono text-xs uppercase font-bold text-slate-600 focus:ring-slate-200">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Text Color</label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="text_color" x-model="config.text_color" class="w-12 h-12 rounded-xl border-0 p-1 bg-slate-100 cursor-pointer shadow-inner">
                                        <input type="text" x-model="config.text_color" class="flex-1 bg-slate-50 border-slate-100 rounded-xl font-mono text-xs uppercase font-bold text-slate-600 focus:ring-slate-200">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Photo Shape</label>
                                    <select name="photo_shape" x-model="config.photo_shape" class="w-full bg-slate-50 border-slate-100 rounded-xl text-sm font-bold text-slate-600 focus:ring-slate-200">
                                        <option value="rounded">Rounded Rectangle</option>
                                        <option value="circle">Circular</option>
                                        <option value="square">Square</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Front Side Content -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-emerald-500 rounded-full"></span>
                                Front Side Fields
                            </h3>
                            
                            <div class="grid grid-cols-1 gap-2">
                                <template x-for="field in availableFields" :key="field.id">
                                    <label class="flex items-center p-3 rounded-2xl hover:bg-slate-50 transition-colors cursor-pointer group">
                                        <input type="checkbox" name="front_fields[]" :value="field.id" x-model="config.front_fields" class="w-5 h-5 rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500 transition-all">
                                        <span class="ml-3 text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors" x-text="field.label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Back Side Content -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-rose-500 rounded-full"></span>
                                Back Side Content
                            </h3>

                            <div class="space-y-4">
                                <template x-for="field in availableFields" :key="'back_'+field.id">
                                    <label class="flex items-center p-3 rounded-2xl hover:bg-slate-50 transition-colors cursor-pointer group">
                                        <input type="checkbox" name="back_fields[]" :value="field.id" x-model="config.back_fields" class="w-5 h-5 rounded-lg border-slate-200 text-rose-600 focus:ring-rose-500 transition-all">
                                        <span class="ml-3 text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors" x-text="field.label"></span>
                                    </label>
                                </template>

                                <div class="pt-4 border-t border-slate-100">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Custom Terms / Rules</label>
                                    <textarea name="back_content" x-model="config.back_content" rows="4" class="w-full bg-slate-50 border-slate-100 rounded-2xl text-xs font-semibold text-slate-600 focus:ring-slate-200 p-4" placeholder="Enter school rules or disclaimer..."></textarea>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50/50">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Show Barcode</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="show_barcode" x-model="config.show_barcode" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Preview Area -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="sticky top-24 space-y-8">
                        <div class="flex items-center gap-4 bg-slate-100/50 p-1.5 rounded-2xl w-fit mx-auto border border-slate-200">
                            <button @click="side = 'front'" :class="side === 'front' ? 'bg-white text-slate-900 shadow-md' : 'text-slate-500'" class="px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                                Front Side
                            </button>
                            <button @click="side = 'back'" :class="side === 'back' ? 'bg-white text-slate-900 shadow-md' : 'text-slate-500'" class="px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                                Back Side
                            </button>
                        </div>

                        <!-- ID Card Preview Container -->
                        <div class="flex justify-center items-center perspective-[1000px]">
                            <div class="relative w-[500px] h-[312.5px] rounded-[1.8rem] shadow-2xl shadow-slate-200/80 transition-all duration-700 preserve-3d border border-slate-100" :style="side === 'back' ? 'transform: rotateY(180deg)' : ''">
                                
                                <!-- FRONT SIDE -->
                                <div class="absolute inset-0 backface-hidden rounded-[1.8rem] overflow-hidden bg-white shadow-inner" 
                                     :style="`background: linear-gradient(135deg, ${config.primary_color}, ${config.secondary_color}); color: ${config.text_color}`">
                                    
                                    <div class="relative p-8 h-full flex flex-col">
                                        <!-- Card Header -->
                                        <div class="flex justify-between items-start mb-6 border-b border-black/5 pb-2">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-white shadow-sm rounded-xl flex items-center justify-center border border-slate-100 overflow-hidden">
                                                    <template x-if="config.logo_url">
                                                        <img :src="config.logo_url" class="w-full h-full object-contain p-1">
                                                    </template>
                                                    <template x-if="!config.logo_url">
                                                        <svg class="w-6 h-6 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71L12 2z"/></svg>
                                                    </template>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-black tracking-[0.2em] uppercase" x-text="config.school_name"></span>
                                                    <span class="text-[9px] font-bold tracking-widest opacity-50">STUDENT IDENTIFICATION</span>
                                                </div>
                                            </div>
                                            <div class="bg-slate-100/50 px-3 py-1 rounded-lg text-[10px] font-black tracking-widest uppercase" :style="`color: ${config.text_color}`">
                                                2024/2025
                                            </div>
                                        </div>

                                        <!-- Card Body -->
                                        <div class="flex gap-8 flex-1">
                                            <!-- Photo -->
                                            <div class="flex flex-col items-center gap-4">
                                                <div class="w-28 h-32 bg-slate-50 border border-slate-100 overflow-hidden shadow-sm" 
                                                     :class="config.photo_shape === 'circle' ? 'rounded-full' : (config.photo_shape === 'rounded' ? 'rounded-2xl' : 'rounded-none')">
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Details -->
                                            <div class="flex-1 space-y-3 pt-2">
                                                <div class="mb-4">
                                                    <div class="text-[9px] font-black opacity-40 uppercase tracking-widest">Official Record</div>
                                                    <div class="text-xl font-bold tracking-tight">Johnathan Doe</div>
                                                </div>

                                                <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                                                    <template x-for="field in config.front_fields" :key="'preview_'+field">
                                                        <div x-show="field !== 'full_name' && field !== 'student_id'">
                                                            <div class="text-[8px] font-black opacity-40 uppercase tracking-widest" x-text="getFieldLabel(field)"></div>
                                                            <div class="text-xs font-semibold" x-text="getFieldValue(field)"></div>
                                                        </div>
                                                    </template>
                                                    
                                                    <div v-show="config.front_fields.includes('student_id')">
                                                        <div class="text-[8px] font-black opacity-40 uppercase tracking-widest">ID Number</div>
                                                        <div class="text-xs font-mono font-bold tracking-widest">RSS-24-00154</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Card Footer -->
                                        <div class="mt-auto pt-4 border-t border-black/5 flex justify-between items-center opacity-60">
                                            <span class="text-[8px] font-medium tracking-wider">Valid until August 2025</span>
                                            <span class="text-[9px] font-black tracking-widest uppercase">Student ID Card</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- BACK SIDE -->
                                <div class="absolute inset-0 backface-hidden rounded-[1.8rem] overflow-hidden bg-white shadow-inner" 
                                     style="transform: rotateY(180deg)"
                                     :style="`background: linear-gradient(135deg, ${config.primary_color}, ${config.secondary_color}); color: ${config.text_color}`">
                                    
                                    <div class="p-8 h-full flex flex-col">
                                        <div class="flex justify-between items-start mb-6 border-b border-black/5 pb-2">
                                            <div class="w-10 h-10 bg-white shadow-sm rounded-xl flex items-center justify-center border border-slate-100 overflow-hidden">
                                                <template x-if="config.logo_url">
                                                    <img :src="config.logo_url" class="w-full h-full object-contain p-1">
                                                </template>
                                                <template x-if="!config.logo_url">
                                                    <svg class="w-6 h-6 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71L12 2z"/></svg>
                                                </template>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[10px] font-black opacity-40 uppercase tracking-[0.2em]">Contact & Rules</div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 flex-1">
                                            <template x-for="field in config.back_fields" :key="'back_preview_'+field">
                                                <div class="flex flex-col">
                                                    <div class="text-[8px] font-black opacity-40 uppercase tracking-widest" x-text="getFieldLabel(field)"></div>
                                                    <div class="text-xs font-medium" x-text="getFieldValue(field)"></div>
                                                </div>
                                            </template>

                                            <div class="mt-2 text-[9px] opacity-60 italic leading-relaxed whitespace-pre-line" x-text="config.back_content"></div>
                                        </div>

                                        <div class="mt-auto pt-6 flex justify-between items-end">
                                            <div x-show="config.show_barcode" class="bg-white p-2 border border-slate-100 rounded-lg">
                                                <div class="w-32 h-8 flex gap-1 items-end">
                                                    <template x-for="i in 20" :key="i">
                                                        <div class="bg-black flex-1" :style="`height: ${Math.random() * 60 + 40}%`"></div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[9px] font-black uppercase tracking-widest" x-text="config.school_name"></div>
                                                <div class="text-[8px] opacity-40">www.renaissance.edu.et</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="bg-indigo-50/50 backdrop-blur-xl border border-indigo-100 rounded-[2rem] p-8 flex items-start gap-6">
                            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-indigo-900 uppercase tracking-widest">Professional White Design</h4>
                                <p class="text-xs text-indigo-700/70 mt-1 font-semibold leading-relaxed">
                                    I've optimized the preview for light backgrounds. If you choose white as your primary color, the text and borders will adjust for maximum readability and a clean, modern look.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function idCardConfig() {
            return {
                side: 'front',
                config: {
                    ... @json($settings),
                    logo_url: "{{ $settings->logo_path ? asset('storage/' . $settings->logo_path) : '' }}"
                },
                availableFields: [
                    { id: 'student_id', label: 'Student ID' },
                    { id: 'full_name', label: 'Full Name' },
                    { id: 'grade', label: 'Grade Level' },
                    { id: 'section', label: 'Class Section' },
                    { id: 'gender', label: 'Gender' },
                    { id: 'date_of_birth', label: 'Date of Birth' },
                    { id: 'blood_group', label: 'Blood Group' },
                    { id: 'guardian_name', label: 'Guardian Name' },
                    { id: 'guardian_phone', label: 'Guardian Contact' },
                    { id: 'address', label: 'Physical Address' },
                    { id: 'emergency_contact', label: 'Emergency Contact' },
                ],
                handleLogoUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.config.logo_url = URL.createObjectURL(file);
                    }
                },
                getFieldLabel(id) {
                    const field = this.availableFields.find(f => f.id === id);
                    return field ? field.label : id;
                },
                getFieldValue(id) {
                    const values = {
                        student_id: 'RSS-24-00154',
                        full_name: 'Johnathan Doe',
                        grade: 'Grade 10',
                        section: 'Section B',
                        gender: 'Male',
                        date_of_birth: '12/05/2010',
                        blood_group: 'A positive',
                        guardian_name: 'Robert Doe',
                        guardian_phone: '+251 911 223 344',
                        address: 'Bole, Addis Ababa, Ethiopia',
                        emergency_contact: '+251 911 556 677 (Mother)'
                    };
                    return values[id] || '---';
                }
            }
        }
    </script>
    <style>
        .backface-hidden {
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .preserve-3d {
            transform-style: preserve-3d;
            -webkit-transform-style: preserve-3d;
        }
        .perspective-[1000px] {
            perspective: 1000px;
            -webkit-perspective: 1000px;
        }
    </style>
    @endpush
</x-admin-layout>
