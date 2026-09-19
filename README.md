# Recipe Box

A recipe web application built for the CSCK543 group assignment.

Visitors can search recipes by title, ingredient, course, dietary need, cuisine,
cooking time, servings and rating, and sort the results in several ways. Registering
an account lets a user save and rate the recipes they like.

Built with Laravel 13 on PHP 8.4, Blade templates, Tailwind CSS and vanilla JavaScript,
storing everything in MySQL, or MariaDB as XAMPP ships it. No front-end framework
renders the markup, as the brief requires.

## Where to start

This file is only the entry point. Everything else lives in [`docs/`](docs):

| I want to…                                                        | Read                                               |
| ----------------------------------------------------------------- | -------------------------------------------------- |
| Run it on my own machine while developing                         | [docs/setup.md](docs/setup.md)                     |
| Run it on XAMPP, the way it is assessed (macOS tested; Linux, Windows) | [docs/xampp.md](docs/xampp.md)                |
| Run the tests: unit, feature, and end-to-end in Chrome            | [docs/testing.md](docs/testing.md)                 |
| Find where a feature lives in the code before I change it         | [docs/architecture.md](docs/architecture.md)       |
| See the VoiceOver and keyboard-only accessibility testing         | [docs/accessibility.md](docs/accessibility.md)     |
| See the phone and tablet layout testing                           | [docs/responsive.md](docs/responsive.md)           |
| See the load and stress test results                              | [docs/load-testing.md](docs/load-testing.md)       |
| See the performance targets and whether we meet them             | [docs/performance.md](docs/performance.md)         |
| Understand the database schema and why it is shaped that way      | [docs/database-design.md](docs/database-design.md) |
| See what is finished and what is still open against the brief     | [docs/status.md](docs/status.md)                   |
| Configure it for production, or write up deployment for the report | [docs/deployment.md](docs/deployment.md)          |

The assignment brief and our technical proposal are in
[docs/requirement](docs/requirement).

## Quick start

On macOS or Linux, with PHP 8.4+, Composer, pnpm and MySQL already installed —
[docs/setup.md](docs/setup.md) explains each step, and how to install the tools:

```sh
composer install
pnpm install
cp .env.example .env        # then set DB_CONNECTION=mysql and your own MySQL credentials
php artisan key:generate
php artisan migrate --seed
composer run dev
```

Open <http://localhost:8000> in Chrome and log in as `amelia@example.test` with the
password `password`.

With XAMPP, on macOS, Linux or Windows, follow [docs/xampp.md](docs/xampp.md) from the
beginning: XAMPP's own PHP is too old for this project, and the guide shows how to work
around it.

## Before you push

- `composer test` passes, and so does `pnpm run test:e2e` if you touched a page or its
  JavaScript — see [docs/testing.md](docs/testing.md).
- `vendor/bin/pint` has been run on your PHP changes.
- If you changed what a page does, update its section in
  [docs/architecture.md](docs/architecture.md). If you finished something that
  [docs/status.md](docs/status.md) lists as open, update that too.

## Attribution

The recipes, their text and their images come from
[BBC Food](https://www.bbc.co.uk/food) and are reproduced here for the educational
purpose of this assignment only. Each recipe stores the URL it came from, shown on its
page.
