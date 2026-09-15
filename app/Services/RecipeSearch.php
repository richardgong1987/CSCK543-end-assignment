<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The criteria behind one recipe search, and the query that answers it.
 *
 * Everything the user can influence is allow-listed as it is read: the sort key is
 * matched against a fixed list, and the numeric filters against fixed sets of
 * options. No part of a request is ever used as a column name or a sort direction,
 * and every value the query does use is bound as a parameter.
 *
 * Input that is not recognised is dropped rather than rejected. A search page opened
 * from a stale bookmark, or edited by hand, should still show recipes.
 */
final class RecipeSearch
{
    /** Sort keys offered to the user, in menu order, with the label each one shows. */
    public const SORTS = [
        'title' => 'Title (A-Z)',
        'title_desc' => 'Title (Z-A)',
        'quickest' => 'Total time: shortest first',
        'slowest' => 'Total time: longest first',
        'rating' => 'Highest rated',
        'steps' => 'Fewest steps',
        'newest' => 'Recently added',
    ];

    /** Upper bounds offered by the "ready in" filter, in minutes. */
    public const TIME_LIMITS = [30, 60, 90, 120];

    /** Lower bounds offered by the "serves at least" filter. */
    public const SERVING_LIMITS = [2, 4, 6, 8];

    /** Lower bounds offered by the average-rating filter. */
    public const RATING_LIMITS = [3, 4, 5];

    private const DEFAULT_SORT = 'title';

    /**
     * Longest time from starting to serving, as SQL over the two joined bands. It is
     * NULL where either band is open-ended, because such a recipe has no upper bound.
     */
    private const TOTAL_MAXIMUM_MINUTES = 'prep_band.max_minutes + cook_band.max_minutes';

    /**
     * @param  list<string>  $categories  category slugs
     * @param  list<string>  $diets  dietary tag slugs
     * @param  list<string>  $cuisines  cuisine slugs
     */
    private function __construct(
        public readonly string $keyword,
        public readonly string $ingredient,
        public readonly array $categories,
        public readonly array $diets,
        public readonly array $cuisines,
        public readonly ?int $maxMinutes,
        public readonly ?int $minServings,
        public readonly ?int $minRating,
        public readonly string $sort,
    ) {}

    /**
     * Build the criteria from raw query-string values, which may be missing, of the
     * wrong type, or repeated.
     *
     * @param  array<string, mixed>  $query
     */
    public static function fromQuery(array $query): self
    {
        return new self(
            keyword: self::text($query['q'] ?? null),
            ingredient: self::text($query['ingredient'] ?? null),
            categories: self::slugs($query['category'] ?? null),
            diets: self::slugs($query['diet'] ?? null),
            cuisines: self::slugs($query['cuisine'] ?? null),
            maxMinutes: self::numberIn($query['max_minutes'] ?? null, self::TIME_LIMITS),
            minServings: self::numberIn($query['min_servings'] ?? null, self::SERVING_LIMITS),
            minRating: self::numberIn($query['min_rating'] ?? null, self::RATING_LIMITS),
            sort: self::sortKey($query['sort'] ?? null),
        );
    }

    /**
     * Whether the search narrows the listing at all. The sort key is not a filter:
     * reordering every recipe still shows every recipe.
     */
    public function isFiltered(): bool
    {
        return $this->keyword !== ''
            || $this->ingredient !== ''
            || $this->categories !== []
            || $this->diets !== []
            || $this->cuisines !== []
            || $this->maxMinutes !== null
            || $this->minServings !== null
            || $this->minRating !== null;
    }

    /**
     * @return Builder<Recipe>
     */
    public function query(): Builder
    {
        $query = Recipe::query()
            // Both time bands are joined once so that the "ready in" filter and the
            // two time sorts can read their minutes directly. Selecting recipes.*
            // is required rather than tidy: time_bands has id, slug and name columns
            // of its own, which would otherwise overwrite the recipe's.
            ->select('recipes.*')
            ->join('time_bands as prep_band', 'prep_band.id', '=', 'recipes.prep_time_band_id')
            ->join('time_bands as cook_band', 'cook_band.id', '=', 'recipes.cook_time_band_id')
            ->withCardDetails()
            ->withCount('steps');

        $this->applyKeyword($query);
        $this->applyFilters($query);
        $this->applySort($query);

        return $query;
    }

