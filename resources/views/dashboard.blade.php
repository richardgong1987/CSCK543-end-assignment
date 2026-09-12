<x-layouts.app title="Dashboard">
    <div
        class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
        <h1 class="mb-1 font-medium">Dashboard</h1>

        <p class="mb-6 text-sm text-[#706f6c] dark:text-[#A1A09A]">
            You're logged in as {{ auth()->user()->email }}.
        </p>

        <h2 class="mb-4 text-lg font-medium">Saved recipes</h2>

        @if ($favouriteRecipes->isEmpty())
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                You haven't saved any recipes yet.
            </p>
        @else
            <ul class="space-y-3">
                @foreach ($favouriteRecipes as $recipe)
                    <li>
                        <a href="{{ route('recipes.show', $recipe) }}" class="underline underline-offset-4">
                            {{ $recipe->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.app>
