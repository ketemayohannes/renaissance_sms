<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Inventory Management</h2>
                <p class="text-sm text-slate-500 mt-1">Manage school assets, supplies, and equipment</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 lg:p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Coming Soon Card -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl border border-amber-200/50 p-12 text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-amber-200">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-3">Inventory Module</h3>
                <p class="text-slate-600 mb-8 max-w-md mx-auto">
                    This module is currently under development. Soon you'll be able to track school assets, manage supplies, and handle equipment assignments.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <div class="px-4 py-2 bg-white rounded-xl border border-amber-200 text-sm font-medium text-amber-700">
                        <span class="mr-2">📦</span> Asset Registry
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-amber-200 text-sm font-medium text-amber-700">
                        <span class="mr-2">📚</span> Supplies Tracking
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-amber-200 text-sm font-medium text-amber-700">
                        <span class="mr-2">🔧</span> Equipment Assignments
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-amber-200 text-sm font-medium text-amber-700">
                        <span class="mr-2">📉</span> Stock Reports
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
