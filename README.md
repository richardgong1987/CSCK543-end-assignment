# Recipe Box

A recipe web application built for the CSCK543 group assignment.

Visitors can search recipes by title, ingredient, course, dietary need, cuisine,
cooking time, servings and rating, and sort the results in several ways. Registering
an account lets a user save and rate the recipes they like.

Built with Laravel 13 on PHP 8.3+, Blade templates, Tailwind CSS and vanilla
JavaScript, storing everything in MySQL. No front-end framework renders the markup,
as the brief requires.

## Requirements

| Tool     | Version                                                  |
| -------- | -------------------------------------------------------- |
| PHP      | 8.3 or newer (developed on 8.5)                          |
| Composer | 2                                                        |
| MySQL    | 8 or newer                                               |
| Node.js  | 20 or newer                                              |
| pnpm     | 10 — this project's lockfile is `pnpm-lock.yaml`, not npm |

### Installing PHP and Composer

Check whether you already have them:

```sh
php -v
composer -V
```

If either is missing, install both with the one-liner for your operating system, then
**restart your terminal** and check the two commands again.

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows (PowerShell):

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

### Installing Node and pnpm

Install Node.js 20+ from [nodejs.org](https://nodejs.org), then:

```sh
npm install -g pnpm
```

## Setting up

### 1. Clone the repository and install dependencies

```sh
git clone git@github.com:richardgong1987/CSCK543-end-assignment.git
cd CSCK543-end-assignment
composer install
pnpm install
```

### 2. Set your own MySQL credentials in `.env`

If you do not already have a `.env` file, start from the example:

```sh
cp .env.example .env
```

Then open `.env` and fill in **the MySQL username and password on your own machine**.
These differ from person to person, so do not copy anybody else's. You do not need to
create the database yourself — step 3 does that:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=csck543
DB_USERNAME=root
DB_PASSWORD=your-own-local-password
```

> `.env` holds the credentials for your machine only, so it must stay out of git —
> keep `.env` listed in `.gitignore` and never commit it. `.env.example` is the shared
> copy, and it carries no real passwords.

Finally, give your copy an application key:

```sh
php artisan key:generate
```

`.env.example` ships with `APP_KEY=` empty, and Laravel encrypts the session cookie
with that key. Without it every page fails with *"No application encryption key has
been specified"*, so this is a one-off you cannot skip. It writes the key straight
into your `.env`.

### 3. Create the database, the tables and the sample data

```sh
php artisan migrate --seed
```

If the database named in `DB_DATABASE` does not exist yet, this warns you and offers
to create it — answer yes, and it is created for you. (Your MySQL user needs the
`CREATE` privilege for that, which `root` has.) The tables themselves are always
created as `utf8mb4`, from the charset in `config/database.php`.

It then builds the schema and loads the eight BBC Food recipes named in the brief,
along with their ingredients, steps, categories and dietary labels, plus four
fictional user accounts with ratings and saved recipes.

## Running the application

```sh
composer run dev
```

One command starts everything: the PHP server on <http://localhost:8000>, the Vite
server that compiles the CSS and JavaScript, and a live log viewer. Press `Ctrl+C` to
stop them all.

Open <http://localhost:8000> in Chrome.

If you would rather not run Vite — to look at the site without a watcher, say — build
the assets once instead, then run the server on its own:

```sh
pnpm run build
php artisan serve
```

Without one or the other, the pages load with no styling at all.

## Signing in

The seeder creates four fictional accounts. Every one of them uses the password
`password`:

- `amelia@example.test`
- `ben@example.test`
- `chen@example.test`
- `dara@example.test`

You can also register a new account at <http://localhost:8000/register>.

## Running the tests

```sh
composer test
```

The tests run against an in-memory SQLite database, so they never read or write your
local MySQL data, and you do not need to reset anything afterwards.

## Resetting the database

```sh
php artisan migrate:fresh --seed
```

Drops every table and rebuilds it from the migrations and seeders. This also deletes
any account you registered by hand, so it is the quickest way back to a known state.

## Command reference

| Command                          | What it does                                     |
| -------------------------------- | ------------------------------------------------ |
| `composer run dev`               | Start the server, Vite and the log viewer         |
| `composer test`                  | Run the whole test suite                          |
| `php artisan migrate --seed`     | Create the tables and load the sample data        |
| `php artisan migrate:fresh --seed` | Rebuild the database from scratch               |
| `pnpm run build`                 | Compile the CSS and JavaScript for production     |
| `vendor/bin/pint`                | Apply the project's PHP code style                |

## What is built, and where

Every URL the application answers is declared in `routes/web.php`. Start there, then
follow the controller.

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

### Layout and navigation

| File                                              | What it is                                                                              |
| ------------------------------------------------- | --------------------------------------------------------------------------------------- |
| `resources/views/components/layouts/app.blade.php` | The shell **every** page uses, the login and registration pages included: skip link, header, nav, `<main>`, footer. The nav shows the app name, a Recipes link, and either Log in / Register or a "Your account" link, the user's name and a Log out button. Above each page's content it shows the `status` flash message that actions such as saving a favourite or a rating send back, in a `role="status"` paragraph so screen readers announce it. The footer carries the copyright line and the module credit. |
| `resources/views/components/layouts/auth.blade.php` | The narrow centred card the auth pages used to use. **Nothing references it any more** — delete it, or move the auth pages back onto it; leaving a second layout that no page opts into only misleads the next reader. |

A page opts into a layout with `<x-layouts.app title="...">`.

`app.blade.php` puts `title` in `<title>` and nothing else, so **each page supplies its own
`<h1>`** — the old auth layout rendered one for you, and login and register now write their own.
Keep it to exactly one per page.

### Home page — `/`

| File                                     | What it does                                                     |
| ---------------------------------------- | ---------------------------------------------------------------- |
| `app/Http/Controllers/HomeController.php` | Counts the recipes, picks the three highest rated, loads the courses and dietary labels with their recipe counts. |
| `resources/views/home.blade.php`          | The page itself.                                                 |

Every link on it is a recipe search: a search box, one chip per course and per dietary
label, four shortcuts ("On the table within 1 hr", "The best rated recipes", and so
on), and three recipe cards. Courses and labels with no recipes are left out, so no
link leads to an empty page.

### Recipe listing, search and sorting — `/recipes`

**This is where the search and sorting live.**

| File                                                 | What it does                                                    |
| ---------------------------------------------------- | ---------------------------------------------------------------- |
| `app/Services/RecipeSearch.php`                      | **All of the search and sorting logic.** Reads the query string, allow-lists it, and builds the Eloquent query. |
| `app/Http/Controllers/RecipeController.php` (`index`) | Hands the query string to `RecipeSearch` and paginates the result. |
| `resources/views/recipes/index.blade.php`             | The results page: the summary line, the grid of cards, the paging links. |
| `resources/views/components/recipe-filters.blade.php` | The search and filter form.                                     |
| `resources/views/components/recipe-card.blade.php`    | One recipe as a card; the whole card links to the recipe. Shared with the home page and the account page. Load recipes for it with `Recipe::withCardDetails()`, or the cards lose their times, labels and rating. |
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

### One recipe — `/recipes/{slug}`

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

### Registration — `/register`

| File                                                   | What it does                                            |
| ------------------------------------------------------ | -------------------------------------------------------- |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Shows the form, validates it, creates the user, signs them in. |
| `resources/views/auth/register.blade.php`               | Name, email, password and password confirmation.        |
| `resources/js/register.validation.js`                   | The client-side checks for this form; see [JavaScript](#javascript). |

Validation lives in the controller's `store()`: the name is required, the email must
be well formed and unused, and the password must meet `Password::defaults()` and be
confirmed. Passwords are hashed by the `hashed` cast on `App\Models\User`.

The client-side checks mirror these rules so the two cannot disagree: the name and email
fields carry `maxlength="255"` to match the server's `max:255`, and the 8-character
password minimum matches `Password::defaults()`. Change both sides together.

### Login and logout — `/login`, `POST /logout`

| File                                                        | What it does                                              |
| ----------------------------------------------------------- | ----------------------------------------------------------- |
| `app/Http/Requests/Auth/LoginRequest.php`                    | Validation, the credential check, and the rate limiting.    |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Shows the form, regenerates the session on login, clears it on logout. |
| `resources/views/auth/login.blade.php`                       | Email, password and "Remember me".                          |
| `resources/js/login.validation.js`                           | The client-side checks for this form; see [JavaScript](#javascript). |

Five failed attempts a minute, keyed on the email **and** the IP address, lock further
attempts out — see `LoginRequest::MAX_ATTEMPTS`. The throttle counter is kept in the
cache, which is why the `cache` table matters (`CACHE_STORE=database`).

### Password reset — `/forgot-password`, `/reset-password/{token}`

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

### Account page — `/dashboard`

| File                                          | What it does                                                                 |
| --------------------------------------------- | ---------------------------------------------------------------------------- |
| `app/Http/Controllers/DashboardController.php` | Loads the signed-in user's saved recipes, most recently saved first, and their ratings with the recipe each one is for. Everything is read through the signed-in user, so nobody sees another user's favourites or ratings. |
| `resources/views/dashboard.blade.php`          | The user's name, email and join date; the saved recipes as recipe cards; and each rated recipe with the scores given, leaving out the facets the user skipped. |

Its "Your details" card links to the account settings.

### Account settings — `/account`

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

### JavaScript

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
| `resources/js/favourite-toggle.js`                      | Sends the Save/Remove favourite form with `fetch()` and flips the button in place, announcing the result to screen readers. Any failure, such as a network error or an expired session, falls back to a normal submit. |
| `resources/js/star-rating.js`                           | Shows the rating form's 1–5 radio buttons as stars, with a hover preview. The radios stay underneath, visually hidden, so keyboards, screen readers and submission behave as without JavaScript. |

The login and registration forms carry `novalidate`, so these messages replace the
browser's own. No automated browser tests cover the scripts yet; see
[End-to-end tests](#testing-proposal-7).

### The database

| Path                                    | Contents                                                                |
| --------------------------------------- | ------------------------------------------------------------------------ |
| `database/migrations`                   | The schema, one file per table, in the order they are created.           |
| `app/Models`                            | One Eloquent model per table, carrying the relationships.                |
| `database/seeders/ReferenceDataSeeder.php` | Time bands, units, courses, dietary labels.                            |
| `database/seeders/RecipeSeeder.php`     | The eight recipes.                                                       |
| `database/seeders/data/recipes.php`     | The recipe data itself, transcribed from BBC Food.                       |
| `database/seeders/SampleUserSeeder.php` | Four fictional users with ratings and saved recipes.                     |
| `docs/database-design.md`               | The schema written up, with the reasoning behind it.                     |

`Recipe` is the centre of it: it belongs to a chef, a cuisine and two time bands, has
many ingredients, sections and steps, and belongs to many categories and dietary tags.

### Shared form components

Small Blade components used across the forms, all in `resources/views/components`:
`input-label`, `text-input`, `select-input`, `checkbox-filter`, `input-error` and
`primary-button`. Prefer these over writing classes inline, so the forms stay
consistent.

`input-error` renders a field's server message with the id `{field}-error`. Point the
field at it with `aria-describedby` while the error exists, as the login and
registration views do, so a screen reader reads the message with the field. Pass `bag`
to read a named error bag, and `id` when two forms on a page share a field name, as the
account settings page does.

### Tests

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
| `tests/Feature/HomePageTest.php`           | The home page and its links                                   |
| `tests/Feature/RecipeSchemaTest.php`       | Relationships, constraints and cascading deletes              |
| `tests/Unit/DurationTest.php`, `tests/Unit/IngredientLineTest.php` | Time and ingredient formatting        |

### Not built yet

Measured against the assignment brief (`docs/requirement/Group Project.md`) and our
own technical proposal (`docs/requirement/1. Technical architecture.md`). Each entry
says what already exists, so nobody redoes work that is done.

#### Features the brief asks for

None outstanding. Every feature the brief asks for is built, along with the account
settings and password reset our proposal mentions; see
[What is built, and where](#what-is-built-and-where).

#### Quality attributes

| Work                    | Where it stands                                                                                                                                                                                                    |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Accessibility**       | Automated checks done; a human screen-reader pass is still to do. Every page has exactly one `<h1>`, a `<main>` landmark, labelled controls, alt text on every image and a sensible heading order; pages with navigation also have a skip link, and the filter groups use `<fieldset>`/`<legend>` inside a `role="search"` form. Form errors, from JavaScript and from the server, are tied to their field with `aria-describedby` and `aria-invalid`, and a failed submit moves focus to the first invalid field. **Checked on 15 September 2026** in Chrome with axe-core 4.13 (WCAG 2.0, 2.1 and 2.2 A/AA plus best practices), in light and dark mode: no violations on the home page, the recipe listing, a recipe page (as a guest and logged in, with the rating form), login, registration (including the client-side and server-side error states), the account page, the account settings page (including a server error on the delete form), and the forgot password and reset password pages (including their status message and a refused token). A scripted keyboard pass over the same pages reached every control with Tab and found a visible focus change on each. Colour contrast was measured for the whole palette and fixed where it fell short: accent-red text darkened from `#f53003` to `#d32903` (3.9:1 → 5.1:1), the JavaScript error text given a dark-mode colour, input and select borders raised to 3:1 (`#91918f` light, `#676763` dark, for WCAG 1.4.11), and placeholders to `#767570`. The audit was a one-off script, not yet in the repository; it belongs with the end-to-end tests. Still to do: a screen-reader pass (VoiceOver or NVDA) and a person completing a keyboard-only journey, since a script cannot judge whether the focus order and announcements make sense. |
| **Responsive layout**   | Built with Tailwind and checked at desktop and narrow widths. Not yet checked on real devices, or in Chrome's device emulation.                                                                                     |
| **Target environment**  | We develop against `php artisan serve`. The brief specifies Apache via XAMPP on Windows, assessed in Chrome. Somebody needs to run the app that way and confirm it behaves, well before submission.                 |

#### Testing (proposal §7)

| Work                          | Where it stands                                                                                                     |
| ----------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| Unit and feature tests        | 166 tests covering the schema, authentication, password reset, search, sorting, favourites, ratings, the account page, account settings and the other pages. Extend these as features land.          |
| **End-to-end tests**          | None. §7.1 asks for Playwright or Dusk covering register → log in → search → open a recipe → save a favourite → log out, including a keyboard-only journey. |
| **Performance testing**       | None. §7.2 asks for Lighthouse, page weight, query counts and N+1 checks.                                            |
| **Load and stress testing**   | None, and only relevant if the JSON endpoint below gets built. §7.3 describes the k6 runs and the figures to record. |
| **Continuous integration**    | No `.github/workflows`. §7.4 asks for the tests to run on every push.                                                |

#### Security, privacy and deployment (proposal §8–§10)

| Work                          | Where it stands                                                                                                                                          |
| ----------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Injection, XSS, CSRF          | Handled: queries go through Eloquent, the sort key is allow-listed, Blade escapes output, and every state-changing form carries `@csrf`.                  |
| Login throttling              | Handled: five attempts a minute per email and IP. Password reset requests and submissions are limited to six a minute per IP, and the broker sends at most one link per address a minute.                                                                                                        |
| **Security headers and CSP**  | Not done (§8.2).                                                                                                                                         |
| **Production configuration**  | Not done (§8.3, §8.5): HTTPS, `Secure`/`HttpOnly`/`SameSite` cookies, `APP_DEBUG=false`, least-privilege database credentials.                            |
| **Dependency audit**          | Not run (§8.5): `composer audit` and `pnpm audit`.                                                                                                       |
| **Privacy notice**            | Not written (§9): why we collect a name and an email, and how test accounts get deleted. The deletion itself exists: a user can delete their own account, with its favourites and ratings, from `/account`, and `php artisan migrate:fresh --seed` resets every account.                                                                  |
| **Deployment write-up**       | Proposed in §10 but not yet written up for the report.                                                                                                   |

#### Optional

- The read-only JSON search endpoint from §5 of the proposal, for example
  `GET /api/recipes?q=pizza&sort=quickest`. It would reuse `RecipeSearch` unchanged —
  the service returns a query, so a controller only has to paginate it and return JSON.

#### Report and delivery (§12)

The report, screenshots, meeting minutes and the video are still outstanding, and the
ER diagram they will need is already in `docs/database-design.md`.

## Attribution

The recipes, their text and their images come from
[BBC Food](https://www.bbc.co.uk/food) and are reproduced here for the educational
purpose of this assignment only. Each recipe stores the URL it came from, shown on its
page.
