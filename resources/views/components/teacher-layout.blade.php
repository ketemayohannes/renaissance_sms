@props(['header' => null])

@php
    $academicYear = \App\Helpers\CachedData::activeAcademicYear();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<script>
    if (localStorage.getItem('darkMode') === 'true') {
        document.documentElement.classList.add('dark');
    }
</script>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trim(strip_tags((string)$header)) ?: 'Teacher Portal' }} - {{ config('app.name', 'Renaissance SMS') }}</title>
    
    <!-- Resource Hints -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="/favicon.ico?v=1.0">
    
    <!-- Asset Preloading (Performance) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .font-heading { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>

    <!-- Performance Monitoring Baseline -->
    <script>
        window.perfData = {
            startTime: performance.now(),
            domInteractive: 0,
            fullLoad: 0
        };
        window.addEventListener('DOMContentLoaded', () => window.perfData.domInteractive = performance.now());
        window.addEventListener('load', () => window.perfData.fullLoad = performance.now());
    </script>
    
    <!-- Alpine Store Initialization (MUST be in head before body renders) -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('ui', {
                sidebarOpen: false,
                modal: {
                    sibling: false,
                    reportCard: false
                },
                openModal(name) { this.modal[name] = true; },
                closeModal(name) { this.modal[name] = false; },
                toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@5.16.0/dist/apexcharts.min.js"
            integrity="sha384-ev+0gnMiCR/xVXDO/DIRntnh/SqpByfMpm3uB8SFR/IkPnsSdZHnyi/NDdUZUXOv"
            crossorigin="anonymous"></script>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-300" x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        @include('layouts.partials.teacher-sidebar')
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300"
             :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'">
            <!-- Top Header -->
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-[90] transition-colors duration-300">
                <!-- Left: Mobile menu + Page Title -->
                <div class="flex items-center gap-4">
                    <!-- Mobile menu button -->
                    <button @click="$store.ui.toggleSidebar()" 
                            class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    
                    @if($header)
                        <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100 font-heading">{{ $header }}</h1>
                    @endif
                </div>
                
                <div class="flex items-center gap-3 lg:gap-4 ml-auto">
                    <!-- Academic Year Badge -->
                    @if($academicYear)
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/50 rounded-lg">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">{{ $academicYear->name }}</span>
                    </div>
                    @endif
                    
                    <!-- Date Display -->
                    <div class="hidden lg:block text-sm text-slate-500 dark:text-slate-400">
                        {{ now()->format('M j, Y') }}
                    </div>

                    <!-- Dark Mode Toggle -->
                    <button 
                        x-data="{ dark: document.documentElement.classList.contains('dark') }"
                        @click="
                            dark = !dark;
                            document.documentElement.classList.toggle('dark', dark);
                            localStorage.setItem('darkMode', dark);
                        "
                        class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition-colors"
                        title="Toggle Dark Mode">
                        <svg x-show="dark" x-cloak class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                    
                    <!-- Notification Bell -->
                    <x-notification-bell />
                    
                    <!-- Profile Dropdown -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center">
                                    <span class="text-white font-semibold text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" 
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="flex items-center gap-2 text-rose-600 dark:text-rose-455 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="flex-1 p-4 lg:p-6 overflow-auto transition-colors duration-300">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 flex items-center gap-3" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition
                         x-init="setTimeout(() => show = false, 5000)">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-emerald-800 dark:text-emerald-300 font-medium">{{ session('success') }}</span>
                        <button @click="show = false" class="ml-auto text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="mb-6 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-850 rounded-xl p-4 flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-450 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-rose-800 dark:text-rose-300 font-medium">{{ session('error') }}</span>
                    </div>
                @endif
                
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="print:hidden no-print mt-auto py-4 text-center bg-slate-50/50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-700">
                <p class="text-xs font-black uppercase tracking-widest">
                    © {{ date('Y') }} All Rights Reserved to Byte Tech Solutions
                </p>
            </footer>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <x-confirm-modal />

    <!-- Page-specific modals -->
    @stack('modals')

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
