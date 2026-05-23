<aside x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" class="fixed inset-y-0 left-0 z-[100] bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 flex flex-col transition-all duration-300 lg:translate-x-0" :class="[ $store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'w-20' : 'w-64' ]">
    <!-- Logo -->
    <div class="h-16 flex items-center border-b border-slate-200 dark:border-slate-700 flex-shrink-0 relative transition-all duration-300" :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-6'">
        <a href="{{ route('parent.dashboard') }}" class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200 flex-shrink-0 z-10 relative">
                <span class="text-white font-bold text-sm">R</span>
            </div>
            <span x-show="!sidebarCollapsed" class="font-heading font-bold text-slate-900 dark:text-slate-100 text-lg whitespace-nowrap">Renaissance</span>
        </a>
        <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)" class="hidden lg:flex p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-indigo-600 transition-colors z-20" :class="sidebarCollapsed ? 'absolute -right-5 top-1/2 -translate-y-1/2 bg-white shadow-md border border-slate-200' : ''">
            <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
        </button>
        <button @click="$store.ui.sidebarOpen = false" class="lg:hidden p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto custom-scrollbar">
        <a href="{{ route('parent.dashboard') }}" class="sidebar-link {{ request()->routeIs('parent.dashboard') ? 'sidebar-link-active' : '' }}" title="Dashboard">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span x-show="!sidebarCollapsed" x-transition>Dashboard</span>
        </a>
        <a href="{{ route('parent.notices.index') }}" class="sidebar-link {{ request()->routeIs('parent.notices.*') ? 'sidebar-link-active' : '' }}" title="Notices">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span x-show="!sidebarCollapsed" x-transition>Notices</span>
        </a>
        <a href="{{ route('parent.contact.form') }}" class="sidebar-link {{ request()->routeIs('parent.contact.*') ? 'sidebar-link-active' : '' }}" title="Contact Teacher">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M8 21h8a2 2 0 002-2v-5a2 2 0 00-2-2h-1l-2-3h-4l-2 3H8a2 2 0 00-2 2v5a2 2 0 002 2z"/></svg>
            <span x-show="!sidebarCollapsed" x-transition>Contact Teacher</span>
        </a>
        <a href="{{ route('parent.profile') }}" class="sidebar-link {{ request()->routeIs('parent.profile') ? 'sidebar-link-active' : '' }}" title="Profile">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span x-show="!sidebarCollapsed" x-transition>Profile</span>
        </a>
    </nav>
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
<div x-show="$store.ui.sidebarOpen" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="$store.ui.sidebarOpen = false" class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden"></div>