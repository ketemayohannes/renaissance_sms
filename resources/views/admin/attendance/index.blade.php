<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Attendance') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Attendance', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6" x-data="{ 
                        gradeLevels: {{ Js::from($gradeLevels) }},
                        selectedGrade: '',
                        sections: [],
                        updateSections() {
                            const grade = this.gradeLevels.find(g => g.id == this.selectedGrade);
                            this.sections = grade ? grade.sections : [];
                        }
                    }">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Select Grade, Section and Date</h3>
                        <form action="{{ route('admin.attendance.register') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 border p-4 rounded-lg bg-gray-50">
                            <div>
                                <label for="grade_id" class="block text-sm font-medium text-gray-700">Grade</label>
                                <select id="grade_id" x-model="selectedGrade" @change="updateSections()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Grade</option>
                                    @foreach($gradeLevels as $gradeLevel)
                                        <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="section_id" class="block text-sm font-medium text-gray-700">Section</label>
                                <select name="section_id" id="section_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Section</option>
                                    <template x-for="section in sections" :key="section.id">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition w-full">
                                    Mark Attendance
                                </button>
                            </div>
                        </form>
                    </div>

                    <hr class="my-8">

                    <div x-data="{ 
                        gradeLevels: {{ Js::from($gradeLevels) }},
                        selectedGrade2: '',
                        sections2: [],
                        updateSections2() {
                            const grade = this.gradeLevels.find(g => g.id == this.selectedGrade2);
                            this.sections2 = grade ? grade.sections : [];
                        }
                    }">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Attendance Reports</h3>
                        <form action="{{ route('admin.attendance.report') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 border p-4 rounded-lg bg-gray-50">
                            <div>
                                <label for="report_grade_id" class="block text-sm font-medium text-gray-700">Grade</label>
                                <select id="report_grade_id" x-model="selectedGrade2" @change="updateSections2()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Grade</option>
                                    @foreach($gradeLevels as $gradeLevel)
                                        <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="report_section_id" class="block text-sm font-medium text-gray-700">Section</label>
                                <select name="section_id" id="report_section_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Section</option>
                                    <template x-for="section in sections2" :key="section.id">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                                <select name="month" id="month" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ date('m') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                                <select name="year" id="year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @foreach(range(date('Y')-1, date('Y')+1) as $y)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition w-full">
                                    View Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
