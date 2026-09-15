@use('App\Support\Duration')

<x-layouts.app title="Find something to cook">
    <section class="mb-12">
        <h1 class="mb-2 text-3xl font-medium">Find something to cook</h1>

        <p class="mb-6 text-[#706f6c] dark:text-[#A1A09A]">
            {{ $recipeCount }} recipes, searchable by title, ingredient, course, diet,
            cuisine, time and rating.
        </p>

        {{-- The same search the listing page runs; this is a shortcut into it. --}}
        <form
            method="GET"
            action="{{ route('recipes.index') }}"
            role="search"
            class="flex flex-col gap-3 sm:flex-row"
        >
            <label for="home-search" class="sr-only">Search recipes</label>

            <x-text-input
                id="home-search"
                type="search"
                name="q"
                placeholder="Try 'pizza', 'mango' or 'vegetarian'"
            />

            <x-primary-button class="shrink-0">Search</x-primary-button>
        </form>

        <p class="mt-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
            Or
            <a
                href="{{ route('recipes.index') }}"
                class="underline underline-offset-4 text-[#d32903] dark:text-[#FF4433]"
            >browse all {{ $recipeCount }} recipes</a>.
        </p>
    </section>
    {{-- Hero Section --}}
    <section class="mb-10">
        <img
            src="{{ asset('images/recipes/mushroom-doner.jpg')}}"
            alt="A colorful picture of delicious mushroom doner"
            class="max-h-80 w-full rounded-lg object-cover shadow-md"
        >
    </section>

    <section aria-labelledby="courses-heading" class="mb-10">
        <h2 id="courses-heading" class="mb-3 text-lg font-medium">Browse by course</h2>

        <ul role="list" class="flex flex-wrap gap-2">
            @foreach ($categories->where('recipes_count', '>', 0) as $category)
                <li>
                    <a
                        href="{{ route('recipes.index', ['category' => [$category->slug]]) }}"
                        class="inline-block rounded-full border border-[#19140035] px-4 py-1.5 text-sm hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                    >
                        {{ $category->name }}
                        <span class="text-[#706f6c] dark:text-[#A1A09A]">({{ $category->recipes_count }})</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section aria-labelledby="diets-heading" class="mb-10">
        <h2 id="diets-heading" class="mb-3 text-lg font-medium">Browse by dietary need</h2>

        <ul role="list" class="flex flex-wrap gap-2">
            @foreach ($dietaryTags->where('recipes_count', '>', 0) as $tag)
                <li>
                    <a
                        href="{{ route('recipes.index', ['diet' => [$tag->slug]]) }}"
                        class="inline-block rounded-full bg-[#f3f3f1] px-4 py-1.5 text-sm hover:bg-[#e9e9e6] dark:bg-[#252523] dark:hover:bg-[#33332f]"
                    >
                        {{ $tag->name }}
                        <span class="text-[#706f6c] dark:text-[#A1A09A]">({{ $tag->recipes_count }})</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section aria-labelledby="shortcuts-heading" class="mb-12">
        <h2 id="shortcuts-heading" class="mb-3 text-lg font-medium">Or start from what you have</h2>

        <ul role="list" class="grid gap-3 sm:grid-cols-2">
            @php
                // Each shortcut is a search the listing page can answer on its own.
                $shortcuts = [
                    ['On the table within ' . Duration::format(60), ['max_minutes' => 60]],
                    ['The best rated recipes', ['sort' => 'rating']],
                    ['The fewest steps to follow', ['sort' => 'steps']],
                    ['Enough to feed eight', ['min_servings' => 8]],
                ];
            @endphp

            @foreach ($shortcuts as [$label, $query])
                <li>
                    <a
                        href="{{ route('recipes.index', $query) }}"
                        class="block rounded-lg bg-white p-4 text-sm shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] hover:shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.32)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] dark:hover:shadow-[inset_0px_0px_0px_1px_#fffaed55]"
                    >
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    @if ($featured->isNotEmpty())
        <section aria-labelledby="featured-heading" class="mb-12">
            <h2 id="featured-heading" class="mb-4 text-lg font-medium">Highest rated right now</h2>

            <ul role="list" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $recipe)
                    <li class="flex">
                        <x-recipe-card :$recipe />
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @guest
        <section class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
            <h2 class="mb-1 font-medium">Keep the ones you like</h2>

            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                <a
                    href="{{ route('register') }}"
                    class="underline underline-offset-4 text-[#d32903] dark:text-[#FF4433]"
                >Create an account</a>
                to save recipes to your own list and rate the ones you have cooked. Browsing and
                searching need no account at all.
            </p>
        </section>
    @endguest
</x-layouts.app>
