@php
    $layout = Auth::user() && Auth::user()->hasRole('Teacher') ? 'teacher-layout' : 'admin-layout';
@endphp
<x-dynamic-component :component="$layout">
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-800 tracking-tight">
            {{ __('Profile Settings') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Breadcrumbs -->
            <x-breadcrumb :items="[
                ['label' => 'Profile', 'url' => '#']
            ]" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Profile Information -->
                <div class="p-8 bg-white/80 backdrop-blur-xl border border-white shadow-xl shadow-slate-200/50 rounded-[2.5rem] relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -m-8 w-32 h-32 bg-indigo-50 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Update Password -->
                <div class="p-8 bg-white/80 backdrop-blur-xl border border-white shadow-xl shadow-slate-200/50 rounded-[2.5rem] relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -m-8 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            @unless(Auth::user() && Auth::user()->hasRole('Teacher'))
            <!-- Delete Account -->
            <div class="p-8 bg-rose-50/50 backdrop-blur-xl border border-rose-100 shadow-xl shadow-rose-100/20 rounded-[2.5rem] relative">
                <div class="absolute top-0 right-0 -m-16 w-64 h-64 bg-rose-100/50 rounded-full blur-3xl opacity-50"></div>
                <div class="relative">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
            @endunless
        </div>
    </div>
</x-dynamic-component>
