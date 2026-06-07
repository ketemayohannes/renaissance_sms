@props(['header' => null])

@php
    $academicYear = \App\Helpers\CachedData::activeAcademicYear();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<script>
    // Prevent flash: apply dark mode class before paint
    if (localStorage.getItem('darkMode') === 'true') {
        document.documentElement.classList.add('dark');
    }
</script>
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

        window.copyToClipboard = function(text, el = null) {
            console.log('copyToClipboard called with:', text);
            const fallback = (text) => {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-9999px";
                textArea.style.top = "0";
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                let successful = false;
                try {
                    successful = document.execCommand('copy');
                } catch (err) {
                    console.error('Fallback copy failed', err);
                }
                document.body.removeChild(textArea);
                return successful;
            };

            const doCopy = async () => {
                if (navigator.clipboard) {
                    try {
                        await navigator.clipboard.writeText(text);
                        console.log('Clipboard API success');
                        return true;
                    } catch (err) {
                        console.warn('Clipboard API failed, trying fallback', err);
                        return fallback(text);
                    }
                } else {
                    console.warn('Clipboard API not available, trying fallback');
                    return fallback(text);
                }
            };

            doCopy().then((result) => {
                if (result && el) {
                    const originalText = el.innerText;
                    el.innerText = 'Copied!';
                    el.classList.add('bg-emerald-500', 'text-white');
                    setTimeout(() => {
                        el.innerText = originalText;
                        el.classList.remove('bg-emerald-500', 'text-white');
                    }, 2000);
                } else if (!result) {
                    alert('Unable to copy. Please copy manually: ' + text);
                }
            });
        };
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-300" x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        @include('layouts.partials.admin-sidebar')
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300"
             :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'">
            <!-- Top Header -->
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-[90] transition-colors duration-300">
                <!-- Left: Mobile menu + Page Title -->
                <div class="flex items-center gap-4">
                    <!-- Mobile menu button -->
                    <button @click="$store.ui.toggleSidebar()" 
                            class="lg:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    
                    @if($header)
                        <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100 font-heading">{{ $header }}</h1>
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
                      }" x-init="query = ''">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" 
                               x-model.debounce.300ms="query"
                               @input.stop="performSearch()"
                               @keydown.down.prevent="selectedIndex = (selectedIndex + 1) % results.length"
                               @keydown.up.prevent="selectedIndex = (selectedIndex - 1 + results.length) % results.length"
                               @keydown.enter.prevent="if(selectedIndex >= 0) window.location.href = results[selectedIndex].url"
                               @focus="showDropdown = query.trim().length >= 2"
                               @click.outside="showDropdown = false"
                               placeholder="Search students, sections..." 
                               autocomplete="off"
                               class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border-0 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white dark:focus:bg-slate-600 placeholder-slate-400 dark:placeholder-slate-500 transition-colors">

                        <!-- Search Results Dropdown -->
                        <div x-cloak x-show="showDropdown && (results.length > 0 || loading || (query.length >= 2 && !loading))"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute top-full left-0 w-full mt-2 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden z-[100]">
                            
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
                        <!-- Sun Icon (shown in dark mode) -->
                        <svg x-show="dark" x-cloak class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <!-- Moon Icon (shown in light mode) -->
                        <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                    
                    <!-- Notification Bell -->
                    <x-notification-bell />
                    
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
            <main class="flex-1 p-4 lg:p-6 overflow-auto transition-colors duration-300">
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
