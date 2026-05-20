<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 justify-between">
            <div class="flex-grow flex flex-col sm:justify-center items-center w-full">
                <div>
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>
                </div>

                <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg mb-8">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <footer class="print:hidden no-print w-full py-4 text-center bg-slate-50/50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-700">
                <p class="text-xs font-black uppercase tracking-widest">
                    © {{ date('Y') }} All Rights Reserved to Byte Tech Solutions
                </p>
            </footer>
        </div>
    </body>
</html>
