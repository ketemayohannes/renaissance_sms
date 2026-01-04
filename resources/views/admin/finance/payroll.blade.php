<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Payroll Management</h2>
                <p class="text-sm text-slate-500 mt-1">Manage staff salaries, deductions, and payments</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 lg:p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Coming Soon Card -->
            <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-3xl border border-violet-200/50 p-12 text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-violet-200">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-3">Payroll Module</h3>
                <p class="text-slate-600 mb-8 max-w-md mx-auto">
                    This module is currently under development. Soon you'll be able to manage staff salaries, process payrolls, handle deductions, and generate payslips.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <div class="px-4 py-2 bg-white rounded-xl border border-violet-200 text-sm font-medium text-violet-700">
                        <span class="mr-2">💰</span> Salary Configuration
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-violet-200 text-sm font-medium text-violet-700">
                        <span class="mr-2">📝</span> Payroll Processing
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-violet-200 text-sm font-medium text-violet-700">
                        <span class="mr-2">➖</span> Deductions & Allowances
                    </div>
                    <div class="px-4 py-2 bg-white rounded-xl border border-violet-200 text-sm font-medium text-violet-700">
                        <span class="mr-2">🧾</span> Payslip Generation
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