    /**
     * One box searching everything a cook might remember about a recipe: its name,
     * its description, the chef, the cuisine, its categories and its ingredients.
     *
     * The normalised tables are searched directly rather than through a derived
     * search-text column, so there is no second copy of the data to keep in step.
     *
     * @param  Builder<Recipe>  $query
     */
    private function applyKeyword(Builder $query): void
    {
        if ($this->keyword === '') {
            return;
        }

        $like = '%'.$this->keyword.'%';

        $query->where(function (Builder $query) use ($like): void {
            $query->where('recipes.title', 'like', $like)
                ->orWhere('recipes.description', 'like', $like)
                ->orWhereHas('chef', fn (Builder $chef) => $chef->where('name', 'like', $like))
                ->orWhereHas('cuisine', fn (Builder $cuisine) => $cuisine->where('name', 'like', $like))
                ->orWhereHas('categories', fn (Builder $category) => $category->where('categories.name', 'like', $like))
                ->orWhereHas('dietaryTags', fn (Builder $tag) => $tag->where('dietary_tags.name', 'like', $like))
                ->orWhereHas('ingredients.ingredient', fn (Builder $i) => $i->where('ingredients.name', 'like', $like));
        });
    }

    /**
     * @param  Builder<Recipe>  $query
     */
    private function applyFilters(Builder $query): void
    {
        if ($this->ingredient !== '') {
            $query->whereHas(
                'ingredients.ingredient',
                fn (Builder $i) => $i->where('ingredients.name', 'like', '%'.$this->ingredient.'%'),
            );
        }

        // Any of the chosen courses: someone browsing starters and desserts wants both.
        if ($this->categories !== []) {
            $query->whereHas(
                'categories',
                fn (Builder $category) => $category->whereIn('categories.slug', $this->categories),
            );
        }

        // All of the chosen dietary tags, because each one narrows what the cook may
        // eat. "Vegan" plus "nut-free" has to satisfy both to be a useful answer.
        foreach ($this->diets as $slug) {
            $query->whereHas('dietaryTags', fn (Builder $tag) => $tag->where('dietary_tags.slug', $slug));
        }

        if ($this->cuisines !== []) {
            $query->whereHas('cuisine', fn (Builder $cuisine) => $cuisine->whereIn('cuisines.slug', $this->cuisines));
        }

        // Recipes whose prep and cooking bands together stay inside the limit. An
        // open-ended band ("overnight") has a NULL maximum and so drops out, which is
        // the honest answer to "ready in 30 minutes".
        if ($this->maxMinutes !== null) {
            $query->where(DB::raw(self::TOTAL_MAXIMUM_MINUTES), '<=', $this->maxMinutes);
        }

        if ($this->minServings !== null) {
            $query->where('recipes.servings_max', '>=', $this->minServings);
        }

        if ($this->minRating !== null) {
            // Averaged over every rating the recipe has; an unrated recipe yields NULL
            // and fails the comparison, which is why it is not returned here.
            $query->whereRaw(
                '(select avg(overall) from ratings where ratings.recipe_id = recipes.id) >= ?',
                [$this->minRating],
            );
        }
    }

    /**
     * @param  Builder<Recipe>  $query
     */
    private function applySort(Builder $query): void
    {
        match ($this->sort) {
            'title' => $query->orderBy('recipes.title'),
            'title_desc' => $query->orderByDesc('recipes.title'),
            // A recipe with an open-ended band has no total, so it sorts last when the
            // cook asked for the quickest and first when they asked for the longest.
            'quickest' => $query->orderByRaw(self::TOTAL_MAXIMUM_MINUTES.' is null')
                ->orderBy(DB::raw(self::TOTAL_MAXIMUM_MINUTES)),
            'slowest' => $query->orderByRaw(self::TOTAL_MAXIMUM_MINUTES.' is not null')
                ->orderByDesc(DB::raw(self::TOTAL_MAXIMUM_MINUTES)),
            'rating' => $query->orderByRaw('average_rating is null')->orderByDesc('average_rating'),
            'steps' => $query->orderBy('steps_count'),
            'newest' => $query->orderByDesc('recipes.created_at'),
        };

        // A stable tie-break, so that paging never repeats or skips a recipe.
        $query->orderBy('recipes.id');
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    /**
     * @return list<string>
     */
    private static function slugs(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_values(array_unique(array_filter(
            $values,
            fn (mixed $slug): bool => is_string($slug) && $slug !== '',
        )));
    }

    /**
     * @param  list<int>  $allowed
     */
    private static function numberIn(mixed $value, array $allowed): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return in_array((int) $value, $allowed, strict: true) ? (int) $value : null;
    }

    private static function sortKey(mixed $value): string
    {
        return is_string($value) && array_key_exists($value, self::SORTS)
            ? $value
            : self::DEFAULT_SORT;
    }
}
