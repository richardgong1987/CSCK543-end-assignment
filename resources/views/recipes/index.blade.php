<x-layouts.app title="Recipes">
    <div class="mb-8">
        <h1 class="mb-2 text-2xl font-medium">Recipes</h1>

        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
            {{ trans_choice('{0}No recipes yet.|{1}One recipe.|[2,*]:count recipes.', $recipes->total(), ['count' => $recipes->total()]) }}
        </p>
    </div>

    @if ($recipes->isEmpty())
        <p>There are no recipes to show. Run <code>php artisan db:seed</code> to load the sample data.</p>
    @else
        <ul role="list" class="grid gap-6 sm:grid-cols-2">
            @foreach ($recipes as $recipe)
                <li class="flex">
                    <x-recipe-card :$recipe />
                </li>
            @endforeach
        </ul>

        <div class="mt-8">
            {{ $recipes->links() }}
        </div>
    @endif
</x-layouts.app>
