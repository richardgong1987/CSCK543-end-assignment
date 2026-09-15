@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }} · {{ config('app.name') }}</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
<a
    href="#main-content"
    class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:rounded-sm focus:bg-white focus:px-4 focus:py-2 focus:shadow dark:focus:bg-[#161615]"
>
    Skip to main content
</a>

<header class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
    <nav aria-label="Main" class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-4 p-6 text-sm">
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="font-medium">
                {{ config('app.name') }}
            </a>

            <a href="{{ route('recipes.index') }}" class="underline-offset-4 hover:underline">
                Recipes
            </a>
        </div>

        <div class="flex items-center gap-4">
            @auth
                <a href="{{ route('dashboard') }}" class="underline-offset-4 hover:underline">Dashboard</a>

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
            @else
                <a href="{{ route('login') }}" class="underline-offset-4 hover:underline">Log in</a>

                <a
                    href="{{ route('register') }}"
                    class="rounded-sm border border-[#19140035] px-5 py-1.5 leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                >
                    Register
                </a>
            @endauth
        </div>
    </nav>
</header>

<main id="main-content" class="mx-auto max-w-4xl p-6 lg:p-8">
    @if (session('status'))
        {{-- role="status" lets a screen reader announce the result of the last action. --}}
        <p role="status" class="mb-6 rounded-sm border border-[#19140035] px-4 py-3 text-sm dark:border-[#3E3E3A]">
            {{ session('status') }}
        </p>
    @endif

    {{ $slot }}
</main>
<footer class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] mt-12">
    <div class="mx-auto max-w-4xl px-6 py-8 text-sm text-[#706f6c] dark:text-[#A1A09A]">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy;{{ date('Y') }} Recipe App. All rights reserved.</p>
            <p>CSK543 Group Project &middot; University of Liverpool</p>
        </div>

    </div>

</footer>
</body>
</html>
