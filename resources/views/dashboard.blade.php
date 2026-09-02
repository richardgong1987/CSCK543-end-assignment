<x-layouts.app title="Dashboard">
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
        <h1 class="mb-1 font-medium">Dashboard</h1>

        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
            You're logged in as {{ auth()->user()->email }}.
        </p>
    </div>
</x-layouts.app>
