<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Fee Management</h2>
                <p class="text-sm text-slate-500 mt-1">Manage student fees, payments, and invoices</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 lg:p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Coming Soon Card -->
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl border border-emerald-200/50 p-12 text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-emerald-200">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-3">Fee Management Module</h3>
                <p class="text-slate-600 mb-8 max-w-md mx-auto">
                    This module is currently under development. Soon you'll be able to manage student fees, generate invoices, track payments, and view financial reports.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <div class="px-4 py-2 bg-white rounded-xl border border-emerald-200 text-sm font-medium text-emerald-700">
                        <span class="mr-2">📋</span> Fee Structure Setup
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-emerald-200 text-sm font-medium text-emerald-700">
                        <span class="mr-2">🧾</span> Invoice Generation
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-emerald-200 text-sm font-medium text-emerald-700">
                        <span class="mr-2">💳</span> Payment Tracking
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-emerald-200 text-sm font-medium text-emerald-700">
                        <span class="mr-2">📊</span> Financial Reports
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
