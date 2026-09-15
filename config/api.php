<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recipe search API rate limit
    |--------------------------------------------------------------------------
    |
    | Requests per minute each IP address may make to GET /api/recipes. Raise it
    | only for a load test against a local copy (see tests/load), which would
    | otherwise measure the limiter rather than the application.
    |
    */

    'recipe_search_per_minute' => (int) env('API_RECIPE_SEARCH_PER_MINUTE', 60),

];
