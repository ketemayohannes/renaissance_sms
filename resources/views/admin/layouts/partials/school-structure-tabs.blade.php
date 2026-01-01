<div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <a href="{{ route('admin.sections.index') }}" 
           class="{{ request()->routeIs('admin.sections.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
            Sections
        </a>
        <a href="{{ route('admin.grade-levels.index') }}" 
           class="{{ request()->routeIs('admin.grade-levels.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
            Grade Levels
        </a>
        <a href="{{ route('admin.divisions.index') }}" 
           class="{{ request()->routeIs('admin.divisions.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
            Divisions
        </a>
    </nav>
</div>
