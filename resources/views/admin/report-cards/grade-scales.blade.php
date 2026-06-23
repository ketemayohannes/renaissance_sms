<x-admin-layout>
    <x-slot name="header">Grade Scaling Config</x-slot>

    <div class="space-y-8 pb-32" x-data="{ 
        scales: {{ json_encode($gradeScales) }},
        addScale() {
            this.scales.push({ min: '', grade: '', label: '' });
        },
        removeScale(index) {
            this.scales.splice(index, 1);
        }
    }">
        <!-- Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'Grade Scales', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                    Grade Scaling Configuration
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Define minimum score thresholds and corresponding letter grades</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50/50 backdrop-blur-md border border-emerald-100 p-6 rounded-[2rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-emerald-900 font-black text-sm uppercase tracking-widest">Update Successful</h3>
                    <p class="text-emerald-700 text-xs font-semibold mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.report-cards.update-grade-scales') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Scale Rules -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-10">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shadow-inner">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Grading Ranges</h3>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Rules are evaluated sequentially from highest threshold to lowest</p>
                                </div>
                            </div>
                            
                            <button type="button" @click="addScale()" class="px-5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add Range
                            </button>
                        </div>

                        <div class="space-y-4">
                            <table class="w-full text-left border-separate border-spacing-y-3">
                                <thead>
                                    <tr>
                                        <th class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-1/3">Min Score (%)</th>
                                        <th class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-1/4">Grade Letter</th>
                                        <th class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-1/3">Description / Label</th>
                                        <th class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(scale, index) in scales" :key="index">
                                        <tr class="group hover:bg-slate-50/50 transition-all duration-300">
                                            <td class="p-2">
                                                <input type="number" :name="'scales['+index+'][min]'" x-model="scale.min" 
                                                       class="premium-input w-full text-sm font-semibold" 
                                                       placeholder="e.g. 90" min="0" max="100" step="0.01" required>
                                            </td>
                                            <td class="p-2">
                                                <input type="text" :name="'scales['+index+'][grade]'" x-model="scale.grade" 
                                                       class="premium-input w-full text-sm font-bold text-center uppercase" 
                                                       placeholder="e.g. A" required>
                                            </td>
                                            <td class="p-2">
                                                <input type="text" :name="'scales['+index+'][label]'" x-model="scale.label" 
                                                       class="premium-input w-full text-sm font-semibold" 
                                                       placeholder="e.g. Excellent">
                                            </td>
                                            <td class="p-2 text-right">
                                                <button type="button" @click="removeScale(index)" 
                                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all"
                                                        title="Delete Range">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            
                            <template x-if="scales.length === 0">
                                <div class="text-center py-12 bg-slate-50 rounded-[2rem] border border-dashed border-slate-200">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">No grading ranges defined</h4>
                                    <p class="text-xs text-slate-500 font-semibold mt-1">Click the 'Add Range' button above to define your scale rules.</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Info panel -->
                <div class="space-y-8">
                    <div class="bg-indigo-600 rounded-[2.5rem] text-white p-8 shadow-xl shadow-indigo-900/40 relative overflow-hidden group">
                        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="relative z-10 flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-black text-sm uppercase tracking-widest mb-3 leading-none">Grading Logic Info</h4>
                                <p class="text-xs font-medium text-indigo-100/90 leading-relaxed space-y-2">
                                    <span>• Scores are mapped to the highest matching threshold. E.g., if a student gets 84.5%, and ranges are Min 90 (A) and Min 80 (B), the score maps to B.</span>
                                    <br><br>
                                    <span>• It is recommended to have a threshold starting at 0 (typically mapping to 'F') to ensure all possible scores map to a valid grade letter.</span>
                                    <br><br>
                                    <span>• These settings apply strictly to Kindergarten division report cards and class rosters. Elementary and High School reporting remains strictly numeric.</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Save Bar -->
            <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 flex items-center gap-4 p-4 bg-white/80 backdrop-blur-2xl rounded-[2.5rem] border border-white shadow-2xl animate-in slide-in-from-bottom-12 duration-500">
                <p class="hidden md:block px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-r border-slate-100 italic">
                    Configure your grading threshold values carefully.
                </p>
                <button type="submit" class="px-12 py-4 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-full hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-900/20 flex items-center gap-2 group">
                    Save Scaling Rules
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
        </form>
    </div>

    <style>
        .premium-input {
            background-color: rgb(248 250 252 / 0.5);
            border-color: rgb(226 232 240);
            border-radius: 1rem;
            padding-left: 1rem;
            padding-right: 1rem;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
            outline: 2px solid transparent;
            outline-offset: 2px;
        }
        .premium-input:focus {
            background-color: rgb(255 255 255);
            border-color: rgb(79 70 229);
            box-shadow: 0 0 0 4px rgb(79 70 229 / 0.1);
        }
    </style>
</x-admin-layout>
