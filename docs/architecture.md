# How the code is organised

Every URL the application answers is declared in `routes/web.php`, apart from the one
JSON endpoint in `routes/api.php`. Start there, then follow the controller.

| URL                                | Who can reach it | Handled by                                                      |
| ---------------------------------- | ---------------- | --------------------------------------------------------------- |
| `/`                                | anyone           | `HomeController@index`                                          |
| `/recipes`                         | anyone           | `RecipeController@index` — the listing and search               |
| `/recipes/{slug}`                  | anyone           | `RecipeController@show` — one recipe                            |
| `/register`                        | guests           | `Auth\RegisteredUserController`                                 |
| `/login`                           | guests           | `Auth\AuthenticatedSessionController`                           |
| `POST /logout`                     | signed in        | `Auth\AuthenticatedSessionController@destroy`                   |
| `/dashboard`                       | signed in        | `DashboardController@index` — the account page                  |
| `POST /recipes/{slug}/favourite`   | signed in        | `FavouriteController@store` — save a favourite                  |
| `DELETE /recipes/{slug}/favourite` | signed in        | `FavouriteController@destroy` — remove a favourite              |
| `PUT /recipes/{slug}/rating`       | signed in        | `RatingController@update` — create or replace the user's rating |
| `/account`                         | signed in        | `AccountController@edit` — account settings                     |
| `PATCH /account`                   | signed in        | `AccountController@update` — change name and email              |
| `PUT /account/password`            | signed in        | `AccountPasswordController@update` — change the password        |
| `DELETE /account`                  | signed in        | `AccountController@destroy` — delete the account                |
| `/forgot-password`                 | guests           | `Auth\PasswordResetLinkController@create` — ask for a reset link |
| `POST /forgot-password`            | guests           | `Auth\PasswordResetLinkController@store` — email the link       |
| `/reset-password/{token}`          | guests           | `Auth\NewPasswordController@create` — the page the link opens   |
| `POST /reset-password`             | guests           | `Auth\NewPasswordController@store` — set the new password       |
| `/privacy`                         | anyone           | `routes/web.php` renders `privacy.blade.php` — the privacy notice |
| `GET /api/recipes`                 | anyone           | `Api\RecipeSearchController` — the JSON search, in `routes/api.php` |

## Layout and navigation

| File                                              | What it is                                                                              |
| ------------------------------------------------- | --------------------------------------------------------------------------------------- |
| `resources/views/components/layouts/app.blade.php` | The shell **every** page uses, the login and registration pages included: skip link, header, nav, `<main>`, footer. The nav shows the app name, a Recipes link, and either Log in / Register or a "Your account" link, the user's name and a Log out button. Above each page's content it shows the `status` flash message that actions such as saving a favourite or a rating send back, in a `role="status"` paragraph so screen readers announce it. The footer carries the copyright line, the module credit and a link to the privacy notice. Each page can pass a `description` for the `<meta name="description">` tag; recipe pages use their own. |
| `resources/views/components/layouts/auth.blade.php` | The narrow centred card the auth pages used to use. **Nothing references it any more** — delete it, or move the auth pages back onto it; leaving a second layout that no page opts into only misleads the next reader. |

A page opts into a layout with `<x-layouts.app title="...">`.

`app.blade.php` puts `title` in `<title>` and nothing else, so **each page supplies its own
`<h1>`** — the old auth layout rendered one for you, and login and register now write their own.
Keep it to exactly one per page.

## Home page — `/`

| File                                     | What it does                                                     |
| ---------------------------------------- | ---------------------------------------------------------------- |
| `app/Http/Controllers/HomeController.php` | Counts the recipes, picks the three highest rated, loads the courses and dietary labels with their recipe counts. |
| `resources/views/home.blade.php`          | The page itself.                                                 |

Every link on it is a recipe search: a search box, one chip per course and per dietary
label, four shortcuts ("On the table within 1 hr", "The best rated recipes", and so
on), and three recipe cards. Courses and labels with no recipes are left out, so no
link leads to an empty page.

