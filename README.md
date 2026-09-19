# Recipe Box

A recipe web application built for the CSCK543 group assignment.

Visitors can search recipes by title, ingredient, course, dietary need, cuisine,
cooking time, servings and rating, and sort the results in several ways. Registering
an account lets a user save and rate the recipes they like.

Built with Laravel 13 on PHP 8.4 or newer, Blade templates, Tailwind CSS and vanilla
JavaScript, storing everything in MySQL, or MariaDB as XAMPP ships it. No front-end
framework renders the markup, as the brief requires.

## Report material, ready for the appendices

The testing the proposal asks for (§7) has been done, and each part is written up with
its method, results, screenshots, the problems we hit and the limitations. They are
written so that they can go into the report as appendices, or be quoted from directly.

Each document numbers its figures with its own letter, so the letter can double as the
appendix letter. Every figure is a PNG in `docs/images/<topic>/`, with its caption in the
document.

| Appendix | Topic | Document | What is in it | Figures |
| --- | --- | --- | --- | --- |
| A | Deployment on XAMPP | [docs/xampp.md](docs/xampp.md) | Why XAMPP's own PHP is too old and how we worked around it; the tested macOS set-up step by step, with Linux and Windows versions; 14 problems we hit and how each was solved | A.1–A.9 |
| B | Accessibility | [docs/accessibility.md](docs/accessibility.md) | Five scenarios with the real VoiceOver, and what it said; a keyboard-only run through 15 steps; one usability finding; a checklist for a person | B.1–B.21 |
| C | Responsive layout | [docs/responsive.md](docs/responsive.md) | 9 pages on an Android phone and tablet (27 page loads) in real mobile Chrome: no horizontal scrolling anywhere | C.1–C.12 |
| D | Load and stress testing | [docs/load-testing.md](docs/load-testing.md) | k6 against the JSON API: 550 requests a second with 0 % errors at 20 users; clean up to about 124 users; full recovery; a bug found and fixed | D.1–D.7 |
| E | Performance | [docs/performance.md](docs/performance.md) | 18 measurable acceptance criteria; all met after three fixes, with the numbers before and after; Lighthouse 100 on every page | E.1–E.8 |
| F | Web sustainability | [docs/sustainability.md](docs/sustainability.md) | 12 criteria based on the W3C Web Sustainability Guidelines; all met; carbon per page view rated A+; caching and compression fixes; why no CDN | F.1–F.4 |
| — | Architecture | [docs/architecture-diagrams.md](docs/architecture-diagrams.md) | Eight diagrams, from system context to the data model, each explained | Figures 1–8 |
| — | Secure deployment | [docs/deployment.md](docs/deployment.md) | Production settings, security headers, the network design (§10) and the dependency audit | — |
| — | Automated tests | [docs/testing.md](docs/testing.md) | The unit, feature and end-to-end tests, and how to run them | — |

All tests were run on 19 September 2026, against the XAMPP copy of the site. Beyond the
screenshots, the load-testing, performance and sustainability documents keep their raw
results in `docs/evidence/<topic>/`: the full k6 and Lighthouse reports (they open in
any browser), server timings, and the sustainability measurements.

**Still needs the group before submission** — each document says exactly what to do:

- **Sign off the targets**: the sign-off tables at the end of
  [performance.md](docs/performance.md#6-sign-off) and
  [sustainability.md](docs/sustainability.md#7-sign-off), and the pass thresholds in
  [load-testing.md](docs/load-testing.md#1-test-set-up).
- **A keyboard-only check by a person**, using the checklist in
  [accessibility.md, section 5](docs/accessibility.md#5-keyboard-only-check-by-a-person).
- **Real phones and tablets**: [responsive.md, section 5](docs/responsive.md#5-real-devices-by-a-person).
- **XAMPP on Windows**, which is written up but not yet tried:
  [xampp.md, section 5](docs/xampp.md#5-windows-not-tested).

[docs/status.md](docs/status.md) tracks everything against the brief.

## Working on the code

| I want to…                                                          | Read                                               |
| ------------------------------------------------------------------- | -------------------------------------------------- |
| Run it on my own machine while developing                           | [docs/setup.md](docs/setup.md)                     |
| Run it on XAMPP, the way it is assessed                             | [docs/xampp.md](docs/xampp.md)                     |
| Run the tests: unit, feature, and end-to-end in Chrome              | [docs/testing.md](docs/testing.md)                 |
| See how the pieces fit before reading code                          | [docs/architecture-diagrams.md](docs/architecture-diagrams.md) |
| Find where a feature lives in the code before I change it           | [docs/architecture.md](docs/architecture.md)       |
| Understand the database schema and why it is shaped that way        | [docs/database-design.md](docs/database-design.md) |
| See what is finished and what is still open against the brief       | [docs/status.md](docs/status.md)                   |

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
