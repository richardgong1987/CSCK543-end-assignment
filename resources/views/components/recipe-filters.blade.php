@use('App\Services\RecipeSearch')
@use('App\Support\Duration')

@props(['search', 'categories', 'dietaryTags', 'cuisines', 'ingredientNames'])

{{--
    A plain GET form, so that a search is a URL: it can be bookmarked, shared and
    paged through, and it works with scripting turned off. The JavaScript in app.js
    only saves a click on the sort menu; the Search button applies it either way.
--}}
<form
    method="GET"
    action="{{ route('recipes.index') }}"
    role="search"
    aria-labelledby="search-heading"
    class="mb-8 rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
>
    <h2 id="search-heading" class="sr-only">Search and sort recipes</h2>

    <div class="grid gap-4 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_auto] md:items-end">
        <div class="grid gap-2">
            <x-input-label for="q">Search recipes</x-input-label>

            <x-text-input
                id="q"
                type="search"
                name="q"
                value="{{ $search->keyword }}"
                placeholder="Title, chef, cuisine, category or ingredient"
                aria-describedby="q-hint"
            />
        </div>

        <div class="grid gap-2">
            <x-input-label for="sort">Sort by</x-input-label>

            <x-select-input id="sort" name="sort" data-auto-submit>
                @foreach (RecipeSearch::SORTS as $key => $label)
                    <option value="{{ $key }}" @selected($search->sort === $key)>{{ $label }}</option>
                @endforeach
            </x-select-input>
        </div>

        <x-primary-button>Search</x-primary-button>
    </div>

    <p id="q-hint" class="mt-2 text-xs text-[#706f6c] dark:text-[#A1A09A]">
        Searches recipe titles and descriptions, chefs, cuisines, categories, dietary
        labels and ingredients.
    </p>

    <details class="mt-5 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]" @if ($search->isFiltered()) open @endif>
        <summary class="cursor-pointer text-sm font-medium">
            Filter by course, diet, cuisine, ingredient, time, servings and rating
        </summary>

        <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <fieldset class="grid content-start gap-2">
                <legend class="mb-2 text-sm font-medium">Course</legend>

                @foreach ($categories as $category)
                    <x-checkbox-filter
                        name="category[]"
                        :value="$category->slug"
                        :checked="in_array($category->slug, $search->categories, true)"
                    >
                        {{ $category->name }}
                    </x-checkbox-filter>
                @endforeach
            </fieldset>

            <fieldset class="grid content-start gap-2">
                <legend class="mb-2 text-sm font-medium">
                    Dietary
                    <span class="font-normal text-[#706f6c] dark:text-[#A1A09A]">(all selected must apply)</span>
                </legend>

                @foreach ($dietaryTags as $tag)
                    <x-checkbox-filter
                        name="diet[]"
                        :value="$tag->slug"
                        :checked="in_array($tag->slug, $search->diets, true)"
                    >
                        {{ $tag->name }}
                    </x-checkbox-filter>
                @endforeach
            </fieldset>

            <fieldset class="grid content-start gap-2">
                <legend class="mb-2 text-sm font-medium">Cuisine</legend>

                @foreach ($cuisines as $cuisine)
                    <x-checkbox-filter
                        name="cuisine[]"
                        :value="$cuisine->slug"
                        :checked="in_array($cuisine->slug, $search->cuisines, true)"
                    >
                        {{ $cuisine->name }}
                    </x-checkbox-filter>
                @endforeach
            </fieldset>

            <div class="grid gap-2">
                <x-input-label for="ingredient">Contains ingredient</x-input-label>

                <x-text-input
                    id="ingredient"
                    name="ingredient"
                    value="{{ $search->ingredient }}"
                    placeholder="e.g. mango"
                    list="ingredient-names"
                    autocomplete="off"
                />

                {{-- A native suggestion list, so the browser does the work rather than a script. --}}
                <datalist id="ingredient-names">
                    @foreach ($ingredientNames as $name)
                        <option value="{{ $name }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="grid gap-2">
                <x-input-label for="max_minutes">Ready in</x-input-label>

                <x-select-input id="max_minutes" name="max_minutes">
                    <option value="">Any length of time</option>

                    @foreach (RecipeSearch::TIME_LIMITS as $limit)
                        <option value="{{ $limit }}" @selected($search->maxMinutes === $limit)>
                            {{ Duration::format($limit) }} or less
                        </option>
                    @endforeach
                </x-select-input>
            </div>

            <div class="grid gap-2">
                <x-input-label for="min_servings">Serves at least</x-input-label>

                <x-select-input id="min_servings" name="min_servings">
                    <option value="">Any number of people</option>

                    @foreach (RecipeSearch::SERVING_LIMITS as $limit)
                        <option value="{{ $limit }}" @selected($search->minServings === $limit)>
                            {{ $limit }} people
                        </option>
                    @endforeach
                </x-select-input>
            </div>

            <div class="grid gap-2">
                <x-input-label for="min_rating">Rated at least</x-input-label>

                <x-select-input id="min_rating" name="min_rating">
                    <option value="">Any rating</option>

                    @foreach (RecipeSearch::RATING_LIMITS as $limit)
                        <option value="{{ $limit }}" @selected($search->minRating === $limit)>
                            {{ $limit }} out of 5 and above
                        </option>
                    @endforeach
                </x-select-input>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-4">
            <x-primary-button>Search</x-primary-button>

            @if ($search->isFiltered())
                <a
                    href="{{ route('recipes.index') }}"
                    class="text-sm underline underline-offset-4 text-[#f53003] dark:text-[#FF4433]"
                >
                    Clear all filters
                </a>
            @endif
        </div>
    </details>
</form>
