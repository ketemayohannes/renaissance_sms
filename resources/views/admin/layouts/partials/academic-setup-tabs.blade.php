<div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        @can('manage academic calendar')
        <a href="{{ route('admin.academic-calendar.index') }}"
           class="{{ request()->routeIs('admin.academic-calendar.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
            Academic Calendar
        </a>
        @endcan
        {{-- Year and term pages require "manage grade levels"; hide their tabs from calendar-only roles. --}}
        @can('manage grade levels')
        <a href="{{ route('admin.academic-years.index') }}"
           class="{{ request()->routeIs('admin.academic-years.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
            Academic Years
        </a>
        <a href="{{ route('admin.terms.index') }}"
           class="{{ request()->routeIs('admin.terms.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
            Terms Management
        </a>
        @endcan
    </nav>
</div>
