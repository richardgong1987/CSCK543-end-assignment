# Setting up for development

How to get the application running on your own machine with `composer run dev`. To run
it on Apache through XAMPP, the way it is assessed, follow [xampp.md](xampp.md) instead.

## Requirements

| Tool     | Version                                                  |
| -------- | -------------------------------------------------------- |
| PHP      | 8.4.1 or newer (developed on 8.5): the locked Symfony 8 and Pest 5 packages need it, although `composer.json` still says `^8.3` |
| Composer | 2                                                        |
| MySQL    | 8 or newer, or MariaDB 10.3 or newer (XAMPP ships MariaDB 10.4) |
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
| `pnpm run test:e2e`              | Run the end-to-end tests in Chrome (build first)  |
| `php artisan migrate --seed`     | Create the tables and load the sample data        |
| `php artisan migrate:fresh --seed` | Rebuild the database from scratch               |
| `pnpm run build`                 | Compile the CSS and JavaScript for production     |
| `vendor/bin/pint`                | Apply the project's PHP code style                |
