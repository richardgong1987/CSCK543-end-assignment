# Deployment and production configuration

Written from the technical proposal (§8.3, §8.5, §10) for the report and for whoever
sets the application up on a real server. **Nothing here has been deployed.** To run it
on Apache with XAMPP, the way it is assessed, follow [xampp.md](xampp.md); this page
covers what changes beyond that for production.

## 1. Production configuration checklist

Everything below is set in `.env` on the server. `.env.example` is tuned for
development, so several values must change.

| Setting | Production value | Why |
| --- | --- | --- |
| `APP_ENV` | `production` | Also switches off `Model::preventLazyLoading()`, which is a development guard. |
| `APP_DEBUG` | `false` | With `true`, an error page shows the stack trace, configuration and query details to anyone (§8.5). |
| `APP_URL` | `https://…` | Links in emails, such as the password reset link, are built from it. |
| `LOG_LEVEL` | `warning` | `debug` records far more than an operator needs. |
| `SESSION_SECURE_COOKIE` | `true` | Unset, the session cookie is also sent over plain HTTP (§8.3). |
| `SESSION_HTTP_ONLY` | `true` (the default) | Keeps JavaScript from reading the session cookie. |
| `SESSION_SAME_SITE` | `lax` (the default) | Stops other sites sending the cookie with cross-site form posts, while a link from an email still arrives logged in. |
| `SESSION_ENCRYPT` | `true` | Session data is stored in the database; encrypting it protects it if the database leaks. |
| `MAIL_MAILER` | `smtp` or a mail service | The development `log` mailer writes password reset links, token included, into `storage/logs/laravel.log` — exactly what §9 says must stay out of logs. |
| `DB_USERNAME` / `DB_PASSWORD` | An application-only account | See least privilege below. |
| `API_RECIPE_SEARCH_PER_MINUTE` | `60` (the default) | Raise it only on a private copy for load testing. |

Then, on every deploy:

```sh
composer install --no-dev --optimize-autoloader
pnpm install && pnpm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`config:cache` freezes `.env` into a cached file, so run it again after any change to
`.env`.

### Compression

`public/.htaccess` compresses HTML, CSS, JavaScript and JSON, which cuts the stylesheet
from 66 to 14 KiB ([performance.md](performance.md#3-what-was-fixed)). It needs Apache's
`mod_deflate`, which XAMPP loads by default; on Debian or Ubuntu, run
`sudo a2enmod deflate`. The same file declares the WebP type for the recipe photos, which
older Apache versions do not know. On nginx, set `gzip on;` with the same `gzip_types`.

### Caching the built assets

`public/.htaccess` also sets how long browsers keep static files
([sustainability.md](sustainability.md#3-what-was-changed)). Every file Vite writes to
`public/build/assets` has a content hash in its name, so it is cached for a year: a
changed file gets a new name. Recipe photos keep their names, so they are cached for a
week. It needs `mod_headers`, which XAMPP loads by default; on Debian or Ubuntu, run
`sudo a2enmod headers`. PHP's built-in server (`php artisan serve`) ignores `.htaccess`,
so these headers appear only under Apache.

A CDN in front of the site can reuse the same lifetimes; see
[sustainability.md](sustainability.md#4-recommended-for-production-not-done-here) for
when one is worth it.

### Least-privilege database accounts

Use two MySQL accounts:

- **The application** connects with `SELECT, INSERT, UPDATE, DELETE` on the
  application's database only. It cannot drop or alter tables, so a compromised
  application cannot destroy the schema.
- **Migrations** run under a separate account that also has `CREATE, ALTER, DROP,
  INDEX, REFERENCES`, used only while deploying.

### HTTPS behind a reverse proxy

The application sends `Strict-Transport-Security` only when it sees the request arrived
over HTTPS. Behind a proxy that terminates TLS, Laravel sees plain HTTP unless it trusts
the proxy's `X-Forwarded-Proto` header, so add the proxy's address in
`bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '10.0.1.10');
    $middleware->append(SecurityHeaders::class);
})
```

Trust only the proxy's own address, never `*` on a server that can be reached directly.

## 2. Security headers already in the application

`app/Http/Middleware/SecurityHeaders.php` adds these to every response (§8.2), so they
apply under Apache, `php artisan serve` or a proxy alike:

| Header | Value |
| --- | --- |
| `Content-Security-Policy` | Scripts and styles only from the site itself or carrying the per-request nonce that `@vite` and `@fonts` print; no inline or `eval` scripts; images from the site or `data:`; forms may only post to the site; no framing; no plugins. |
| `X-Frame-Options` | `DENY` — the same no-framing rule for older browsers. |
| `X-Content-Type-Options` | `nosniff` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | Camera, microphone, geolocation and payment switched off. |
| `Strict-Transport-Security` | One year, only on HTTPS requests. |

While a Vite dev server is running, the policy also allows scripts, styles, fonts and
the live-reload socket over `http:` and `ws:`, and inline styles, because Vite injects
CSS without a nonce. That relaxation happens only when `public/hot` exists, which is
another reason to delete it on a server. Laravel's debug error page (only with
`APP_DEBUG=true`) is sent without the policy, because it is built from inline code.

## 3. Secure deployment design (§10)

```
Public Internet
     │  HTTPS on 443 (80 only redirects to 443)
     ▼
