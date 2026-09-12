@use('App\Support\Duration')

<x-layouts.app :title="$recipe->title">
    <nav aria-label="Breadcrumb" class="mb-6 text-sm">
        <a href="{{ route('recipes.index') }}" class="underline-offset-4 hover:underline">
            &larr; All recipes
        </a>
    </nav>

    <article>
        <header class="mb-8">
            <h1 class="mb-3 text-2xl font-medium">{{ $recipe->title }}</h1>

            <p class="mb-4 text-[#706f6c] dark:text-[#A1A09A]">{{ $recipe->description }}</p>

            @auth
                @if ($isFavourite)
                    <form method="POST" action="{{ route('recipes.favourite.destroy', $recipe) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="mb-4 rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100">
                            Remove favourite
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('recipes.favourite.store', $recipe) }}">
                        @csrf

                        <button type="submit"
                            class="mb-4 rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100">
                            Save favourite
                        </button>
                    </form>
                @endif
            @endauth

            @if ($recipe->categories->isNotEmpty() || $recipe->dietaryTags->isNotEmpty())
                <ul class="flex flex-wrap gap-2" aria-label="Categories and dietary information">
                    @foreach ($recipe->categories as $category)
                        <li class="rounded-full border border-[#19140035] px-3 py-0.5 text-xs dark:border-[#3E3E3A]">
                            {{ $category->name }}
                        </li>
                    @endforeach

                    @foreach ($recipe->dietaryTags as $tag)
                        <li class="rounded-full bg-[#f3f3f1] px-3 py-0.5 text-xs dark:bg-[#252523]">
                            {{ $tag->name }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </header>

        @if ($recipe->image_path)
            <img src="{{ asset($recipe->image_path) }}" alt="{{ $recipe->title }}" width="832" height="468"
                class="mb-8 aspect-video w-full rounded-lg object-cover">
        @endif

        <dl
            class="mb-10 grid grid-cols-2 gap-4 rounded-lg bg-white p-5 text-sm shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:grid-cols-3 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
            <div>
                <dt class="text-[#706f6c] dark:text-[#A1A09A]">Preparation time</dt>
                <dd>{{ $recipe->prepTimeBand->name }}</dd>
            </div>

            <div>
                <dt class="text-[#706f6c] dark:text-[#A1A09A]">Cooking time</dt>
                <dd>{{ $recipe->cookTimeBand->name }}</dd>
            </div>

            <div>
                <dt class="text-[#706f6c] dark:text-[#A1A09A]">Serves</dt>
                <dd>{{ $recipe->servingsText() }}</dd>
            </div>

            @if ($recipe->chef)
                <div>
                    <dt class="text-[#706f6c] dark:text-[#A1A09A]">Chef</dt>
                    <dd>{{ $recipe->chef->name }}</dd>
                </div>
            @endif

            @if ($recipe->cuisine)
                <div>
                    <dt class="text-[#706f6c] dark:text-[#A1A09A]">Cuisine</dt>
                    <dd>{{ $recipe->cuisine->name }}</dd>
                </div>
            @endif

            <div>
                <dt class="text-[#706f6c] dark:text-[#A1A09A]">Rating</dt>
                <dd>
                    @if ($ratingCount > 0)
                        {{ number_format($averageRating, 1) }} out of 5
                        <span aria-hidden="true">★</span>
                        <span class="text-[#706f6c] dark:text-[#A1A09A]">
                            ({{ trans_choice('{1}:count rating|[2,*]:count ratings', $ratingCount, ['count' => $ratingCount]) }})
                        </span>
                    @else
                        Not yet rated
                    @endif
                </dd>
            </div>
        </dl>

        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)]">
            <section aria-labelledby="ingredients-heading">
                <h2 id="ingredients-heading" class="mb-4 text-lg font-medium">Ingredients</h2>

                @if ($looseIngredients->isNotEmpty())
                    <ul class="mb-6 space-y-2 text-sm">
                        @foreach ($looseIngredients as $line)
                            <li>{{ $line->displayText() }}</li>
                        @endforeach
                    </ul>
                @endif

                @foreach ($recipe->ingredientSections as $section)
                    <h3 class="mt-6 mb-3 font-medium first:mt-0">{{ $section->title }}</h3>

                    <ul class="space-y-2 text-sm">
                        @foreach ($section->ingredients as $line)
                            <li>{{ $line->displayText() }}</li>
                        @endforeach
                    </ul>
                @endforeach
            </section>

            <section aria-labelledby="method-heading">
                <h2 id="method-heading" class="mb-1 text-lg font-medium">Method</h2>

                <p class="mb-4 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {{ $recipe->steps->count() }} steps, about {{ Duration::format($recipe->totalStepMinutes()) }} in
                    total
                </p>

                <ol class="space-y-5">
                    @foreach ($recipe->steps as $step)
                        <li class="flex gap-4">
                            <span
                                class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#f3f3f1] text-sm dark:bg-[#252523]"
                                aria-hidden="true">
                                {{ $step->step_number }}
                            </span>

                            <div>
                                <p class="mb-1 text-xs tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]">
                                    Step {{ $step->step_number }} · {{ $step->durationText() }}
                                </p>

                                <p class="text-sm">{{ $step->instruction }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>

        @if ($recipe->tips)
            <section aria-labelledby="tips-heading"
                class="mt-10 rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                <h2 id="tips-heading" class="mb-2 text-lg font-medium">Recipe tips</h2>

                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $recipe->tips }}</p>
            </section>
        @endif

        @if ($recipe->source_url)
            <footer
                class="mt-10 border-t border-[#e3e3e0] pt-5 text-sm text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                <p>
                    Recipe and photograph &copy; BBC Food, reproduced for coursework purposes.
                    <a href="{{ $recipe->source_url }}" rel="noopener" class="underline underline-offset-4">
                        View the original recipe
                    </a>
                </p>
            </footer>
        @endif
    </article>
</x-layouts.app>
