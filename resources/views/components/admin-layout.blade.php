@props(['header' => null])

@php
    $layoutStart = microtime(true);
    \Illuminate\Support\Facades\Log::info('AdminLayout rendering started');
    $academicYear = \App\Helpers\CachedData::activeAcademicYear();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trim(strip_tags($header)) ?: 'Dashboard' }} - {{ config('app.name', 'Renaissance SMS') }}</title>
    
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
</head>
<body class="font-sans antialiased bg-slate-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div x-data="{ mobileOpen: $store.sidebar?.open || false }" 
             x-init="$watch('mobileOpen', val => { if(window.$store && window.$store.sidebar) window.$store.sidebar.open = val })">
            @php $t = microtime(true); @endphp
            @include('layouts.partials.admin-sidebar')
            @php \Illuminate\Support\Facades\Log::info('Sidebar rendering took ' . (microtime(true) - $t)*1000 . 'ms'); @endphp
        </div>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-30">
                <!-- Left: Mobile menu + Page Title -->
                <div class="flex items-center gap-4">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen; $dispatch('toggle-sidebar')" 
                            class="lg:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    
                    @if($header)
                        <h1 class="text-lg font-semibold text-slate-900 font-heading">{{ $header }}</h1>
                    @endif
                </div>
                
                <!-- Center: Global Search -->
                <div class="hidden md:block flex-1 max-w-md mx-4 lg:mx-8"
                     x-data="{
                        query: '',
                        results: [],
                        loading: false,
                        showDropdown: false,
                        selectedIndex: -1,
                        async performSearch() {
                            const trimmedQuery = this.query.trim();
                            if (trimmedQuery.length < 2) {
                                this.results = [];
                                this.showDropdown = false;
                                return;
                            }
                            this.loading = true;
                            this.showDropdown = true;
                            try {
                                const response = await fetch(`/admin/search?q=${encodeURIComponent(trimmedQuery)}`);
                                this.results = await response.json();
                                this.selectedIndex = -1;
                            } catch (error) {
                                console.error('Search failed:', error);
                            } finally {
                                this.loading = false;
                            }
                        }
                     }">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" 
                               x-model.debounce.300ms="query"
                               @input="performSearch()"
                               @keydown.down.prevent="selectedIndex = (selectedIndex + 1) % results.length"
                               @keydown.up.prevent="selectedIndex = (selectedIndex - 1 + results.length) % results.length"
                               @keydown.enter.prevent="if(selectedIndex >= 0) window.location.href = results[selectedIndex].url"
                               @focus="showDropdown = query.trim().length >= 2"
                               @click.outside="showDropdown = false"
                               placeholder="Search students, sections..." 
                               class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border-0 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white placeholder-slate-400 transition-colors">

                        <!-- Search Results Dropdown -->
                        <div x-show="showDropdown && (results.length > 0 || loading || (query.length >= 2 && !loading))"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute top-full left-0 w-full mt-2 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden z-50">
                            
                            <div x-show="loading" class="p-4 text-center">
                                <div class="inline-block animate-spin w-4 h-4 border-2 border-indigo-600 border-t-transparent rounded-full mr-2"></div>
                                <span class="text-sm text-slate-500">Searching...</span>
                            </div>

                            <div x-show="!loading && results.length > 0" class="max-h-[70vh] overflow-y-auto">
                                <template x-for="(result, index) in results" :key="index">
                                    <a :href="result.url" 
                                       @mouseenter="selectedIndex = index"
                                       class="block px-4 py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors"
                                       :class="{ 'bg-slate-50': selectedIndex === index }">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                                <template x-if="result.type === 'Student'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </template>
                                                <template x-if="result.type === 'Section'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                    </svg>
                                                </template>
                                                <template x-if="result.type === 'Subject'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                </template>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-slate-900 truncate" x-text="result.title"></div>
                                                <div class="text-xs text-slate-500 truncate" x-text="result.subtitle"></div>
                                            </div>
                                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded" x-text="result.type"></div>
                                        </div>
                                    </a>
                                </template>
                            </div>

                            <div x-show="!loading && results.length === 0 && query.trim().length >= 2" class="p-8 text-center text-sm text-slate-500">
                                <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                No results found for "<span class="font-medium text-slate-900" x-text="query.trim()"></span>"
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 lg:gap-4">
                    @php $t = microtime(true); @endphp
                    <!-- Academic Year Badge -->
                    @if($academicYear)
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-indigo-50 rounded-lg">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm font-medium text-indigo-700">{{ $academicYear->name }}</span>
                    </div>
                    @endif
                    
                    <!-- Date Display -->
                    <div class="hidden lg:block text-sm text-slate-500">
                        {{ now()->format('M j, Y') }}
                    </div>
                    
                    <!-- Profile Dropdown -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center">
                                    <span class="text-white font-semibold text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-sm font-medium text-slate-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" 
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="flex items-center gap-2 text-rose-600 hover:bg-rose-50">
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
            <main class="flex-1 p-4 lg:p-6 overflow-auto">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 alert-success flex items-center gap-3" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition
                         x-init="setTimeout(() => show = false, 5000)">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-emerald-800">{{ session('success') }}</span>
                        <button @click="show = false" class="ml-auto text-emerald-600 hover:text-emerald-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="mb-6 alert-danger flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-rose-800">{{ session('error') }}</span>
                    </div>
                @endif
                
                {{ $slot }}
            </main>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <x-confirm-modal />
    
    <!-- Mobile sidebar toggle script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', { open: false });
        });
        
        document.addEventListener('toggle-sidebar', () => {
            const sidebar = document.querySelector('aside');
            if (sidebar) {
                const mobileOpen = sidebar.__x.$data.mobileOpen;
                sidebar.__x.$data.mobileOpen = !mobileOpen;
            }
        });
    </script>
    
    @php
        \Illuminate\Support\Facades\Log::info('Profile/Badge rendering took ' . (microtime(true) - $t)*1000 . 'ms');
        $executionTime = (microtime(true) - LARAVEL_START) * 1000;
        \Illuminate\Support\Facades\Log::info('AdminLayout rendering finished', ['execution_time' => $executionTime]);
    @endphp

    <!-- High-Precision Diagnostics Floating Badge -->
    <div class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-1 items-end pointer-events-none" x-data="{ 
        expanded: false,
        metrics: {
            render: '{{ number_format($executionTime, 1) }}ms',
            interactive: '0ms',
            total: '0ms'
        }
    }" x-init="
        window.addEventListener('load', () => {
            setTimeout(() => {
                metrics.interactive = Math.round(window.perfData.domInteractive).toLocaleString() + 'ms';
                metrics.total = Math.round(window.perfData.fullLoad).toLocaleString() + 'ms';
            }, 100);
        });
    ">
        <div class="px-4 py-2 bg-slate-900/95 text-[10px] font-black text-white rounded-xl shadow-2xl backdrop-blur-md border border-white/10 flex items-center gap-3 transition-all">
            <span class="flex items-center gap-1.5" title="Server-side PHP Generation">
                <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-pulse"></span>
                PHP: <span x-text="metrics.render"></span>
            </span>
            <span class="w-px h-3 bg-white/20"></span>
            <span class="flex items-center gap-1.5" title="Time to DOM Interactive">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                UX: <span x-text="metrics.interactive"></span>
            </span>
            <span class="w-px h-3 bg-white/20"></span>
            <span class="flex items-center gap-1.5" title="Total page load including assets">
                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                Load: <span x-text="metrics.total"></span>
            </span>
        </div>
        <div class="px-3 py-1 bg-white/10 text-[8px] font-bold text-slate-400 uppercase tracking-widest rounded-full">Optimization Engine Active</div>
    </div>

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