Reverse proxy — the only machine with a public address; terminates TLS
     │  private network: proxy → app on 80/9000 only
     ▼
Laravel application (Apache or nginx + PHP-FPM)
     │  private network: app → database on 3306 only
     ▼
MySQL — no public address, no public port
```

Figure 7 in [architecture-diagrams.md](architecture-diagrams.md#7-production-deployment-proposed)
draws the same design with the mail service, backups, administrator access and an
optional CDN.

- **Network segmentation and firewall rules.** The proxy accepts 80 and 443 from
  anywhere; the application server accepts traffic only from the proxy; MySQL accepts
  3306 only from the application server.
- **Administration** goes through a VPN, a bastion host or an IP allow-list. MySQL and
  phpMyAdmin are never exposed to the Internet; XAMPP's phpMyAdmin in particular must not
  be reachable on a public server.
- **Attack surface.** The only public routes are the application's own and Laravel's
  `/up` health check, which the proxy can restrict to the monitoring system. Remove
  XAMPP components that are not used (FileZilla, Mercury, Tomcat).
- **Secrets** stay in `.env` on the server, readable only by the web server's user,
  and never in git (`.env` is in `.gitignore`).
- **Logs** are readable only by operators. With the real mailer configured, the
  application logs no passwords, reset tokens or form contents.
- **Backups** of the database are encrypted and access-controlled.
- **Separate environments** for development, testing and production, each with its own
  `.env`, database and credentials. The end-to-end tests already use their own
  throwaway SQLite database.
- **Dependency updates.** The GitHub workflow runs `composer audit` and `pnpm audit` on
  every push; see below.

## 4. Dependency audit (§8.5)

| Command | Result on 15 September 2026 |
| --- | --- |
| `composer audit` | No security vulnerability advisories found. |
| `pnpm audit` | No known vulnerabilities found. |

The "Dependency audit" job in `.github/workflows/tests.yml` runs both on every push
and pull request, so a newly published advisory fails the build.

## 5. Load and stress testing the JSON API (§7.3)

`tests/load/recipe-search-api.js` is a k6 script with a `load` scenario (20 steady
virtual users for two minutes) and a `stress` scenario (ramping to 400, then back to 0
to watch recovery). **It was run on 19 September 2026; the results, and the capacity
recommendations for a production server, are in [load-testing.md](load-testing.md).** To
run it again, on a local copy only:

```sh
API_RECIPE_SEARCH_PER_MINUTE=1000000 php artisan serve
k6 run -e TEST_TYPE=load tests/load/recipe-search-api.js
k6 run -e TEST_TYPE=stress tests/load/recipe-search-api.js
```

Raising the rate limit matters: at the default of 60 requests a minute per IP the run
would measure the limiter, not the application. Record requests per second, average and
p95 response time, error rate, the highest stable number of virtual users, and whether
response times return to normal in the final stage.
