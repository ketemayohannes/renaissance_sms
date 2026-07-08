<aside 
       x-data="{ 
           openCategories: JSON.parse(localStorage.getItem('studentOpenCategories')) || { 
               'academic': true
           },
           toggleCategory(key) {
               this.openCategories[key] = !this.openCategories[key];
               localStorage.setItem('studentOpenCategories', JSON.stringify(this.openCategories));
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
        <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200 flex-shrink-0 z-10 relative">
                <span class="text-white font-bold text-sm">R</span>
            </div>
            <span x-show="!sidebarCollapsed" 
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 -translate-x-2"
                  x-transition:enter-end="opacity-100 translate-x-0"
                  class="font-heading font-bold text-slate-900 dark:text-slate-100 text-lg whitespace-nowrap">Renaissance</span>
        </a>

        <!-- Desktop Collapse Toggle -->
        <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)"
                class="hidden lg:flex p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-indigo-600 transition-colors z-20"
                :class="sidebarCollapsed ? 'absolute -right-5 top-1/2 -translate-y-1/2 bg-white shadow-md border border-slate-200 dark:bg-slate-800 dark:border-slate-700' : ''">
            <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>

        <!-- Mobile close button -->
        <button @click="$store.ui.sidebarOpen = false" class="lg:hidden p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto custom-scrollbar"
         x-on:scroll.throttle.50ms="$el.querySelectorAll('.sidebar-category-header').forEach(h => h.classList.toggle('is-stuck', h.offsetTop <= $el.scrollTop))">
        
        <!-- Overview Category Header -->
        <div class="sidebar-category-header" 
             :class="sidebarCollapsed ? 'text-center px-0 flex justify-center w-full' : 'sticky top-0 z-10 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md'">
            <span x-show="!sidebarCollapsed" class="uppercase tracking-widest font-black text-[10px] text-slate-400">Overview</span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>

        <!-- Dashboard Link -->
        <a href="{{ route('student.dashboard') }}" class="sidebar-link {{ request()->routeIs('student.dashboard') ? 'sidebar-link-active' : '' }}" title="Dashboard">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition>Dashboard</span>
        </a>

        <!-- Academics Category Header -->
        <div class="sidebar-category-header flex items-center cursor-pointer group mt-4" 
             :class="sidebarCollapsed ? 'text-center px-0 justify-center w-full' : 'justify-between sticky top-0 z-10 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md'"
             @click="toggleCategory('academic')">
            <span x-show="!sidebarCollapsed" class="group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors uppercase tracking-widest font-black text-[10px] text-slate-400">Academics</span>
            <span x-show="!sidebarCollapsed">
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                     :class="openCategories['academic'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
            <span x-show="sidebarCollapsed">•••</span>
        </div>
        
        <div x-show="openCategories['academic'] || sidebarCollapsed" x-collapse>
            <!-- My Grades -->
            <a href="{{ route('student.grades.index') }}" class="sidebar-link {{ request()->routeIs('student.grades.*') ? 'sidebar-link-active' : '' }}" title="My Grades">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>My Grades</span>
            </a>

            <!-- Activities -->
            <a href="{{ route('student.activities.index') }}" class="sidebar-link {{ request()->routeIs('student.activities.*') ? 'sidebar-link-active' : '' }}" title="Activities & Homework">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Activities & Tasks</span>
            </a>

            <!-- Library -->
            @can('view library')
            <a href="{{ route('student.library.index') }}" class="sidebar-link {{ request()->routeIs('student.library.*') ? 'sidebar-link-active' : '' }}" title="Library">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>Library</span>
            </a>
            @endcan

            <!-- My Profile -->
            <a href="{{ route('student.profile') }}" class="sidebar-link {{ request()->routeIs('student.profile') ? 'sidebar-link-active' : '' }}" title="My Profile">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition>My Profile</span>
            </a>
        </div>
    </nav>
    
    <!-- User Section -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-700 flex-shrink-0 bg-slate-50/50 dark:bg-slate-800/30">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200 dark:shadow-none flex-shrink-0">
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
