# Architecture diagrams

Eight diagrams of Recipe Box, from the widest view to the narrowest: who uses it, how it
runs on XAMPP, how the code is layered, how two typical requests travel through it, how
the front end is built and checked, how it would be deployed for real, and the
database. [architecture.md](architecture.md) then says which file does what.

Each diagram carries its own legend. The colours are kept consistent where they can
be: orange for people and the browser, blue for our code and the servers that run it,
green for data, purple for files, and dashed grey for anything outside the project or
optional.

The diagrams are drawn in [draw.io](https://www.drawio.com/) (also called
diagrams.net), laid out by hand so that lines run at right angles and labels do not
cross. The editable sources are the `.drawio` files in [`diagrams/`](diagrams); the
pictures below are PNG exports at twice the normal resolution, ready for the report.
To change a diagram, see [section 9](#9-changing-a-diagram).

## 1. System context

![Figure 1 — Recipe Box, who uses it and what it talks to](images/architecture/1-system-context.png)

[draw.io source](diagrams/1-system-context.drawio)

- **Three kinds of user.** Visitors can search and read without an account; registered
  users can also save and rate recipes; and any program can use the JSON search API,
  which returns exactly what the listing page shows.
- **One application, one database.** The pages and the API are the same Laravel
  application, reading the same tables.
- **The only outside system is mail**, for password reset links. During development and
  on XAMPP the message is written to the log file instead of being sent.

## 2. Running on XAMPP

![Figure 2 — how a request is served on XAMPP](images/architecture/2-xampp-runtime.png)

[draw.io source](diagrams/2-xampp-runtime.drawio)

- **Apache answers static files itself** — CSS, JavaScript, fonts and photos never
  start PHP. `public/.htaccess` compresses text and tells browsers how long to keep
  each file ([sustainability.md](sustainability.md)).
- **Everything else goes to Laravel** through `public/index.php`, run by PHP-FPM 8.5,
  because XAMPP's own PHP 8.2 is too old for Laravel 13. XAMPP's PHP stays in place for
  phpMyAdmin and the XAMPP dashboard.
- **Both hops use Unix sockets**, so no other program on the machine can answer by
  mistake. The reasons for each choice are in [xampp.md](xampp.md#2-how-the-pieces-fit-together).
- **This is the tested macOS set-up, and Linux is the same.** On Windows, PHP 8.5
  replaces XAMPP's PHP inside Apache as a module, so there is no PHP-FPM box, and
  MariaDB is reached over TCP port 3306 ([xampp.md, Windows](xampp.md#5-windows-not-tested)).

## 3. Inside the application

![Figure 3 — where code goes inside the Laravel application](images/architecture/3-application-layers.png)

[draw.io source](diagrams/3-application-layers.drawio)

- **Three places for code**, as Laravel lays them out: controllers handle input and
  output, services do the work, and Eloquent models talk to the database. There are no
  extra layers, because the application does not need them.
- **The search logic lives in one class**, `Services/RecipeSearch`, shared by the
  listing page and the API, so the two can never disagree.
- **Simple actions skip the service.** Saving a favourite or a rating is a single write,
  so the controller calls the model directly.
- **Every response passes through `SecurityHeaders`**, which adds the Content Security
  Policy and its per-request nonce.
- **JavaScript only enhances.** Every page and form works with it switched off; the
  scripts add instant form checks, star ratings and in-place saving (see figure 5).

## 4. A recipe search

![Figure 4 — a recipe search, from click to page](images/architecture/4-search-request.png)

[draw.io source](diagrams/4-search-request.drawio)

- **The search is a plain `GET`**, so every result page has a URL that can be shared or
  bookmarked.
- **Nothing from the address reaches SQL as code** (steps 6–7): unknown filters are
  dropped, the sort key is matched against a fixed list, and values are bound
  parameters ([architecture.md](architecture.md#recipe-listing-search-and-sorting--recipes)).
- **A handful of queries per page**, with related data loaded up front; the test suite
  fails if the listing goes over its budget of 12 ([performance.md](performance.md)).
- **Steps 13–14 happen once.** After the first visit, the browser takes the CSS,
  JavaScript, fonts and photos from its cache, and asks the server only for the page.

## 5. Saving a favourite, with and without JavaScript

![Figure 5 — saving a favourite with and without JavaScript](images/architecture/5-favourite-toggle.png)

[draw.io source](diagrams/5-favourite-toggle.drawio)

- **One address, two kinds of answer.** The same controller returns JSON when the script
  asks for it, and a redirect with a message for an ordinary form.
- **Progressive enhancement:** with JavaScript, the button changes in place and screen
  readers hear the result; without it, or if the request fails, the form submits as
  usual. The user gets the same outcome either way.
- **Saving twice does no harm**: the recipe is attached without duplicates, and the
  database also enforces one favourite per user and recipe.

## 6. Build and continuous integration

![Figure 6 — building the front end, and the checks on every push](images/architecture/6-build-and-ci.png)

[draw.io source](diagrams/6-build-and-ci.drawio)

- **`pnpm run build` writes everything the browser needs** into `public/build`, with a
  hash of each file's content in its name. A changed file gets a new name, which is why
  browsers may keep these files for a year.
- **Tailwind reads the Blade views** and generates only the classes they use.
- **The layout never names a built file directly**: `@vite` and `@fonts` look the names
  up in the manifest and add the page's CSP nonce.
- **Every push runs three jobs** in GitHub Actions: the unit and feature tests, the
  end-to-end tests in Chrome, and a security audit of the dependencies
  ([testing.md](testing.md)).

## 7. Production deployment (proposed)

![Figure 7 — proposed production deployment](images/architecture/7-production-deployment.png)

[draw.io source](diagrams/7-production-deployment.drawio)

**Nothing here has been deployed**; it is the design from the proposal (§10), written
up in [deployment.md](deployment.md).

- **Only the reverse proxy is public.** It handles HTTPS; the application server and the
  database sit on a private network and accept traffic only from the machine in front
  of them.
- **The database has no public address**, and the application connects with an account
  that can read and write rows but not change or drop tables.
- **Operators come in through a VPN or bastion host**, never over the public internet.
- **A CDN is optional**, and not used for the XAMPP submission; when it would be worth
  it is discussed in [sustainability.md](sustainability.md#4-recommended-for-production-not-done-here).

## 8. Data model

![Figure 8 — conceptual data model](images/architecture/8-data-model.png)

[draw.io source](diagrams/8-data-model.drawio)

This is the conceptual view of the database: one box per table, named in plain English
with the table name underneath, and no columns. The full ER diagram, with every column,
key and constraint, is in [database-design.md](database-design.md#1-er-diagram), which
also explains the reasons behind the design.

- **Green boxes are the principal entities**: things that exist in their own right, such
  as recipes, users, chefs and ingredients. **Blue boxes are associative or supporting
  entities**: they exist only to connect two others, or as a part of a recipe.
- **Solid arrows are relationships, read along the arrow**: a chef *writes* a recipe; a
  unit *measures* an ingredient line. **Dashed lines** join an associative entity to its
  other side: a favourite links a user to a recipe.
- **`recipes` is the centre.** Each recipe has one chef and one cuisine, a band for its
  preparation time and one for its cooking time, method steps, and ingredient lines
  that may be grouped under sections. Courses and dietary labels attach through their
  own junction tables, because a recipe can have several of each.
- **Users reach recipes in only two ways**, by saving them (`favourites`) and by rating
  them (`ratings`). Both allow one row per user and recipe.

## 9. Changing a diagram

Open the `.drawio` file in [app.diagrams.net](https://app.diagrams.net) (free, in the
browser, nothing to install) or in the draw.io desktop app, edit it, and save it back
over the same file. The PNGs also carry a copy of their diagram, so draw.io can open a
PNG directly as well.

Then export the PNG again. From the draw.io editor: *File → Export as → PNG*, zoom 200 %,
border 24, "Include a copy of my diagram" ticked, saved over the old file in
`docs/images/architecture`. Or from the command line, with the desktop app installed
(`brew install --cask drawio` on macOS):

```sh
for f in docs/diagrams/*.drawio; do
    drawio -x -f png -s 2 -b 24 -e -o "docs/images/architecture/$(basename "$f" .drawio).png" "$f"
done
```

The diagrams were last exported with draw.io 31.4.5 on 19 September 2026.
