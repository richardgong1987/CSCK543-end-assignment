# MySQL export

A ready-to-import dump of the `csck543` database, so the schema and the seeded recipe
data can be inspected or restored without running Laravel's migrations.

The authoritative definition of the schema is still
`database/migrations/`; [docs/database-design.md](../../docs/database-design.md)
explains why the tables are shaped the way they are. These files are a snapshot of what
those migrations and seeders produce.

| File | Contents |
| --- | --- |
| [01-schema.sql](01-schema.sql) | `CREATE DATABASE` and all 21 `CREATE TABLE` statements, with keys, foreign keys and indexes |
| [02-data.sql](02-data.sql) | `INSERT` statements for the 17 tables that hold real data |

Import them in that order.

## Importing

Into MySQL:

```sh
mysql -h 127.0.0.1 -P 3306 -u root -p < database/sql/01-schema.sql
mysql -h 127.0.0.1 -P 3306 -u root -p < database/sql/02-data.sql
```

Into XAMPP's MariaDB, whose client and port differ — see
[docs/xampp.md](../../docs/xampp.md):

```sh
/Applications/XAMPP/xamppfiles/bin/mysql -h 127.0.0.1 -P 3307 -u root < database/sql/01-schema.sql
/Applications/XAMPP/xamppfiles/bin/mysql -h 127.0.0.1 -P 3307 -u root < database/sql/02-data.sql
```

Or open both files in phpMyAdmin's **Import** tab.

Both files create and select the `csck543` database themselves, so no database needs to
exist beforehand. `01-schema.sql` drops each table before recreating it, so importing
over an existing `csck543` replaces it.

Both imports have been checked against MySQL 9.1 and against MariaDB as XAMPP ships it.
The dump avoids MySQL-9-only collations for that reason.

## What is in it

8 recipes taken from BBC Food, with their 114 ingredient rows, 48 steps and the
reference data they classify against — 7 chefs, 5 cuisines, 6 categories, 8 dietary
tags, 8 time bands, 13 units and 81 ingredients. Six user accounts supply the 8
favourites and 27 ratings that the account pages display.

The four sample accounts all use the password `password`:

| Email | Name |
| --- | --- |
| `amelia@example.test` | Amelia Carter |
| `ben@example.test` | Ben Okafor |
| `chen@example.test` | Chen Wei |
| `dara@example.test` | Dara Novak |

`cache`, `cache_locks`, `sessions` and `password_reset_tokens` are created by
`01-schema.sql` but deliberately left empty: they hold nothing but runtime state, which
the application refills on its own.

## Regenerating

After a schema or seeder change, re-export with the same two commands used to produce
these files:

```sh
mysqldump -h 127.0.0.1 -P 3306 -u root -p --databases csck543 \
  --single-transaction --no-tablespaces --set-gtid-purged=OFF \
  --default-character-set=utf8mb4 --skip-dump-date --no-data \
  > database/sql/01-schema.sql

{ echo 'USE `csck543`;'; echo; \
  mysqldump -h 127.0.0.1 -P 3306 -u root -p csck543 \
    --single-transaction --no-tablespaces --set-gtid-purged=OFF \
    --default-character-set=utf8mb4 --skip-dump-date \
    --no-create-info --complete-insert \
    --ignore-table=csck543.cache --ignore-table=csck543.cache_locks \
    --ignore-table=csck543.sessions --ignore-table=csck543.password_reset_tokens; \
} > database/sql/02-data.sql
```

MySQL 9 writes `COLLATE utf8mb4_0900_ai_ci` and `DEFAULT ENCRYPTION='N'` into the
`CREATE DATABASE` line, and MariaDB rejects both. Change that line back to
`COLLATE utf8mb4_unicode_ci` and delete the encryption clause, as `01-schema.sql` has
it, or the XAMPP import fails on its first statement.
