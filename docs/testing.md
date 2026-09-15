# Testing

## Running the tests

```sh
composer test
```

The tests run against an in-memory SQLite database, so they never read or write your
local MySQL data, and you do not need to reset anything afterwards.

### Running the end-to-end tests

```sh
pnpm run build
pnpm run test:e2e
```

Playwright drives your installed Google Chrome through whole journeys in `tests/e2e`.
It starts its own PHP server on port 8123 against `database/e2e.sqlite`, rebuilt from
the migrations and seeders on every run, so these never touch your MySQL data either.
They load the built assets, so build first; a running Vite dev server is ignored. When a
test fails, its trace is kept in `test-results/` — open it with
`pnpm exec playwright show-trace <path>`.

## What each test file covers

| File                                       | Covers                                                     |
| ------------------------------------------ | ------------------------------------------------------------ |
| `tests/Feature/Auth/RegistrationTest.php`  | Registering, duplicate emails, password confirmation, hashing |
| `tests/Feature/Auth/AuthenticationTest.php` | Logging in and out, wrong passwords, throttling, guarded pages |
| `tests/Feature/RecipeSearchTest.php`       | Every search criterion, every sort, and the search page       |
| `tests/Unit/RecipeSearchCriteriaTest.php`  | The allow-list that stands between a URL and the SQL          |
| `tests/Feature/RecipePageTest.php`         | The listing and the recipe detail page                        |
| `tests/Feature/FavouriteTest.php`          | Saving and removing favourites, guests, the dashboard list    |
| `tests/Feature/RatingTest.php`             | Rating and re-rating a recipe, score validation, the form     |
| `tests/Feature/DashboardTest.php`          | The account page: details, saved recipes, the user's ratings  |
| `tests/Feature/AccountTest.php`            | Account settings: details, password change, deleting the account, error bags |
| `tests/Feature/Auth/PasswordResetTest.php` | Reset links, the identical reply for unknown addresses, rate limiting, used, expired and mismatched tokens |
| `tests/Feature/Api/RecipeSearchApiTest.php` | The JSON search: its shape, matching the listing's results and order, no user data, bad input, 404 and rate limiting |
| `tests/Feature/SecurityHeadersTest.php`    | The headers on every kind of response, nonces on every script and style tag, HSTS only over HTTPS |
| `tests/Feature/QueryBudgetTest.php`        | A query budget for each main page, so a page that starts running more queries fails |
| `tests/e2e/journey.spec.js`                | In Chrome: register, log in, search, open a recipe, save it, find it on the account page, log out; rating with the stars |
| `tests/e2e/keyboard.spec.js`               | In Chrome, keyboard only: the skip link, and the same journey with Tab, arrow keys and Enter |
| `tests/load/recipe-search-api.js`          | k6 load and stress scenarios for the JSON search (run by hand; see docs/deployment.md) |
| `tests/Feature/HomePageTest.php`           | The home page and its links                                   |
| `tests/Feature/RecipeSchemaTest.php`       | Relationships, constraints and cascading deletes              |
| `tests/Unit/DurationTest.php`, `tests/Unit/IngredientLineTest.php` | Time and ingredient formatting        |

## Performance, load testing and CI

- Lighthouse scores, page weight, response times and the query budgets are recorded in
  [status.md](status.md#testing-proposal-7).
- The k6 load and stress script for the JSON API, and how to run it safely, are in
  [deployment.md](deployment.md#5-load-and-stress-testing-the-json-api-73).
- `.github/workflows/tests.yml` runs `composer test`, the end-to-end tests and the
  dependency audits on every push and pull request.
