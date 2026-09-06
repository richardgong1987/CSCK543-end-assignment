@props(['title', 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title }} · {{ config('app.name') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] flex min-h-screen flex-col items-center justify-center p-6 lg:p-8">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4 text-center">
                    <a href="{{ route('home') }}" class="font-medium">
                        {{ config('app.name') }}
                    </a>

                    <div class="space-y-2">
                        <h1 class="text-xl font-medium">{{ $title }}</h1>

                        @if ($description)
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $description }}</p>
                        @endif
                    </div>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
