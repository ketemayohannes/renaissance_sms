<x-admin-layout>
    <x-slot name="header">Edit Notice</x-slot>

    <div class="space-y-8 pb-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Notice Board', 'url' => route('admin.notices.index')],
                    ['label' => 'Edit Notice', 'url' => '#'],
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-amber-500 rounded-full"></span>
                    Edit Notice
                </h1>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-8 lg:p-12">
            <form action="{{ route('admin.notices.update', $notice) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Notice Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $notice->title) }}"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all" required>
                        @error('title') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Target Audience <span class="text-rose-500">*</span></label>
                        <select name="target_audience" class="premium-select w-full" required>
                            @foreach(['All','Parent','Teacher','Student'] as $aud)
                                <option value="{{ $aud }}" {{ old('target_audience', $notice->target_audience) == $aud ? 'selected' : '' }}>{{ $aud }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Publish Status</label>
                        <div class="flex items-center gap-4 h-[50px]">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $notice->is_active) ? 'checked' : '' }} class="w-5 h-5 rounded accent-violet-600">
                                <span class="text-sm font-bold text-slate-700">Active / Visible</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Publish Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="publish_date" value="{{ old('publish_date', $notice->publish_date->format('Y-m-d')) }}"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Expiry Date <span class="text-slate-400">(optional)</span></label>
                        <input type="date" name="expiry_date" value="{{ old('expiry_date', $notice->expiry_date?->format('Y-m-d')) }}"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Notice Content <span class="text-rose-500">*</span></label>
                        <textarea name="content" rows="8"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all leading-relaxed" required>{{ old('content', $notice->content) }}</textarea>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Replace Attachment <span class="text-slate-400">(leave empty to keep existing)</span></label>
                        @if($notice->attachment)
                            <p class="text-xs text-slate-500 mb-2 font-semibold">Current: <a href="/storage/{{ $notice->attachment }}" target="_blank" class="text-violet-600 hover:underline">View file</a></p>
                        @endif
                        <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.docx"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-600 font-semibold focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all file:mr-4 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-violet-600 file:text-white hover:file:bg-violet-700">
                    </div>
                </div>

                <div class="pt-4 flex items-center gap-4 border-t border-slate-100">
                    <button type="submit" class="px-10 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-[2rem] hover:bg-amber-500 shadow-xl shadow-slate-200 transition-all">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.notices.index') }}" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-rose-500 transition-colors">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
