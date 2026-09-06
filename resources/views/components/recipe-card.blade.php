@props(['recipe'])

@php
    // Supplied by the listing query's withAvg/withCount; absent when a card is
    // rendered from a recipe loaded without them.
    $averageRating = $recipe->average_rating ?? null;
@endphp

<article class="flex h-full flex-col overflow-hidden rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
    @if ($recipe->image_path)
        <img
            src="{{ asset($recipe->image_path) }}"
            alt="{{ $recipe->title }}"
            width="832"
            height="468"
            loading="lazy"
            class="aspect-video w-full object-cover"
        >
    @endif

    <div class="flex flex-1 flex-col gap-3 p-5">
        <h2 class="text-base font-medium">
            <a
                href="{{ route('recipes.show', $recipe) }}"
                class="underline-offset-4 hover:underline focus:underline"
            >
                {{ $recipe->title }}
            </a>
        </h2>

        <p class="flex-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
            {{ Str::limit($recipe->description, 120) }}
        </p>

        <dl class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
            <div class="flex gap-1">
                <dt>Prep:</dt>
                <dd>{{ $recipe->prepTimeBand->name }}</dd>
            </div>

            <div class="flex gap-1">
                <dt>Cook:</dt>
                <dd>{{ $recipe->cookTimeBand->name }}</dd>
            </div>

            @if ($averageRating)
                <div class="flex gap-1">
                    <dt>Rating:</dt>
                    <dd>
                        {{ number_format($averageRating, 1) }} out of 5
                        <span aria-hidden="true">★</span>
                        ({{ $recipe->ratings_count }})
                    </dd>
                </div>
            @endif
        </dl>

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
    </div>
</article>
