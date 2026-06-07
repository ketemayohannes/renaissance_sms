<x-admin-layout>
    <x-slot name="header">Post Notice</x-slot>

    <div class="space-y-8 pb-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Notice Board', 'url' => route('admin.notices.index')],
                    ['label' => 'Create Notice', 'url' => '#'],
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-violet-600 rounded-full"></span>
                    Post a Notice
                </h1>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-8 lg:p-12">
            <form action="{{ route('admin.notices.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Title --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Notice Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g., School Holiday Announcement"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all" required>
                        @error('title') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Audience --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Target Audience <span class="text-rose-500">*</span></label>
                        <select name="target_audience" class="premium-select w-full" required>
                            @foreach(['All','Parent','Teacher','Student'] as $aud)
                                <option value="{{ $aud }}" {{ old('target_audience') == $aud ? 'selected' : '' }}>{{ $aud }}</option>
                            @endforeach
                        </select>
                        @error('target_audience') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Active toggle --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Publish Status</label>
                        <div class="flex items-center gap-4 h-[50px]">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="w-5 h-5 rounded accent-violet-600">
                                <span class="text-sm font-bold text-slate-700">Publish immediately</span>
                            </label>
                        </div>
                    </div>

                    {{-- Publish Date --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Publish Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="publish_date" value="{{ old('publish_date', now()->format('Y-m-d')) }}"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all" required>
                        @error('publish_date') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Expiry Date --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Expiry Date <span class="text-slate-400">(optional)</span></label>
                        <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all">
                        @error('expiry_date') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Content --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Notice Content <span class="text-rose-500">*</span></label>
                        <textarea name="content" rows="8" placeholder="Write the full notice content here..."
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all leading-relaxed" required>{{ old('content') }}</textarea>
                        @error('content') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Attachment --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Attachment <span class="text-slate-400">(PDF, Image, Word — max 5MB)</span></label>
                        <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.docx"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-600 font-semibold focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all file:mr-4 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-violet-600 file:text-white hover:file:bg-violet-700">
                        @error('attachment') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-4 flex items-center gap-4 border-t border-slate-100">
                    <button type="submit" class="px-10 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-[2rem] hover:bg-violet-600 shadow-xl shadow-slate-200 transition-all">
                        Post Notice
                    </button>
                    <a href="{{ route('admin.notices.index') }}" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-rose-500 transition-colors">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
