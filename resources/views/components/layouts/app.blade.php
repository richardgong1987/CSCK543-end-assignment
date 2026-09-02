@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title }} · {{ config('app.name', 'Laravel') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
        <header class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
            <nav class="mx-auto flex max-w-4xl items-center justify-between gap-4 p-6 text-sm">
                <a href="{{ route('home') }}" class="font-medium">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <div class="flex items-center gap-4">
                    <span class="text-[#706f6c] dark:text-[#A1A09A]">{{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                        >
                            Log out
                        </button>
                    </form>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-4xl p-6 lg:p-8">
            {{ $slot }}
        </main>
    </body>
</html>
