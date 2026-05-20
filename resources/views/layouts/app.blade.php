<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

        <style>
            :root {
                --font-heading: 'Outfit', sans-serif;
                --font-body: 'Inter', sans-serif;
                --brand-indigo: #4f46e5;
                --brand-blue: #3b82f6;
                --brand-emerald: #10b981;
                --brand-amber: #f59e0b;
                --brand-rose: #f43f5e;
                --brand-purple: #8b5cf6;
            }
            body { 
                font-family: var(--font-body); 
                background-attachment: fixed;
            }
            h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }
            
            .glass-card {
                background: rgba(255, 255, 255, 0.75);
                backdrop-filter: blur(20px) saturate(180%);
                -webkit-backdrop-filter: blur(20px) saturate(180%);
                border: 1px solid rgba(255, 255, 255, 0.5);
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
            }
            
            .glass-nav {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            }

            .mesh-gradient {
                background-color: #f8fafc;
                background-image: 
                    radial-gradient(at 0% 0%, hsla(253,16%,93%,1) 0, transparent 50%), 
                    radial-gradient(at 50% 0%, hsla(225,39%,90%,1) 0, transparent 50%), 
                    radial-gradient(at 100% 0%, hsla(339,49%,90%,1) 0, transparent 50%),
                    radial-gradient(at 0% 100%, hsla(208,100%,92%,1) 0, transparent 50%),
                    radial-gradient(at 100% 100%, hsla(240,100%,92%,1) 0, transparent 50%);
            }

            .premium-card {
                background: white;
                border: 1px solid #f1f5f9;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }

            .premium-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 25px 30px -5px rgba(0, 0, 0, 0.08);
            }

            .vibrant-gradient-blue { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
            .vibrant-gradient-emerald { background: linear-gradient(135deg, #10b981 0%, #047857 100%); }
            .vibrant-gradient-amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
            .vibrant-gradient-purple { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
            .vibrant-gradient-rose { background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%); }
            .vibrant-gradient-slate { background: linear-gradient(135deg, #334155 0%, #1e293b 100%); }

            .hero-heading {
                background: linear-gradient(to right, #0f172a, #334155);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                letter-spacing: -0.04em;
            }

            .custom-scrollbar::-webkit-scrollbar { width: 4px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(79, 70, 229, 0.2);
                border-radius: 10px;
            }

            @keyframes pulse-intense {
                0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.2); }
                50% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
            }
            .animate-alert { animation: pulse-intense 2s infinite; }

            /* Robust Selection Styles */
            input[type="radio"]:checked + .selection-indicator {
                transform: scaleX(1) !important;
            }
            input[type="radio"]:checked ~ .relative .vibrant-icon {
                transform: scale(1.1) rotate(5deg);
                box-shadow: 0 10px 20px -5px rgba(0,0,0,0.2);
            }
            .premium-card:has(input[type="radio"]:checked) {
                border-color: #4f46e5;
                background: white;
                box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.1);
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased mesh-gradient min-h-screen">
        <div class="min-h-screen flex flex-col justify-between">
            <div>
                @include('layouts.navigation')

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Global Flash Messages -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 10000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
                                <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                            </span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
                                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Page Content -->
                <main class="relative z-10">
                    {{ $slot }}
                </main>
            </div>

            <!-- Footer -->
            <footer class="print:hidden no-print py-4 text-center bg-slate-50/50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-700">
                <p class="text-xs font-black uppercase tracking-widest">
                    © {{ date('Y') }} All Rights Reserved to Byte Tech Solutions
                </p>
            </footer>
        </div>

        <!-- Confirmation Modal -->
        <x-confirm-modal />


        <!-- Page-specific scripts -->
        @stack('scripts')
    </body>
</html>
