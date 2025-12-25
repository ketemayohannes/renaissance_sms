<x-admin-layout>
    <x-slot name="header">Withdraw Student: {{ $student->full_name }}</x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => $student->full_name, 'url' => route('admin.students.show', $student)],
            ['label' => 'Withdraw', 'url' => '#']
        ]" />
        
        <div class="card overflow-hidden">
            <div class="p-6 text-gray-900">
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h4 class="font-bold text-yellow-800 mb-2">⚠️ Warning</h4>
                        <p class="text-yellow-700 text-sm">
                            This action will mark the student as no longer active and close their current enrollment.
                            This is a permanent status change that should only be used for students who are officially leaving the school.
                        </p>
                    </div>

                    <form action="{{ route('admin.students.withdraw.store', $student) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="new_status" class="block text-sm font-medium text-gray-700">New Status</label>
                            <select name="new_status" id="new_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Status</option>
                                <option value="withdrawn">Withdrawn</option>
                                <option value="graduated">Graduated</option>
                                <option value="transferred">Transferred</option>
                                <option value="dropped_out">Dropped Out</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                            <select name="reason" id="reason" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Reason</option>
                                @foreach($reasons as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="effective_date" class="block text-sm font-medium text-gray-700">Effective Date</label>
                            <input type="date" name="effective_date" id="effective_date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>

                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Optional notes about this withdrawal..."></textarea>
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('admin.students.show', $student) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded transition">
                                Cancel
                            </a>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-10 rounded shadow-lg transition">
                                Confirm Withdrawal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
</x-admin-layout>
