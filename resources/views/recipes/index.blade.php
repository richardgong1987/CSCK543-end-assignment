@use('App\Services\RecipeSearch')

<x-layouts.app title="Recipes">
    <h1 class="mb-6 text-2xl font-medium">Recipes</h1>

    <x-recipe-filters
        :$search
        :$categories
        :$dietaryTags
        :$cuisines
        :$ingredientNames
    />

    <section aria-labelledby="results-heading">
        <h2 id="results-heading" class="mb-6 text-sm text-[#706f6c] dark:text-[#A1A09A]">
            @if ($search->isFiltered())
                {{ trans_choice(
                    '{0}No recipes match your search.|{1}One recipe matches your search.|[2,*]:count recipes match your search.',
                    $recipes->total(),
                    ['count' => $recipes->total()],
                ) }}
            @else
                {{ trans_choice(
                    '{0}No recipes yet.|{1}One recipe.|[2,*]:count recipes.',
                    $recipes->total(),
                    ['count' => $recipes->total()],
                ) }}
            @endif

            @if ($recipes->isNotEmpty())
                Sorted by {{ Str::lcfirst(RecipeSearch::SORTS[$search->sort]) }}.
            @endif
        </h2>

        @if ($recipes->isEmpty())
            @if ($search->isFiltered())
                <p>
                    Nothing here matches every filter you chose. Try removing one, or
                    <a
                        href="{{ route('recipes.index') }}"
                        class="underline underline-offset-4 text-[#f53003] dark:text-[#FF4433]"
                    >start again</a>.
                </p>
            @else
                <p>There are no recipes to show. Run <code>php artisan db:seed</code> to load the sample data.</p>
            @endif
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
    </section>
</x-layouts.app>
