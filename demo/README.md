# htmx 4 demo

Interactive showcase of every htmx 4 feature on Laravel 13, built on
[`imsus/laravel-htmx`](../). This app is a monorepo sibling of the package and
consumes it at HEAD through a composer path repository, so it always demos the
current commit.

## Local development

```sh
composer install
cp .env.example .env            # if .env is missing
php artisan key:generate
php artisan vendor:publish --tag=laravel-htmx-config --tag=laravel-htmx-assets --force
php artisan serve
```

The `file` session driver keeps local runs free of a database. Feature demos
use the sqlite file created by `composer setup` and read the shared seeded
Linear-style workspace (projects, issues, activity logs):

```sh
php artisan db:seed
```

## Patterns

The homepage lists every pattern showcase; each links to its own page under
`/patterns/<slug>`. All patterns drive the same seeded workspace data —
adding a pattern never needs new demo data, just a new page (and any fragment
routes it swaps).

After changing the package's `config/` or `public/` files, re-run the
`vendor:publish` command above so the copies under `config/` and
`public/vendor/laravel-htmx/` track the package.

## Tests

```sh
php artisan test
```

## Deployment (Laravel Cloud)

This is the monorepo application: import this repository on Laravel Cloud and
select `demo/` as the root directory (or pass `--root-directory=demo` to the
CLI). Target PHP 8.5. Cloud sets its own environment, so configure a real
database and session/cache driver there — sqlite is local-only.

`composer.lock` is committed for deterministic builds; the path-repository
package is copied (not symlinked) into `vendor/` during install.
