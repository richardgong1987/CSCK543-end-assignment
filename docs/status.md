# Project status

Measured against the assignment brief (`requirement/Group Project.md`) and our own
technical proposal (`requirement/1. Technical architecture.md`). Each entry says what
already exists, so nobody redoes work that is done.

## Features the brief asks for

None outstanding. Every feature the brief asks for is built, along with the account
settings and password reset our proposal mentions; see
[What is built, and where](architecture.md).

## Quality attributes

| Work                    | Where it stands                                                                                                                                                                                                    |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Accessibility**       | Automated checks done; a human screen-reader pass is still to do. Every page has exactly one `<h1>`, a `<main>` landmark, labelled controls, alt text on every image and a sensible heading order; pages with navigation also have a skip link, and the filter groups use `<fieldset>`/`<legend>` inside a `role="search"` form. Form errors, from JavaScript and from the server, are tied to their field with `aria-describedby` and `aria-invalid`, and a failed submit moves focus to the first invalid field. **Checked on 15 September 2026** in Chrome with axe-core 4.13 (WCAG 2.0, 2.1 and 2.2 A/AA plus best practices), in light and dark mode: no violations on the home page, the recipe listing, a recipe page (as a guest and logged in, with the rating form), login, registration (including the client-side and server-side error states), the account page, the account settings page (including a server error on the delete form), the forgot password and reset password pages (including their status message and a refused token), and the privacy notice. A scripted keyboard pass over the same pages reached every control with Tab and found a visible focus change on each. Colour contrast was measured for the whole palette and fixed where it fell short: accent-red text darkened from `#f53003` to `#d32903` (3.9:1 → 5.1:1), the JavaScript error text given a dark-mode colour, input and select borders raised to 3:1 (`#91918f` light, `#676763` dark, for WCAG 1.4.11), and placeholders to `#767570`. The audit was a one-off script, not yet in the repository; it belongs with the end-to-end tests. Still to do: a screen-reader pass (VoiceOver or NVDA) and a person completing a keyboard-only journey, since a script cannot judge whether the focus order and announcements make sense. |
| **Responsive layout**   | **Checked on 15 September 2026** in Chrome's device emulation at 360, 390, 768 and 1280 px wide, on every page as a guest and signed in (48 page loads): no horizontal overflow anywhere, and the phone-width screenshots reviewed by eye. The login and registration forms had a fixed width wider than a phone, and now shrink to fit. Still to do: a check on a real phone and tablet. |
| **Target environment**  | **Tested on macOS on 19 September 2026**: the application runs on XAMPP 8.2.4's Apache and MariaDB, with PHP 8.5 as PHP-FPM because XAMPP's own PHP 8.2 is too old for this project. The steps, every problem we solved on the way and the screenshot evidence are in [xampp.md](xampp.md). The brief assesses on XAMPP for Windows, whose steps are written in the same guide but have not been tried yet. |

## Testing (proposal §7)

| Work                          | Where it stands                                                                                                     |
| ----------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| Unit and feature tests        | 195 tests covering the schema, authentication, password reset, search, sorting, favourites, ratings, the account page, account settings, the JSON API, security headers, query budgets and the other pages. Extend these as features land.          |
| End-to-end tests              | Built: four Playwright tests in `tests/e2e`, run in stable Chrome with `pnpm run test:e2e`, all passing on 15 September 2026. They cover §7.1's journey — register, log in, search, open a recipe, save a favourite, view it on the account page, log out — plus rating with the star control, the skip link, and the whole journey again using only the keyboard. The keyboard journey found a real bug, now fixed: saving a favourite threw keyboard focus back to the top of the page. |
| Performance testing           | **Measured on 15 September 2026** with Lighthouse 13.4 in Chrome (mobile emulation, built assets): performance 93 on the home page, 86 on the recipe listing, 96 on a recipe page and 98 on login; accessibility, best practices and SEO 100 on all four; total blocking time 0 ms and layout shift 0 everywhere; page weight 196–789 KiB. Server responses averaged 34 ms for a search page and 28 ms for the JSON API over 20 requests each. The built CSS is 13.4 KB and the JavaScript 1.7 KB gzipped; the eight recipe images total 644 KB. `QueryBudgetTest` pins each page's query count, and `Model::preventLazyLoading()` turns any N+1 query into an error; none was found. The measurement led to a meta description on every page, sizes on the home page's hero image, and eager loading for the listing's first image. What still slows the listing (largest paint 4.0 s) is image weight — WebP or AVIF would save about 320 KiB — and, on a real server, caching headers (see [deployment.md](deployment.md#caching-the-built-assets)). The group has not agreed acceptance criteria yet (§7.2), so these figures are the baseline to set them against. |
| **Load and stress testing**   | Script written, not yet run: `tests/load/recipe-search-api.js` has a k6 `load` scenario (20 steady users) and a `stress` scenario (ramping to 400, then recovery) for the JSON API. k6 has to be installed first; how to run it and what to record are in [deployment.md](deployment.md#5-load-and-stress-testing-the-json-api-73). |
| **Continuous integration**    | Written, not yet seen running: `.github/workflows/tests.yml` runs the unit and feature tests, the end-to-end tests in Chrome, and `composer audit` and `pnpm audit`, on every push and pull request. Check the first run in the repository's Actions tab. |

## Security, privacy and deployment (proposal §8–§10)

| Work                          | Where it stands                                                                                                                                          |
| ----------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Injection, XSS, CSRF          | Handled: queries go through Eloquent, the sort key is allow-listed, Blade escapes output, and every state-changing form carries `@csrf`.                  |
| Login throttling              | Handled: five attempts a minute per email and IP. Password reset requests and submissions are limited to six a minute per IP, and the broker sends at most one link per address a minute.                                                                                                        |
| Security headers and CSP      | Handled (§8.2): a Content Security Policy with per-request nonces and no inline scripts, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, and HSTS over HTTPS; checked in Chrome with no policy violations, with built assets and with the Vite dev server. See [Security headers and query checks](architecture.md#security-headers-and-query-checks). |
| Production configuration      | Written up (§8.3, §8.5) in [deployment.md](deployment.md#1-production-configuration-checklist): `APP_DEBUG=false`, `Secure`/`HttpOnly`/`SameSite` cookies, a real mailer, least-privilege database accounts, HTTPS behind a proxy, and caching. Applies once the app is deployed; `.env.example` stays set up for development. |
| Dependency audit              | Run on 15 September 2026: `composer audit` and `pnpm audit` found no known vulnerabilities. The CI workflow repeats both on every push. |
| Privacy notice                | Written (§9): `/privacy`, linked from every page's footer and the registration form. It covers what is stored and why, the cookies, and how to delete an account; see [Privacy notice](architecture.md#privacy-notice--privacy). |
| Deployment write-up           | Written (§10) in [deployment.md](deployment.md#3-secure-deployment-design-10): the network layout, firewall rules, administration access, secrets, logs, backups and separate environments, ready to adapt for the report. |

## Optional

Built: the read-only JSON search endpoint from §5 of the proposal,
`GET /api/recipes`; see [JSON search API](architecture.md#json-search-api--apirecipes). The listing page
does not call it — it stays a plain server-rendered search — so the endpoint is there for
the load tests and for any page that wants search results without a reload.

## Report and delivery (§12)

The report, screenshots, meeting minutes and the video are still outstanding, and the
ER diagram they will need is already in [database-design.md](database-design.md).
