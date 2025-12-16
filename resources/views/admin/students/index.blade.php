<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Students', 'url' => '#']
            ]" />
            
            <div class="flex justify-end mb-4 gap-2">
                <a href="{{ route('admin.students.export') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow-sm">
                    Export Students
                </a>
                <a href="{{ route('admin.students.import') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow-sm">
                    Import Students
                </a>
                <a href="{{ route('admin.students.create') }}" class="bg-black hover:bg-gray-800 text-white font-semibold py-2 px-4 rounded shadow-sm">
                    Register New Student
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('import_errors'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative mb-4">
                    <strong class="font-bold">Import Warnings/Errors:</strong>
                    <div class="mt-2 max-h-40 overflow-y-auto text-sm">
                        <ul class="list-disc list-inside">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Search Filter -->
                    <!-- Advanced Filter -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <form action="{{ route('admin.students.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <!-- Search -->
                            <div class="md:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name, ID, Admission No..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>

                            <!-- Gender -->
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <select name="gender" id="gender" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">All Genders</option>
                                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>

                            <!-- Grade Level -->
                            <div>
                                <label for="grade_id" class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                                <select name="grade_id" id="grade_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">All Grades</option>
                                    @foreach($gradeLevels as $grade)
                                        <option value="{{ $grade->id }}" {{ request('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Section -->
                            <div>
                                <label for="section_name" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                                <select name="section_name" id="section_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">All Sections</option>
                                    <option value="unassigned" {{ request('section_name') == 'unassigned' ? 'selected' : '' }} class="text-red-600 font-bold">Unassigned</option>
                                    @php
                                        $uniqueSections = collect();
                                        foreach($sections as $gradeSections) {
                                            foreach($gradeSections as $section) {
                                                if (!$uniqueSections->contains('name', $section->name)) {
                                                    $uniqueSections->push($section);
                                                }
                                            }
                                        }
                                        $uniqueSections = $uniqueSections->sortBy('name');
                                    @endphp
                                    @foreach($uniqueSections as $section)
                                        <option value="{{ $section->name }}" {{ request('section_name') == $section->name ? 'selected' : '' }}>{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="md:col-span-6 flex justify-between items-center mt-2">
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow-sm text-sm font-semibold">Apply Filters</button>
                                    @if(request()->anyFilled(['search', 'gender', 'grade_id', 'section_name', 'status']))
                                        <a href="{{ route('admin.students.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md shadow-sm text-sm font-semibold flex items-center">Clear Filters</a>
                                    @endif
                                </div>
                                
                                <!-- Quick Link for Blocked Students -->
                                <a href="{{ route('admin.students.index', ['status' => 'blocked']) }}" class="text-red-600 hover:text-red-800 text-sm font-medium underline">
                                    View Blocked Students
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto" x-data="{ selected: [], allSelected: false }">
                        <!-- Bulk Actions Toolbar -->
                        <div x-show="selected.length > 0" class="bg-indigo-50 p-2 mb-2 rounded flex justify-between items-center transition " style="display: none;">
                            <span class="text-sm text-indigo-700 font-semibold" x-text="selected.length + ' selected'"></span>
                            <div class="flex space-x-2">
                                <form action="{{ route('admin.students.bulk-destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete the selected students?')">
                                    @csrf
                                    <!-- Create hidden inputs for each selected ID -->
                                    <template x-for="id in selected">
                                        <input type="hidden" name="ids[]" :value="id">
                                    </template>
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1 px-3 rounded shadow-sm">
                                        Delete Selected
                                    </button>
                                </form>
                            </div>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" id="selectAll" @click="allSelected = !allSelected; selected = allSelected ? {{ $students->pluck('id') }} : []" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No
                                    </th>
                                    <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider p-0">
                                        <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="px-6 py-3 group flex items-center w-full h-full hover:text-gray-700 cursor-pointer">
                                            Name
                                            @if(request('sort') === 'name')
                                                <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @else
                                                <span class="ml-1 text-gray-300 opacity-0 group-hover:opacity-100">↕</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider p-0">
                                        <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['sort' => 'student_id', 'direction' => request('sort') === 'student_id' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="px-6 py-3 group flex items-center w-full h-full hover:text-gray-700 cursor-pointer">
                                            ID
                                            @if(request('sort') === 'student_id')
                                                <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @else
                                                <span class="ml-1 text-gray-300 opacity-0 group-hover:opacity-100">↕</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider p-0">
                                        <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['sort' => 'gender', 'direction' => request('sort') === 'gender' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="px-6 py-3 group flex items-center w-full h-full hover:text-gray-700 cursor-pointer">
                                            Gender
                                            @if(request('sort') === 'gender')
                                                <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @else
                                                <span class="ml-1 text-gray-300 opacity-0 group-hover:opacity-100">↕</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Grade/Section
                                    </th>
                                    <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider p-0">
                                        <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['sort' => 'is_active', 'direction' => request('sort') === 'is_active' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="px-6 py-3 group flex items-center w-full h-full hover:text-gray-700 cursor-pointer">
                                            Status
                                            @if(request('sort') === 'is_active')
                                                <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @else
                                                <span class="ml-1 text-gray-300 opacity-0 group-hover:opacity-100">↕</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($students as $student)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" value="{{ $student->id }}" x-model="selected" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ ($students->currentPage() - 1) * $students->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @if($student->photo)
                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($student->photo) }}" alt="{{ $student->full_name }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                            <span class="text-gray-500 font-medium text-sm">{{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $student->full_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $student->admission_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $student->student_id }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ ucfirst($student->gender) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($student->enrollments->whereNull('end_date')->first())
                                                @php $enrollment = $student->enrollments->whereNull('end_date')->first(); @endphp
                                                <div class="text-sm text-gray-900">{{ $enrollment->section->gradeLevel->name }} - {{ $enrollment->section->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $enrollment->academicYear->name }}</div>
                                            @else
                                                <span class="text-red-500 text-sm">Not Enrolled</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $student->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $student->is_active ? 'Active' : 'Blocked' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.students.show', $student) }}" class="text-blue-600 hover:text-blue-800 font-medium mr-3">View</a>
                                            <a href="{{ route('admin.students.edit', $student) }}" class="text-indigo-600 hover:text-indigo-800 font-medium mr-3">Edit</a>
                                            
                                            <!-- Block/Unblock -->
                                            <form action="{{ route('admin.students.toggle-block', $student) }}" method="POST" class="inline-block mr-3 delete-form" data-confirm-message="Are you sure you want to {{ $student->is_active ? 'block' : 'unblock' }} this student?">
                                                @csrf
                                                <button type="submit" class="{{ $student->is_active ? 'text-orange-600 hover:text-orange-800 font-medium' : 'text-green-600 hover:text-green-800 font-medium' }}">
                                                    {{ $student->is_active ? 'Block' : 'Unblock' }}
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure? This will delete the user account as well.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No students found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div>
                            {{ $students->links() }}
                        </div>
                        <form method="GET" action="{{ route('admin.students.index') }}" class="flex items-center gap-2">
                            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            
                            <label for="per_page" class="text-sm text-gray-700 font-medium">Rows per page:</label>
                            <select name="per_page" id="per_page" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 pl-2 pr-8">
                                <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
