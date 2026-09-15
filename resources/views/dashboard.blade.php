<x-layouts.app title="Your account">
    <h1 class="mb-8 text-2xl font-medium">Your account</h1>

    <section aria-labelledby="details-heading"
        class="mb-10 rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
        <h2 id="details-heading" class="mb-4 text-lg font-medium">Your details</h2>

        <dl class="grid gap-4 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-[#706f6c] dark:text-[#A1A09A]">Name</dt>
                <dd>{{ $user->name }}</dd>
            </div>

            <div>
                <dt class="text-[#706f6c] dark:text-[#A1A09A]">Email address</dt>
                <dd class="break-all">{{ $user->email }}</dd>
            </div>

            <div>
                <dt class="text-[#706f6c] dark:text-[#A1A09A]">Member since</dt>
                <dd>{{ $user->created_at->format('j F Y') }}</dd>
            </div>
        </dl>

        <p class="mt-4 text-sm">
            <a href="{{ route('account.edit') }}" class="underline underline-offset-4">
                Edit your details, change your password or delete your account
            </a>
        </p>
    </section>

    <section aria-labelledby="saved-heading" class="mb-10">
        <h2 id="saved-heading" class="mb-4 text-lg font-medium">Saved recipes</h2>

        @if ($favouriteRecipes->isEmpty())
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                You haven't saved any recipes yet.
                <a href="{{ route('recipes.index') }}" class="underline underline-offset-4">Browse recipes</a>
                and choose "Save favourite" on any you want to keep.
            </p>
        @else
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($favouriteRecipes as $recipe)
                    <li>
                        <x-recipe-card :$recipe />
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section aria-labelledby="ratings-heading">
        <h2 id="ratings-heading" class="mb-4 text-lg font-medium">Your ratings</h2>

        @if ($ratings->isEmpty())
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                You haven't rated any recipes yet. Each recipe page has a rating form at the bottom.
            </p>
        @else
            <ul
                class="divide-y divide-[#e3e3e0] rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:divide-[#3E3E3A] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                @foreach ($ratings as $rating)
                    <li class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2 p-4">
                        <a href="{{ route('recipes.show', $rating->recipe) }}" class="font-medium underline-offset-4 hover:underline">
                            {{ $rating->recipe->title }}
                        </a>

                        <dl class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            @foreach (['overall' => 'Overall', 'taste' => 'Taste', 'difficulty' => 'Difficulty', 'appearance' => 'Appearance'] as $field => $label)
                                {{-- The three facets are optional; a skipped one is left out rather than shown as blank. --}}
                                @continue($rating->{$field} === null)

                                <div class="flex gap-1">
                                    <dt>{{ $label }}:</dt>
                                    <dd>{{ $rating->{$field} }} out of 5</dd>
                                </div>
                            @endforeach
                        </dl>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>
