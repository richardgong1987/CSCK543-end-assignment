# Running Recipe Box on XAMPP (Windows)

The brief assesses the application on Apache via XAMPP, in Chrome. This guide takes a
Windows machine from nothing to the site running at <http://recipebox.test>.

> **Not yet tried end to end on Windows.** The steps are assembled from the XAMPP and PHP
> download pages and the project's own requirements. The first person to follow them,
> please fix anything that does not match what you see, and update the "Target
> environment" row in [status.md](status.md).

For development on macOS or Linux, [setup.md](setup.md) is simpler.

## Why XAMPP needs a newer PHP

The newest XAMPP for Windows, **8.2.12**, bundles PHP 8.2.12, Apache 2.4.58 and MariaDB
10.4.32. This project needs **PHP 8.4.1 or newer**: `composer.lock` pins Symfony 8 and
Pest 5, which require it, so with XAMPP's own PHP `composer install` stops with an error.

The fix is to keep XAMPP's Apache and MariaDB and swap its PHP for a current one. The
project is developed on PHP 8.5, so use that. MariaDB 10.4 is fine: Laravel 13 supports
MariaDB 10.3 and newer, and the application talks to it as MySQL.

## 1. Install XAMPP

1. Download **XAMPP 8.2.12 for Windows** from
   [apachefriends.org](https://www.apachefriends.org/download.html) and install it to
   `C:\xampp` (the paths below assume that folder).
2. Open the **XAMPP Control Panel** and start **MySQL** once to check it runs. Leave
   Apache stopped for now.

## 2. Replace XAMPP's PHP with PHP 8.5

1. Install the **Microsoft Visual C++ Redistributable for Visual Studio 2015–2022 (x64)**
   from <https://aka.ms/vs/17/release/vc_redist.x64.exe>. PHP for Windows needs it.
2. From [php.net's Windows downloads](https://www.php.net/downloads.php?os=windows),
   download PHP 8.5, **VS17 x64 Thread Safe** — the zip is named like
   `php-8.5.10-Win32-vs17-x64.zip`.
   **It must be Thread Safe.** XAMPP loads PHP as an Apache module, and only the
   Thread Safe build works that way; with Non Thread Safe, Apache will not start.
3. Rename `C:\xampp\php` to `C:\xampp\php-8.2`, then extract the zip into a new, empty
   `C:\xampp\php`.
4. In `C:\xampp\php`, copy `php.ini-development` to `php.ini` and edit `php.ini`:

   ```ini
   extension_dir = "C:\xampp\php\ext"

   ; Remove the leading ";" from each of these lines
   extension=curl
   extension=fileinfo
   extension=intl
   extension=mbstring
   extension=openssl
   extension=pdo_mysql
   extension=pdo_sqlite
   extension=sqlite3
   extension=zip
   ```

   `pdo_mysql` talks to MariaDB; `pdo_sqlite` and `sqlite3` are for the test suite,
   which runs on SQLite; `zip` lets Composer unpack packages quickly.
5. Open `C:\xampp\apache\conf\extra\httpd-xampp.conf` and make sure these lines point at
   the new folder (they usually already do):

   ```apache
   LoadFile "C:/xampp/php/php8ts.dll"
   LoadModule php_module "C:/xampp/php/php8apache2_4.dll"
   PHPIniDir "C:/xampp/php"
   ```

6. Add `C:\xampp\php` to your Windows `PATH` (Settings → System → About → Advanced
   system settings → Environment Variables → `Path` → New), so the terminal uses the same
   PHP as Apache. Open a **new** terminal and check:

   ```powershell
   php -v            # must say PHP 8.5
   php -m            # the list must include pdo_mysql, pdo_sqlite, mbstring, openssl
   ```

7. Start **Apache** in the XAMPP Control Panel and open <http://localhost/dashboard/>.
   If Apache will not start, see [Troubleshooting](#troubleshooting).

XAMPP's bundled phpMyAdmin may not work with PHP 8.5. The application does not need it:
`php artisan migrate` creates the database for you.

## 3. Install Composer, Node.js and pnpm

1. **Composer:** run the installer from [getcomposer.org](https://getcomposer.org/download/).
   When it asks for PHP, choose `C:\xampp\php\php.exe`.
2. **Node.js** 20 or newer, from [nodejs.org](https://nodejs.org).
3. **pnpm**, in a new terminal:

   ```powershell
   npm install -g pnpm
   ```

4. **Git**, from [git-scm.com](https://git-scm.com), if you do not have it.

## 4. Get the code and configure it

In a terminal:

```powershell
cd C:\xampp\htdocs
git clone git@github.com:richardgong1987/CSCK543-end-assignment.git
cd CSCK543-end-assignment
composer install
pnpm install
Copy-Item .env.example .env
php artisan key:generate
```

Open `.env` and change the database block for XAMPP's MariaDB, whose `root` account
has no password by default. `.env.example` starts on SQLite, so `DB_CONNECTION` must
change too:

```dotenv
APP_URL=http://recipebox.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=csck543
DB_USERNAME=root
DB_PASSWORD=
```

Use `mysql`, not `mariadb`, for `DB_CONNECTION`: the ratings table adds its 1–5 score
checks only on the `mysql` connection, and MariaDB 10.4 supports them.

With MySQL running in the Control Panel, create the tables and the sample data:

```powershell
php artisan migrate --seed
```

It offers to create the `csck543` database if it does not exist — answer yes.

## 5. Point Apache at the project

Apache must serve the project's **`public` folder**, never the project folder itself,
which holds `.env` and the source code.

1. Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` and add at the end:

   ```apache
   <VirtualHost *:80>
       ServerName localhost
       DocumentRoot "C:/xampp/htdocs"
   </VirtualHost>

   <VirtualHost *:80>
       ServerName recipebox.test
       DocumentRoot "C:/xampp/htdocs/CSCK543-end-assignment/public"
       <Directory "C:/xampp/htdocs/CSCK543-end-assignment/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   The first block keeps <http://localhost> working for XAMPP's own pages.
   `AllowOverride All` lets `public/.htaccess` send every address to Laravel.
2. Check that `C:\xampp\apache\conf\httpd.conf` still has these lines **without** a
   leading `#` (XAMPP enables them by default):

   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   Include conf/extra/httpd-vhosts.conf
   ```

3. Open **Notepad as administrator**, open `C:\Windows\System32\drivers\etc\hosts`, and
   add:

   ```text
   127.0.0.1  recipebox.test
   ```

4. Restart Apache in the Control Panel.

## 6. Build the assets and open the site

```powershell
pnpm run build
```

Then **delete `public\hot` if it exists**. A Vite dev server (`composer run dev` or
`pnpm run dev`) writes that file, and while it is there every page loads its CSS and
JavaScript from the dev server instead of `public\build` — with no dev server running,
the site shows up unstyled.

Open <http://recipebox.test> in Chrome and log in as `amelia@example.test` with the
password `password`. The other sample accounts are listed in
[setup.md](setup.md#signing-in).

## After pulling new changes

```powershell
git pull
composer install
pnpm install
pnpm run build
php artisan migrate
```

To go back to a clean database with only the sample data:
`php artisan migrate:fresh --seed`. It deletes every account, including any you
registered by hand.

Password reset emails are not sent in development: the whole message, link included,
is written to `storage\logs\laravel.log`.

## Running the tests on Windows

`composer test` runs the unit and feature tests on SQLite, so it needs `pdo_sqlite` and
`sqlite3` enabled (step 2.4) but not MariaDB. The end-to-end tests (`pnpm run test:e2e`)
need Google Chrome installed; they have not yet been tried on Windows. See
[testing.md](testing.md).

## Troubleshooting

| What you see | Likely cause and fix |
| --- | --- |
| Apache will not start after swapping PHP | The PHP zip was **Non Thread Safe** or 32-bit (x86) — download the x64 Thread Safe build again. Or the Visual C++ redistributable is missing (step 2.1). The reason is in `C:\xampp\apache\logs\error.log`. |
| Apache will not start, "port 80 in use" | Another program (IIS, Skype, another web server) holds port 80. Stop it, or change `Listen 80` in `httpd.conf` and use `http://recipebox.test:8080`. |
| `composer install` fails with "php >=8.4.1" or "requires php ^8.4" | The terminal is still using an older PHP. Run `where php`; `C:\xampp\php\php.exe` must be first. Open a new terminal after changing `PATH`. |
| "could not find driver" | `extension=pdo_mysql` is still commented out in `C:\xampp\php\php.ini`, or `extension_dir` is wrong. Restart Apache after fixing it. |
| The home page works but every other page is "Not Found" | Apache is not reading `public\.htaccess`: check `AllowOverride All`, that `mod_rewrite` is loaded, and that `DocumentRoot` ends in `\public`. |
| The site opens XAMPP's dashboard instead | The `hosts` entry or the virtual host is missing, or Apache was not restarted. |
| Pages have no styling | `pnpm run build` has not been run, or `public\hot` exists — delete it. |
| "No application encryption key has been specified" | Run `php artisan key:generate`. |
| "SQLSTATE[HY000] [2002]" or "Connection refused" | MySQL is not started in the XAMPP Control Panel. |
| "Access denied for user 'root'" | Your XAMPP `root` has a password; put it in `DB_PASSWORD`. |
| A blank page or "500 Server Error" | The error is in `storage\logs\laravel.log`. Keep `APP_DEBUG=true` in `.env` on your own machine to see it in the browser. |
| phpMyAdmin shows errors | XAMPP's phpMyAdmin is older than PHP 8.5. The application does not need it; use any MySQL client, such as HeidiSQL, or update phpMyAdmin separately. |

## Where these facts come from

Checked on 15 September 2026:

- XAMPP versions and what they bundle: [apachefriends.org downloads](https://www.apachefriends.org/download.html)
- PHP for Windows builds, the Thread Safe requirement for Apache, and the Visual C++
  redistributable: [php.net Windows downloads](https://www.php.net/downloads.php?os=windows)
- The PHP version this project needs: the `require.php` of the packages in
  `composer.lock` (Symfony 8 and Pest 5)

For putting the application on a real server rather than your own machine, continue
with [deployment.md](deployment.md).