## Recipe listing, search and sorting — `/recipes`

**This is where the search and sorting live.**

| File                                                 | What it does                                                    |
| ---------------------------------------------------- | ---------------------------------------------------------------- |
| `app/Services/RecipeSearch.php`                      | **All of the search and sorting logic.** Reads the query string, allow-lists it, and builds the Eloquent query. |
| `app/Http/Controllers/RecipeController.php` (`index`) | Hands the query string to `RecipeSearch` and paginates the result. |
| `resources/views/recipes/index.blade.php`             | The results page: the summary line, the grid of cards, the paging links. |
| `resources/views/components/recipe-filters.blade.php` | The search and filter form.                                     |
| `resources/views/components/recipe-card.blade.php`    | One recipe as a card; the whole card links to the recipe. Shared with the home page and the account page. Load recipes for it with `Recipe::withCardDetails()`, or the cards lose their times, labels and rating. |
| `resources/views/components/recipe-image.blade.php`   | A recipe photo as an `<img>` with its WebP copies in a `srcset`. Each JPEG in `public/images/recipes` needs copies 416, 640 and 832 px wide beside it; [performance.md](performance.md#adding-a-recipe-photo) shows how to make them. |
| `resources/js/app.js`                                 | Applies the sort menu on change and keeps empty fields out of the URL. Both are conveniences; the form works without JavaScript. |

A search is a plain `GET`, so it is always a shareable URL, for example
`/recipes?q=mango&diet[]=vegan&max_minutes=60&sort=rating`.

What can be searched on:

| Query parameter  | Filter                                                                              |
| ---------------- | ------------------------------------------------------------------------------------ |
| `q`              | One keyword across title, description, chef, cuisine, category, dietary label and ingredient |
| `ingredient`     | Recipes containing an ingredient by name                                             |
| `category[]`     | Course — matches **any** of the chosen ones                                          |
| `diet[]`         | Dietary label — must match **all** of them, since each one narrows what may be eaten |
| `cuisine[]`      | Cuisine — any of the chosen ones                                                     |
| `max_minutes`    | Preparation plus cooking time within 30, 60, 90 or 120 minutes                       |
| `min_servings`   | Serves at least 2, 4, 6 or 8                                                         |
| `min_rating`     | Averages at least 3, 4 or 5 out of 5                                                 |
| `sort`           | `title`, `title_desc`, `quickest`, `slowest`, `rating`, `steps`, `newest`            |

Two things to know before changing this file:

- **The sort key never reaches SQL.** It is matched against `RecipeSearch::SORTS` and
  a `match` statement decides the ordering, so no request value is ever used as a
  column name or a direction. Adding a sort means adding an entry to `SORTS` *and* an
  arm to `applySort()`.
- **Unrecognised input is dropped, not rejected.** An old bookmark still shows
  recipes rather than an error.

## One recipe — `/recipes/{slug}`

| File                                                  | What it does                                                    |
| ----------------------------------------------------- | ---------------------------------------------------------------- |
| `app/Http/Controllers/RecipeController.php` (`show`)   | Loads the recipe with its ingredients, sections, steps and labels, plus the average rating, whether the signed-in user has saved it, and their own rating. |
| `resources/views/recipes/show.blade.php`               | Ingredients grouped under their headings, numbered steps with a time each, servings, rating, the BBC Food source link, the Save/Remove favourite button and the rating form. Guests see "Log in to save this recipe" and "Log in to rate this recipe" links instead. |
| `app/Http/Controllers/FavouriteController.php`         | Saves or removes the favourite. Redirects back with a message, or answers JSON (`is_favourite`, `message`) when the request asks for it. |
| `app/Http/Controllers/RatingController.php`            | Validates and stores the rating: `overall` is required, `taste`, `difficulty` and `appearance` are optional, all whole numbers from 1 to 5 to match the database constraints. A user has one rating per recipe, so rating again replaces it, and a facet left out clears its earlier score. |
| `app/Models/RecipeIngredient.php` (`displayText()`)    | Turns stored amounts back into a line a cook reads: "3 garlic cloves, crushed". |
| `app/Support/Duration.php`                             | Formats minutes as "45 mins" or "1 hr 20 mins".                 |

Recipes resolve by `slug`, not by id — see `Recipe::getRouteKeyName()`.

Both forms on the page work as ordinary form submissions. With JavaScript on,
`favourite-toggle.js` and `star-rating.js` enhance them; see [JavaScript](#javascript).

## Registration — `/register`

| File                                                   | What it does                                            |
| ------------------------------------------------------ | -------------------------------------------------------- |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Shows the form, validates it, creates the user, signs them in. |
| `resources/views/auth/register.blade.php`               | Name, email, password and password confirmation, with a link to the privacy notice. |
| `resources/js/register.validation.js`                   | The client-side checks for this form; see [JavaScript](#javascript). |

Validation lives in the controller's `store()`: the name is required, the email must
be well formed and unused, and the password must meet `Password::defaults()` and be
confirmed. Passwords are hashed by the `hashed` cast on `App\Models\User`.

The client-side checks mirror these rules so the two cannot disagree: the name and email
fields carry `maxlength="255"` to match the server's `max:255`, and the 8-character
password minimum matches `Password::defaults()`. Change both sides together.

## Login and logout — `/login`, `POST /logout`

| File                                                        | What it does                                              |
| ----------------------------------------------------------- | ----------------------------------------------------------- |
| `app/Http/Requests/Auth/LoginRequest.php`                    | Validation, the credential check, and the rate limiting.    |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Shows the form, regenerates the session on login, clears it on logout. |
| `resources/views/auth/login.blade.php`                       | Email, password and "Remember me".                          |
| `resources/js/login.validation.js`                           | The client-side checks for this form; see [JavaScript](#javascript). |

Five failed attempts a minute, keyed on the email **and** the IP address, lock further
attempts out — see `LoginRequest::MAX_ATTEMPTS`. The throttle counter is kept in the
cache, which is why the `cache` table matters (`CACHE_STORE=database`).

## Password reset — `/forgot-password`, `/reset-password/{token}`

| File                                                        | What it does                                              |
| ----------------------------------------------------------- | ----------------------------------------------------------- |
| `app/Http/Controllers/Auth/PasswordResetLinkController.php`  | Shows the "Forgot your password?" form and emails a reset link through Laravel's password broker. |
| `app/Http/Controllers/Auth/NewPasswordController.php`        | Shows the form the link opens and sets the new password if the token matches the email address. |
| `resources/views/auth/forgot-password.blade.php`             | The email address field. Linked from the login page.        |
| `resources/views/auth/reset-password.blade.php`              | Email (filled in from the link), new password and confirmation. |

**Where the email goes in development:** `.env` sets `MAIL_MAILER=log`, so no email is
sent. The whole message, reset link included, is written to `storage/logs/laravel.log`;
copy the link from there into the browser. Set a real mailer in `.env` to send them.

Things to know before changing these:

- **Neither form reveals who has an account.** Asking for a link gives the same reply
  whether or not the address is registered, and a bad, used or expired token gets the
  same message as an unknown address. Laravel's default messages would tell a stranger
  which addresses are registered, which is why the controllers replace them.
- **A link works once, for 60 minutes**, and the broker sends at most one link per
  address a minute (`config/auth.php`, `passwords.users`). Both routes that accept a
  submission are also limited to six requests a minute per IP.
- **Resetting signs out "Remember me" logins elsewhere**, by replacing the user's
  remember token. The new password follows the same rules as registration.

## Account page — `/dashboard`

| File                                          | What it does                                                                 |
| --------------------------------------------- | ---------------------------------------------------------------------------- |
| `app/Http/Controllers/DashboardController.php` | Loads the signed-in user's saved recipes, most recently saved first, and their ratings with the recipe each one is for. Everything is read through the signed-in user, so nobody sees another user's favourites or ratings. |
| `resources/views/dashboard.blade.php`          | The user's name, email and join date; the saved recipes as recipe cards; and each rated recipe with the scores given, leaving out the facets the user skipped. |

Its "Your details" card links to the account settings.

## Account settings — `/account`

| File                                                  | What it does                                                         |
| ----------------------------------------------------- | --------------------------------------------------------------------- |
| `app/Http/Controllers/AccountController.php`          | Shows the settings page; saves a new name and email (the email must be unused, though keeping your own passes); deletes the account once the user confirms their password, then logs them out and ends the session. |
| `app/Http/Controllers/AccountPasswordController.php`  | Changes the password. The current password is required, and the new one must meet `Password::defaults()` and be confirmed. |
| `resources/views/account/edit.blade.php`              | Three forms on one page: details, password, and deleting the account. |

Things to know before changing these:

- **Deleting an account removes the user's favourites and ratings too**, through the
  cascading foreign keys on `favourites` and `ratings`. This is also how test accounts
  get deleted (§9).
- **The password form and the delete form both have a `password` field**, so their
  errors are kept apart in named error bags, `updatePassword` and `deleteAccount`, and
  their inputs and messages have distinct ids (`new_password`, `delete_password`). A
  new form on this page with a clashing field name needs the same treatment.
- The rules mirror registration's: `max:255` on name and email with a matching
  `maxlength`, and the same 8-character password minimum on both sides.

## Privacy notice — `/privacy`

`resources/views/privacy.blade.php`, linked from the footer and the registration form.
It says what the application stores and why (name, email, the password as a hash, saved
recipes and ratings, the login session with its IP address and browser description),
which cookies it sets, that nothing is shared or tracked, and how to delete an account.
It reads the session cookie's name from `config('session.cookie')`, so it stays right if
`APP_NAME` changes. **If the application starts storing anything new, update this page
in the same change.**

## JSON search API — `/api/recipes`

| File                                                  | What it does                                                         |
| ----------------------------------------------------- | --------------------------------------------------------------------- |
| `routes/api.php`                                      | The one route, public and read-only.                                   |
| `app/Http/Controllers/Api/RecipeSearchController.php` | Runs the query string through `RecipeSearch` and paginates twelve at a time. |
| `app/Http/Resources/RecipeResource.php`               | One recipe as JSON: slug, title, description, page URL, image URL, times, servings, categories, dietary labels, average rating, number of ratings and number of steps. |
| `config/api.php`                                      | The rate limit, 60 requests a minute per IP (`API_RECIPE_SEARCH_PER_MINUTE`). |

It takes exactly the listing's query parameters (see the table above) and uses the same
service, so `/api/recipes?q=pizza&sort=quickest` always returns what
`/recipes?q=pizza&sort=quickest` shows. The response is Laravel's paginated resource
shape: `data`, `links` and `meta`. It exposes aggregate ratings only, never who rated or
saved a recipe. Unrecognised input is dropped, as on the listing; an unknown address
under `/api` answers with a JSON 404, and going over the limit with a 429.

## JavaScript

Every script is progressive enhancement: each page works with JavaScript off, and the
scripts only make it quicker to use. All of them are imported by `resources/js/app.js`,
the one script entry the layout loads on every page, so each script first checks that
its form is on the page and otherwise does nothing.

| File                                                    | What it does                                                        |
| ------------------------------------------------------- | -------------------------------------------------------------------- |
| `resources/js/app.js`                                   | The entry point. Also applies the sort menu as soon as it changes and keeps empty fields out of the search URL. |
| `resources/js/common.js`                                | `setupFormValidation()`, shared by login and registration. A field is checked when the user leaves it, but only once they have typed in it, so tabbing through an empty form stays quiet. While a message is showing, it is re-checked on every keystroke and clears the moment the value is fixed; all visible messages are re-checked, because one rule can depend on another field (correcting the password can fix the confirmation). On submit every field is checked and focus moves to the first invalid one. Each message is linked to its field with `aria-describedby` and `aria-invalid` only while it shows, and the server's messages from the previous submission are hidden and unlinked once the user starts typing. A blur caused by pressing a button is not checked: the message it showed would push the button out from under the pointer and the click would be lost, and the submit check covers that field anyway. |
| `resources/js/register.validation.js`                   | Registration's fields and rules: a name, a well-formed email, a password of at least 8 characters (matching `Password::defaults()`) and a matching confirmation. |
| `resources/js/login.validation.js`                      | Login's fields and rules: a well-formed email and a non-empty password. |
| `resources/js/account.validation.js`                    | The account settings page's three forms: a name and well-formed email; the current password, a new one of at least 8 characters and a matching confirmation; and a password to confirm deletion. The 8-character minimum is `MIN_PASSWORD_LENGTH` in `common.js`, shared with registration. |
| `resources/js/password-reset.validation.js`             | The forgot password form (a well-formed email) and the reset form (a well-formed email, a new password of at least 8 characters and a matching confirmation). |
| `resources/js/favourite-toggle.js`                      | Sends the Save/Remove favourite form with `fetch()` and flips the button in place, announcing the result to screen readers. Any failure, such as a network error or an expired session, falls back to a normal submit. While a request is out the button is marked `aria-busy` rather than disabled, because disabling a focused button throws keyboard focus back to the top of the page. |
| `resources/js/star-rating.js`                           | Shows the rating form's 1–5 radio buttons as stars, with a hover preview. The radios stay underneath, visually hidden, so keyboards, screen readers and submission behave as without JavaScript. |

The login and registration forms carry `novalidate`, so these messages replace the
browser's own. The end-to-end tests exercise the scripts in Chrome; see
[Running the end-to-end tests](testing.md#running-the-end-to-end-tests).

## The database

| Path                                    | Contents                                                                |
| --------------------------------------- | ------------------------------------------------------------------------ |
| `database/migrations`                   | The schema, one file per table, in the order they are created.           |
| `app/Models`                            | One Eloquent model per table, carrying the relationships.                |
| `database/seeders/ReferenceDataSeeder.php` | Time bands, units, courses, dietary labels.                            |
| `database/seeders/RecipeSeeder.php`     | The eight recipes.                                                       |
| `database/seeders/data/recipes.php`     | The recipe data itself, transcribed from BBC Food.                       |
| `database/seeders/SampleUserSeeder.php` | Four fictional users with ratings and saved recipes.                     |
| [`database-design.md`](database-design.md) | The schema written up, with the reasoning behind it.                     |

`Recipe` is the centre of it: it belongs to a chef, a cuisine and two time bands, has
many ingredients, sections and steps, and belongs to many categories and dietary tags.

## Shared form components

Small Blade components used across the forms, all in `resources/views/components`:
`input-label`, `text-input`, `select-input`, `checkbox-filter`, `input-error` and
`primary-button`. Prefer these over writing classes inline, so the forms stay
consistent.

`input-error` renders a field's server message with the id `{field}-error`. Point the
field at it with `aria-describedby` while the error exists, as the login and
registration views do, so a screen reader reads the message with the field. Pass `bag`
to read a named error bag, and `id` when two forms on a page share a field name, as the
account settings page does.

## Security headers and query checks

- `app/Http/Middleware/SecurityHeaders.php` adds a Content Security Policy and the other
  security headers to every response. Scripts and styles need the per-request nonce that
  `@vite` and `@fonts` print, so **do not add inline `<script>`, `<style>` or `style=""`
  to a view** — the browser will refuse them. Put the code in `resources/js` or use
  Tailwind classes. The policy relaxes only while a Vite dev server is running; the
  headers, and why each is there, are in [deployment.md](deployment.md).
- Outside production, `Model::preventLazyLoading()` in `AppServiceProvider` makes an N+1
  query throw instead of running quietly. If a page fails with a lazy loading error, add
  the relationship to the query's `with()`.
