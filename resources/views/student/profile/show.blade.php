<x-student-layout header="My Profile">

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Personal Info -->
            <div class="bg-white p-4 sm:p-8 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h2 class="text-lg font-medium text-gray-900">Personal Information</h2>
                    <p class="mt-1 text-sm text-gray-600 mb-6">Your personal details as recorded in school records.</p>
                    
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Student ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $student->student_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $student->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $student->date_of_birth->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Gender</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $student->gender }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Admission Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $student->admission_number }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Password Update -->
            <div class="bg-white p-4 sm:p-8 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h2 class="text-lg font-medium text-gray-900">Update Password</h2>
                    <p class="mt-1 text-sm text-gray-600 mb-6">Ensure your account is using a long, random password to stay secure.</p>

                    <form method="post" action="{{ route('student.password.update') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('put')

                        <div>
                            <x-input-label for="current_password" :value="__('Current Password')" />
                            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('New Password')" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>

                            @if (session('status') === 'password-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-gray-600"
                                >{{ __('Saved.') }}</p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-student-layout>
