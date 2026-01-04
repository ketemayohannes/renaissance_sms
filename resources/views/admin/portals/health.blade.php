<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Student Health Records</h2>
                <p class="text-sm text-slate-500 mt-1">Manage student medical information and health visits</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 lg:p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Coming Soon Card -->
            <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-3xl border border-rose-200/50 p-12 text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-rose-200">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-3">Health Records Module</h3>
                <p class="text-slate-600 mb-8 max-w-md mx-auto">
                    This module is currently under development. Soon you'll be able to track student health visits, manage medical records, and maintain emergency contacts.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <div class="px-4 py-2 bg-white rounded-xl border border-rose-200 text-sm font-medium text-rose-700">
                        <span class="mr-2">🏥</span> Health Visits Log
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-rose-200 text-sm font-medium text-rose-700">
                        <span class="mr-2">💊</span> Medical Conditions
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-rose-200 text-sm font-medium text-rose-700">
                        <span class="mr-2">💉</span> Vaccination Records
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-rose-200 text-sm font-medium text-rose-700">
                        <span class="mr-2">📞</span> Emergency Contacts
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
