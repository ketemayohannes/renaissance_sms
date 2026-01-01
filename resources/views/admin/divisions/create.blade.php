<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Establish Division</h2>
        </div>
    </x-slot>

    <div class="space-y-8 pb-12">
        <x-breadcrumb :items="[
            ['label' => 'Divisions', 'url' => route('admin.divisions.index')],
            ['label' => 'Creation Portal', 'url' => '#']
        ]" />

        <div class="max-w-3xl mx-auto">
            <div class="glass-panel overflow-hidden border-white/40 shadow-2xl">
                <div class="p-8 border-b border-slate-50 bg-slate-50/30">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Division Configuration</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1 italic">Define the parameters for the new academic division.</p>
                </div>

                <div class="p-8">
                    <form action="{{ route('admin.divisions.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="name" class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Division Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                                    class="premium-input w-full" placeholder="e.g. Primary School" required>
                                @error('name')<span class="text-rose-600 text-[10px] font-bold uppercase tracking-tight">{{ $message }}</span>@enderror
                            </div>

                            <div class="space-y-2">
                                <label for="code" class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Designation Code *</label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}" 
                                    class="premium-input w-full" placeholder="e.g. PS" required>
                                @error('code')<span class="text-rose-600 text-[10px] font-bold uppercase tracking-tight">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="description" class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Operational Description</label>
                            <textarea name="description" id="description" rows="3" 
                                class="premium-input w-full" placeholder="Brief overview of the division's purpose...">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="sort_order" class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Sequence Order *</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" 
                                    class="premium-input w-full" required>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-4">
                            <a href="{{ route('admin.divisions.index') }}" class="px-6 py-3 text-slate-400 hover:text-slate-600 font-black text-[10px] uppercase tracking-widest transition-all">
                                Abort Mission
                            </a>
                            <button type="submit" class="vibrant-btn-blue h-12">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Deploy Division
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
