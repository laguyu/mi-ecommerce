<div align="center">

# Mi Ecommerce

**A full-stack commerce application for product discovery, checkout, order management, and store administration.**

Built with Laravel, Vue, and Vite.

</div>

## Overview

Mi Ecommerce brings a customer storefront and a role-protected admin area together in one Laravel application. The Vue storefront uses Laravel endpoints for catalog and checkout operations, while business rules such as promotion pricing and payment completion are handled on the server.

The repository also includes deployment examples and supporting operational documentation.

## Features

- **Storefront:** home page merchandising, category and brand browsing, product details, catalog filters, favorites, and a browser-persisted cart.
- **Checkout and orders:** configurable delivery fees and free-shipping thresholds, coupon validation, order summaries, and customer order history.
- **Payments:** Stripe Checkout and PayPal order flows, return handling, and provider webhook endpoints.
- **Administration:** permission-protected management for products, categories, brands, promotions, coupons, banners, product carousels, orders, users, and site settings.
- **Promotions and pricing:** time-bound product promotions are resolved server-side; the highest applicable discount is selected.
- **Customer communications:** order email notifications, contact messages, newsletter subscriptions, and exportable subscriber lists.
- **Content and operations:** editable policy pages, store delivery and theme settings, product/order exports, and PDF order documents.
- **Media storage:** Laravel filesystem integration supports the configured default disk, including local/public storage and S3-compatible storage.

## Technology

| Area | Implementation |
| --- | --- |
| Server | PHP 8.3, Laravel 13, Eloquent ORM |
| Web UI | Vue 3, Vite 8, Tailwind CSS 4 |
| Database | SQLite by default for local setup; MySQL-compatible configuration is available |
| Authentication | Laravel web authentication |
| Payments | Stripe and PayPal integrations with webhook routes |
| Documents and exports | Laravel Dompdf and Laravel Excel |
| Deployment assets | Dockerfile, Render blueprint, and Vercel configuration |

## Application Layout

```text
app/
  Http/Controllers/   Storefront, account, admin, and API request handling
  Models/             Eloquent domain models
  Services/           Pricing, payment, and order notification logic
  Support/            Shared application support, including media storage
database/
  migrations/         Database schema
  seeders/            Local demonstration data
resources/js/         Vue storefront and shared frontend code
resources/views/      Laravel application and admin views
routes/               Web and versioned API routes
tests/                PHPUnit unit and feature test suites
```

## Requirements

- PHP 8.3 or later with the extensions required by Laravel and the configured database driver.
- Composer 2.
- Node.js 20 or later and npm.
- SQLite for the default local setup, or a configured MySQL-compatible database.

## Local Setup

1. Install PHP and JavaScript dependencies and create the local environment file:

	```bash
	composer install
	cp .env.example .env
	```

	In PowerShell, use `Copy-Item .env.example .env` for the second command.

2. Generate an application key. For the default SQLite setup, create the database file if it does not already exist:

	```bash
	php artisan key:generate
	```

	```powershell
	New-Item -ItemType File -Force database/database.sqlite
	```

	Keep `DB_CONNECTION=sqlite` in `.env` for SQLite. To use MySQL, configure `DB_*` values for a database you have created.

3. Apply migrations and, for local exploration only, load the sample catalog and accounts:

	```bash
	php artisan migrate --seed
	```

	The seeder creates predictable demo passwords. Never run it against a public or production database; use dedicated credentials and production data instead.

4. Install frontend dependencies and build assets:

	```bash
	npm ci
	npm run build
	```

5. Start the Laravel development server:

	```bash
	php artisan serve
	```

	For frontend hot reload during development, run `npm run dev` in a second terminal.

Payment credentials are optional for browsing the application. To exercise payment flows, add sandbox credentials and webhook settings from the provider to `.env`. See [DOCUMENTACION_ECOMMERCE.md](DOCUMENTACION_ECOMMERCE.md) for the project-specific Stripe and PayPal walkthrough (Spanish).

## Tests and Build

Run the configured PHP test suite:

```bash
php artisan test
```

Build the production frontend assets:

```bash
npm run build
```

The current PHPUnit suite contains Laravel example tests, not comprehensive coverage of checkout, payment providers, or administration workflows. Payment and order lifecycle changes should be verified with provider sandbox credentials and end-to-end checks before release. The repository includes a payment checklist in [CHECKLIST_PAGOS_E2E.md](CHECKLIST_PAGOS_E2E.md) (Spanish).

## Deployment Notes

The repository includes a Docker-based Render configuration, a Vercel PHP entry point, and a Render/TiDB deployment guide at [GUIA_DEPLOY_GRATIS_RENDER_NEON.md](GUIA_DEPLOY_GRATIS_RENDER_NEON.md) (Spanish). These files are deployment starting points, not evidence that a public instance is currently available. Configure the database, storage, mail, payment credentials, and application URL in the target environment.

Review deployment settings before exposing an instance:

- `vercel.json` currently sets `APP_DEBUG=true`; production deployments must use `APP_DEBUG=false` and a non-public error configuration.
- The Render blueprint enables demo/auth seeding, and the Docker startup command can run seeders. Disable demo accounts and use non-default credentials before connecting any public or production database.
- The included Render plan uses local filesystem storage by default. Uploaded files may not persist across restarts on an ephemeral filesystem; configure persistent or object storage for production use.
- Review the startup migration behavior and all environment variables against [CHECKLIST_SEGURIDAD_MVP_PRODUCCION.md](CHECKLIST_SEGURIDAD_MVP_PRODUCCION.md) (Spanish) before release.

## Further Documentation

- [Ecommerce feature and payment guide](DOCUMENTACION_ECOMMERCE.md) (Spanish)
- [Detailed technical documentation](DOCUMENTACION_TECNICA_INTEGRAL_DETALLADA.md) (Spanish)
- [Deployment guide](GUIA_DEPLOY_GRATIS_RENDER_NEON.md) (Spanish)
- [Production security checklist](CHECKLIST_SEGURIDAD_MVP_PRODUCCION.md) (Spanish)

