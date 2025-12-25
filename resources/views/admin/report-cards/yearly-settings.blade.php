<x-admin-layout>
    <x-slot name="header">Yearly Report Card Settings</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Report Cards', 'url' => route('admin.section-grades.index')],
            ['label' => 'Yearly Settings', 'url' => '#']
        ]" />

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.report-cards.update-yearly-settings') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column: Evaluation Method & Remark -->
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="evaluation_method" :value="__('Evaluation Method (Grading Scale)')" />
                                    <p class="text-xs text-gray-500 mb-2">Enter the text for the "Evaluation method" box (e.g., 100-90 = A Excellent, etc.)</p>
                                    <textarea id="evaluation_method" name="evaluation_method" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings->yearly_config['evaluation_method'] ?? "100-90 - A .... Excellent\n89-80 - B .... Very Good\n79-70 - C .... Satisfactory\n69-60 - D .... Fair\n<60 .... Poor" }}</textarea>
                                </div>

                                <div>
                                    <x-input-label for="remark" :value="__('Remark Text')" />
                                    <p class="text-xs text-gray-500 mb-2">Enter the text for the "Remark" box explaining promotion rules.</p>
                                    <textarea id="remark" name="remark" rows="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings->yearly_config['remark'] ?? "A student who has a final yearly average mark of 50% or above in every subject is to be considered as a better achiever.\nAny mark below 50% needs more effort to improve his/her performance.\nConduct marks of C or below show that some behavioral problem. Which should be improved by close follow up and counselling of parents." }}</textarea>
                                </div>
                            </div>

                            <!-- Right Column: Principal & Footer -->
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="principal_name" :value="__('Principal Name')" />
                                    <x-text-input id="principal_name" class="block mt-1 w-full" type="text" name="principal_name" :value="$settings->yearly_config['principal_name'] ?? ''" />
                                </div>

                                <div>
                                    <x-input-label for="parent_instructions" :value="__('Parent Instructions (Footer)')" />
                                    <p class="text-xs text-gray-500 mb-2">Instructions displayed at the bottom of the back page for parents.</p>
                                    <textarea id="parent_instructions" name="parent_instructions" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings->yearly_config['parent_instructions'] ?? "Please sign the grade report after the first, second, and third quarters and return it back to school immediately after discussing the report with your child. After the fourth quarter the grade report card will be collected by parents. These and all school records should be kept in a safe place for permanent record." }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-primary-button>
                                {{ __('Save Yearly Settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
