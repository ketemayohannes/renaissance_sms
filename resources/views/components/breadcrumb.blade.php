@props(['items' => [], 'home' => null])

@php
    // Portals other than admin (parent, teacher, student) each have their own
    // dashboard route — pick it from the current route's prefix rather than
    // hardcoding admin.dashboard, so this component works everywhere.
    $home ??= match (true) {
        request()->routeIs('parent.*') => route('parent.dashboard'),
        request()->routeIs('teacher.*') => route('teacher.dashboard'),
        request()->routeIs('student.*') => route('student.dashboard'),
        default => route('admin.dashboard'),
    };
@endphp

<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <!-- Home -->
        <li class="inline-flex items-center">
            <a href="{{ $home }}" class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-indigo-400">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
                Dashboard
            </a>
        </li>

        <!-- Dynamic Items -->
        @foreach($items as $index => $item)
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400 dark:text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    @if($loop->last)
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-slate-500 md:ml-2">{{ $item['label'] }}</span>
                    @else
                        <a href="{{ $item['url'] }}" class="ml-1 text-sm font-medium text-gray-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-indigo-400 md:ml-2">{{ $item['label'] }}</a>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
