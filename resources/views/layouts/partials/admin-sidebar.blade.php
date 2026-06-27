@php
    $pendingExamsCount = \App\Models\ExamPaper::where('status', 'submitted')->count();
@endphp

<aside 
       x-data="{ 
           openCategories: JSON.parse(localStorage.getItem('openCategories')) || { 
               'students': true, 
               'communications': true, 
               'academics': true, 
               'reports': true, 
               'hr': true, 
               'setup': true, 
               'system': true 
           },
           toggleCategory(key) {
               if (!this.openCategories[key]) {
                   for (let k in this.openCategories) {
                       this.openCategories[k] = false;
                   }
               }
               this.openCategories[key] = !this.openCategories[key];
               localStorage.setItem('openCategories', JSON.stringify(this.openCategories));
           }
       }"
       class="fixed inset-y-0 left-0 z-[100] bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 flex flex-col transition-all duration-300 lg:translate-x-0"
       :class="[
           $store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full',
           sidebarCollapsed ? 'w-20' : 'w-64'
       ]">
    
    <!-- Logo -->
    <div class="h-16 flex items-center border-b border-slate-200 dark:border-slate-700 flex-shrink-0 relative transition-all duration-300"
         :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-6'">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200 flex-shrink-0 z-10 relative">
                <span class="text-white font-bold text-sm">R</span>
            </div>
            <span x-show="!sidebarCollapsed" 
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 -translate-x-2"
                  x-transition:enter-end="opacity-100 translate-x-0"
                  class="font-heading font-bold text-slate-900 dark:text-slate-100 text-lg whitespace-nowrap">Renaissance</span>
        </a>

        <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)"
                class="hidden lg:flex p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-indigo-600 transition-colors z-20"
                :class="sidebarCollapsed ? 'absolute -right-5 top-1/2 -translate-y-1/2 bg-white shadow-md border border-slate-200' : ''">
            <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>

        <button @click="$store.ui.sidebarOpen = false" class="lg:hidden p-1 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto custom-scrollbar"
         x-on:scroll.throttle.50ms="$el.querySelectorAll('.sidebar-category-header').forEach(h => h.classList.toggle('is-stuck', h.offsetTop <= $el.scrollTop))">
        
        <div class="sidebar-category-header" 
             :class="sidebarCollapsed ? 'text-center px-0 flex justify-center w-full' : 'sticky top-0 z-10 bg-white/95 backdrop-blur-md'">
            <span x-show="!sidebarCollapsed">Overview</span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : '' }}" title="Dashboard">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition>Dashboard</span>
        </a>
        
        @canany(['view students', 'view parents', 'view disciplinary', 'view promotions', 'view id cards'])
        <div class="sidebar-category-header flex items-center cursor-pointer group" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 backdrop-blur-md'"
             @click="toggleCategory('students')">
            <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 transition-colors">Student Management</span>
            <span x-show="!sidebarCollapsed">
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                     :class="openCategories['students'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        
        <div x-show="openCategories['students'] || sidebarCollapsed" x-collapse>
            @can('view students')
            <a href="{{ route('admin.students.index') }}" class="sidebar-link {{ request()->routeIs('admin.students.*') && !request()->has('status') ? 'sidebar-link-active' : '' }}" title="Active Students">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Active Students</span>
            </a>

            <a href="{{ route('admin.students.index', ['status' => 'inactive']) }}" class="sidebar-link {{ request('status') === 'inactive' ? 'sidebar-link-active' : '' }}" title="Inactive Students">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Inactive Students</span>
            </a>
            @endcan

            @can('view parents')
            <a href="{{ route('admin.guardians.index') }}" class="sidebar-link {{ request()->routeIs('admin.guardians.*') ? 'sidebar-link-active' : '' }}" title="Parents">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Parents</span>
            </a>
            @endcan

            @can('view disciplinary')
            <a href="{{ route('admin.disciplinary.index') }}" class="sidebar-link {{ request()->routeIs('admin.disciplinary.*') && !request()->routeIs('admin.discipline-settings.*') ? 'sidebar-link-active' : '' }}" title="Disciplinary">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Disciplinary</span>
            </a>

            <a href="{{ route('admin.discipline-settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.discipline-settings.*') ? 'sidebar-link-active' : '' }}" title="Discipline Settings">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Discipline Settings</span>
            </a>
            @endcan

            @can('view promotions')
            <a href="{{ route('admin.promotions.index') }}" class="sidebar-link {{ request()->routeIs('admin.promotions.*') ? 'sidebar-link-active' : '' }}" title="Promotions">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Promotions</span>
            </a>
            @endcan

            @can('view id cards')
            <a href="{{ route('admin.id-cards.index') }}" class="sidebar-link {{ request()->routeIs('admin.id-cards.*') ? 'sidebar-link-active' : '' }}" title="ID Cards">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.333 0 4 .667 4 2v1H5v-1c0-1.333 2.667-2 4-2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>ID Cards</span>
            </a>
            @endcan
        </div>
        @endcanany

        @canany(['view attendance', 'view timetable', 'view master gradebook', 'view subject gradebook', 'view assessment types', 'view assessment assignments', 'view subject assignments', 'view activities'])
        <div class="sidebar-category-header flex items-center cursor-pointer group relative" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 backdrop-blur-md'"
             @click="toggleCategory('academics')">
            
            <div class="flex items-center gap-2">
                <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 transition-colors">Academic Operations</span>
                @if($pendingExamsCount > 0)
                    <span x-show="!sidebarCollapsed && !openCategories['academics']" x-transition class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-600 text-[9px] font-black border border-rose-200">
                        {{ $pendingExamsCount }}
                    </span>
                @endif
            </div>

            <div class="flex items-center">
                <span x-show="!sidebarCollapsed">
                    <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                         :class="openCategories['academics'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </span>
                <span x-show="sidebarCollapsed" class="relative flex h-3 w-3">
                    @if($pendingExamsCount > 0)
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    @else
                        •••
                    @endif
                </span>
            </div>
        </div>
        
        <div x-show="openCategories['academics'] || sidebarCollapsed" x-collapse>
            @can('view attendance')
            <a href="{{ route('admin.attendance.index') }}" class="sidebar-link {{ request()->routeIs('admin.attendance.*') ? 'sidebar-link-active' : '' }}" title="Attendance">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Attendance</span>
            </a>
            @endcan

            @can('view timetable')
            <a href="{{ route('admin.timetable.index') }}" class="sidebar-link {{ request()->routeIs('admin.timetable.*') ? 'sidebar-link-active' : '' }}" title="Timetable">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Timetable</span>
            </a>
            @endcan
            
            @can('view master gradebook')
            <a href="{{ route('admin.section-grades.index') }}" class="sidebar-link {{ request()->routeIs('admin.section-grades.*') ? 'sidebar-link-active' : '' }}" title="Gradebook (Master)">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Gradebook (Master)</span>
            </a>
            @endcan

            @can('view subject gradebook')
            <a href="{{ route('admin.gradebook.index') }}" class="sidebar-link {{ request()->routeIs('admin.gradebook.*') ? 'sidebar-link-active' : '' }}" title="Gradebook (Subject)">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Gradebook (Subject)</span>
            </a>
            @endcan

            @can('view assessment types')
            <a href="{{ route('admin.assessment-types.index') }}" class="sidebar-link {{ request()->routeIs('admin.assessment-types.*') ? 'sidebar-link-active' : '' }}" title="Assessment Types">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Assessment Types</span>
            </a>
            @endcan

            @can('view assessment assignments')
            <a href="{{ route('admin.assessment-templates.index') }}" class="sidebar-link {{ request()->routeIs('admin.assessment-templates.*') ? 'sidebar-link-active' : '' }}" title="Assessment Assignment">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Assessment Assignment</span>
            </a>
            @endcan

            @can('view subject assignments')
            <a href="{{ route('admin.subject-assignments.index') }}" class="sidebar-link {{ request()->routeIs('admin.subject-assignments.*') ? 'sidebar-link-active' : '' }}" title="Assignments">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Assignments</span>
            </a>
            @endcan

            @can('view activities')
            <a href="{{ route('admin.activities.index') }}" class="sidebar-link {{ request()->routeIs('admin.activities.*') ? 'sidebar-link-active' : '' }}" title="Activities & Exams">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Activities & Exams</span>
            </a>
            @endcan

            <a href="{{ route('admin.exams.index') }}" class="sidebar-link {{ request()->routeIs('admin.exams.*') ? 'sidebar-link-active' : '' }} flex items-center justify-between" title="Exam Paper Reviews">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        @if($pendingExamsCount > 0)
                            <span x-show="sidebarCollapsed" class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                            </span>
                        @endif
                    </div>
                    <span x-show="!sidebarCollapsed" x-transition>Exam Reviews</span>
                </div>
                @if($pendingExamsCount > 0)
                    <span x-show="!sidebarCollapsed" x-transition class="px-2 py-0.5 rounded-full bg-rose-500 text-white text-[9px] font-black shadow-sm">
                        {{ $pendingExamsCount }}
                    </span>
                @endif
            </a>
        </div>
        @endcanany

        @canany(['view academic reports', 'view report cards'])
        <div class="sidebar-category-header flex items-center cursor-pointer group" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 backdrop-blur-md'"
             @click="toggleCategory('reports')">
            <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 transition-colors">Results & Reporting</span>
            <span x-show="!sidebarCollapsed">
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                     :class="openCategories['reports'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        
        <div x-show="openCategories['reports'] || sidebarCollapsed" x-collapse>
            @can('view academic reports')
            <a href="{{ route('admin.academic-reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.academic-reports.*') ? 'sidebar-link-active' : '' }}" title="Academic Reports">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Academic Reports</span>
            </a>

            <a href="{{ route('admin.result-analysis.index') }}" class="sidebar-link {{ request()->routeIs('admin.result-analysis.*') ? 'sidebar-link-active' : '' }}" title="Result Analysis">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Result Analysis</span>
            </a>
            @endcan
            
            @can('view report cards')
            <a href="{{ route('admin.report-cards.exports') }}" class="sidebar-link {{ request()->routeIs('admin.report-cards.*') ? 'sidebar-link-active' : '' }}" title="Report Cards">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Report Cards</span>
            </a>
            @endcan

            @can('view academic reports')
            <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'sidebar-link-active' : '' }}" title="General Reports">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>General Reports</span>
            </a>
            @endcan
        </div>
        @endcanany

        {{-- ═══ Communication ═══ --}}
        <div class="sidebar-category-header flex items-center cursor-pointer group" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 backdrop-blur-md'"
             @click="toggleCategory('communications')">
            <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 transition-colors">Communication</span>
            <span x-show="!sidebarCollapsed">
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                     :class="openCategories['communications'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        
        <div x-show="openCategories['communications'] || sidebarCollapsed" x-collapse>
            <a href="{{ route('admin.notices.index') }}" class="sidebar-link {{ request()->routeIs('admin.notices.*') ? 'sidebar-link-active' : '' }}" title="Notice Board">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Notice Board</span>
            </a>

            <a href="{{ route('admin.messages.index') }}" class="sidebar-link {{ request()->routeIs('admin.messages.*') ? 'sidebar-link-active' : '' }}" title="Messages">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Messages</span>
            </a>

            @role('Super Admin')
            <a href="{{ route('admin.settings.general.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.general.*') || request()->routeIs('admin.settings.communication.*') ? 'sidebar-link-active' : '' }}" title="General Settings">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>General Settings</span>
            </a>
            @endrole
        </div>

        @canany(['view employees', 'view section assignments'])
        <div class="sidebar-category-header flex items-center cursor-pointer group" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 backdrop-blur-md'"
             @click="toggleCategory('hr')">
            <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 transition-colors">Human Resources</span>
            <span x-show="!sidebarCollapsed">
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                     :class="openCategories['hr'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        
        <div x-show="openCategories['hr'] || sidebarCollapsed" x-collapse>
            @can('view employees')
            <a href="{{ route('admin.employees.index') }}" class="sidebar-link {{ request()->routeIs('admin.employees.*') ? 'sidebar-link-active' : '' }}" title="Employees (Staff)">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Employees (Staff)</span>
            </a>
            @endcan

            @can('view section assignments')
            <a href="{{ route('admin.teacher-assignments.index') }}" class="sidebar-link {{ request()->routeIs('admin.teacher-assignments.*') ? 'sidebar-link-active' : '' }}" title="Section Assignments">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Section Assignments</span>
            </a>
            @endcan
        </div>
        @endcanany

        @canany(['view finance', 'view payroll'])
        <div class="sidebar-category-header flex items-center cursor-pointer group" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 backdrop-blur-md'"
             @click="toggleCategory('finance')">
            <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 transition-colors uppercase tracking-widest">Finance & Ops</span>
            <span x-show="!sidebarCollapsed">
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                     :class="openCategories['finance'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        <div x-show="openCategories['finance'] || sidebarCollapsed" x-collapse>
            @can('view finance')
            <a href="{{ route('admin.finance.fees') }}" class="sidebar-link {{ request()->routeIs('admin.finance.fees') ? 'sidebar-link-active' : '' }}" title="Fee Management">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Fee Management</span>
            </a>
            @endcan

            @can('view payroll')
            <a href="{{ route('admin.finance.payroll') }}" class="sidebar-link {{ request()->routeIs('admin.finance.payroll') ? 'sidebar-link-active' : '' }}" title="Payroll">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Payroll</span>
            </a>
            @endcan
        </div>
        @endcanany

        @canany(['view health', 'view inventory', 'view library'])
        <div class="sidebar-category-header flex items-center cursor-pointer group" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 backdrop-blur-md'"
             @click="toggleCategory('services')">
            <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 transition-colors uppercase tracking-widest">Portal Services</span>
            <span x-show="!sidebarCollapsed">
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                     :class="openCategories['services'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        <div x-show="openCategories['services'] || sidebarCollapsed" x-collapse>
            @can('view health')
            <a href="{{ route('admin.portals.health') }}" class="sidebar-link {{ request()->routeIs('admin.portals.health') ? 'sidebar-link-active' : '' }}" title="Health Records">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Health Records</span>
            </a>
            @endcan
            @can('view inventory')
            <a href="{{ route('admin.portals.inventory') }}" class="sidebar-link {{ request()->routeIs('admin.portals.inventory') ? 'sidebar-link-active' : '' }}" title="Inventory">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Inventory</span>
            </a>
            @endcan
        </div>
        @endcanany

        @can('view school setup')
        <div class="sidebar-category-header flex items-center cursor-pointer group" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 backdrop-blur-md'"
             @click="toggleCategory('setup')">
            <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 transition-colors">School Setup</span>
            <span x-show="!sidebarCollapsed">
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                     :class="openCategories['setup'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        
        <div x-show="openCategories['setup'] || sidebarCollapsed" x-collapse>
            <a href="{{ route('admin.academic-years.index') }}" class="sidebar-link {{ request()->routeIs('admin.academic-years.*') || request()->routeIs('admin.terms.*') ? 'sidebar-link-active' : '' }}" title="Years & Terms">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Years & Terms</span>
            </a>

            <a href="{{ route('admin.divisions.index') }}" class="sidebar-link {{ request()->routeIs('admin.divisions.*') ? 'sidebar-link-active' : '' }}" title="Divisions">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Divisions</span>
            </a>

            <a href="{{ route('admin.sections.index') }}" class="sidebar-link {{ request()->routeIs('admin.sections.*') || request()->routeIs('admin.grade-levels.*') ? 'sidebar-link-active' : '' }}" title="School Structure">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>School Structure (Sections)</span>
            </a>
            
            <a href="{{ route('admin.subjects.index') }}" class="sidebar-link {{ request()->routeIs('admin.subjects.*') ? 'sidebar-link-active' : '' }}" title="Curriculum">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Curriculum</span>
            </a>
        </div>
        @endcan

        @canany(['view security', 'view maintenance'])
        <div class="sidebar-category-header flex items-center cursor-pointer group" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 backdrop-blur-md'"
             @click="toggleCategory('system')">
            <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 transition-colors">System</span>
            <span x-show="!sidebarCollapsed">
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                     :class="openCategories['system'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        
        <div x-show="openCategories['system'] || sidebarCollapsed" x-collapse>
            @can('view security')
            <a href="{{ route('admin.roles.index') }}" class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'sidebar-link-active' : '' }}" title="Security">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Security</span>
            </a>
            @endcan
            
            @can('view maintenance')
            <a href="{{ route('admin.audit-logs.index') }}" class="sidebar-link {{ request()->routeIs('admin.audit-logs.*') ? 'sidebar-link-active' : '' }}" title="Maintenance">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Maintenance</span>
            </a>
            @endcan
        </div>
        @endcanany
    </nav>
    
    <!-- User Section -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-700 flex-shrink-0 bg-slate-50/50 dark:bg-slate-800/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200 flex-shrink-0">
                <span class="text-white font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div class="flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition>
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.ui.sidebarOpen" 
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="$store.ui.sidebarOpen = false"
     class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden">
</div>
