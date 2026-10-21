# DOST-PES Asset Lifecycle Management (ALM) System

A web application that tracks DOST-PES physical assets from registration through custody, maintenance, transfer and disposal.

Built by **Dev Logic (Group 41)** for **DOST-PES** as a QUT IFN736 industry project. System version **v2.4.1**.

## Features

- **Asset register:** tag, category, location, serial number, warranty, useful life, salvage value and photos.
- **Custody:** assign an asset to a holder, who must acknowledge receipt.
- **Maintenance:** issue reports, approval and technician assignment, five-step checklist, activity log and SLA response/resolution targets (set in minutes or hours).
- **Scheduled maintenance:** recurring schedules (daily to annual); a daily job creates the work order when a schedule falls due and notifies the default technician.
- **Transfers and disposals:** requests with admin approval.
- **Depreciation and reports:** straight-line depreciation, calculated monthly; reports exportable as PDF, Excel or CSV.
- **Security:** four roles with enforced area access, optional MFA, accounts created only by a Technical Admin (self-registration is disabled).

| Role | Area | Main tasks |
|:--|:--|:--|
| Administrative Admin | `/admin` | Registers and assigns assets; approves requests, transfers and disposals; manages SLA policies and maintenance schedules; views reports and depreciation |
| Technical Admin | `/techadmin` | Creates, activates and deactivates user accounts; views roles |
| Technician | `/technician` | Works assigned work orders; views the maintenance schedule |
| Asset Holder | `/holder` | Acknowledges assets in their custody; reports issues; requests transfers |

Every role can hold assets, acknowledge assignments and report issues on its own assets.

## Tech stack

| Layer | Technology |
|:--|:--|
| Framework | Laravel 13 |
| Language | PHP 8.4.1 or newer (required by the committed `composer.lock`) |
| Database | PostgreSQL 15 or newer (developed on 18) |
| Web server | nginx with PHP-FPM, or Apache, with HTTPS |
| Build tools | Composer 2; Node.js 22.12 or newer with npm (required by the committed `package-lock.json`) |
| File storage | Server disk by default; S3 optional (needs a small code change) |

## Quick start (laptop, for testing and training)

Requires PHP 8.4.1+ (with the `pdo_pgsql`, `fileinfo`, `gd`, `mbstring`, `curl`, `zip`, `bcmath` and `intl` extensions), Composer 2, Node.js 22.12+ and PostgreSQL 15+. `php artisan serve` is for testing only, not production. Run `composer check-platform-reqs --lock` before `composer install` to confirm PHP and its extensions are ready. Step-by-step Mac and Windows instructions are in the Setup Guide.

```bash
git clone https://github.com/alwints12-source/alm-system.git
cd alm-system
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
```

On Windows, run the same commands in PowerShell, but use `npm.cmd install` and `npm.cmd run build` (PowerShell often blocks `npm`) and `copy .env.example .env`. The Setup Guide (Mac and Windows) covers selecting the right PHP and Node.js in Laravel Herd and enabling missing PHP extensions.

Create an empty PostgreSQL database named `alm_system`, then set `DB_USERNAME` and `DB_PASSWORD` in `.env`. Set `APP_URL` to the exact address you open in the browser, including the port.

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

`migrate` builds all 27 tables from `database/schema/base_schema.sql` and then applies the later migrations. `--seed` adds the four starter accounts, seven asset categories and six locations; the asset register starts empty.

To run the maintenance scheduler on a laptop, leave this running in a second terminal:

```bash
php artisan schedule:work
```

Open <http://127.0.0.1:8000> and sign in with one of the seeded test accounts:

| Role | Email | Password |
|:--|:--|:--|
| Administrative Admin | `admin@test.com` | `password` |
| Technical Admin | `techadmin@test.com` | `password` |
| Asset Holder | `holder@test.com` | `password` |
| Technician | `technician@test.com` | `password` |

These accounts are for local testing and training only. Never use them on a live server: change every password, or deactivate the accounts, before go-live (see the Deployment Guide).

## Configuration notes

- **Scheduler:** recurring maintenance needs Laravel's scheduler. On a server, add one cron entry for the web user. Once a day, `maintenance:generate-work-orders` creates a work order for each active schedule that is due (unless one is already open), assigns it to the schedule's default technician and notifies them. Without the cron entry (or `schedule:work`), no work orders are created from schedules.

  ```
  * * * * * cd /var/www/alm && php artisan schedule:run >> /dev/null 2>&1
  ```

- **Photos:** stored in `storage/app/public` and served through the `public/storage` link (`php artisan storage:link`). Uploads of up to 5 MB need PHP's `upload_max_filesize` and `post_max_size` raised above the default.
- **Email:** `MAIL_MAILER=log` writes messages to `storage/logs/laravel.log`. Password-reset emails reach users only once an SMTP server is configured in `.env`.
- **Backups:** back up the database and the `storage/app/public` folder together.

## Documentation

| Document | Purpose |
|:--|:--|
| User Manual | How to use the system, by role |
| Deployment Guide | Hosting, installing, backups and updating |
| Technical Specifications | Architecture, data, security and known limitations |
| System Architecture | One-page architecture diagram |
| Database Schema and ERD | Every table and column, with entity-relationship diagrams |
| Setup Guide (Mac and Windows) | Step-by-step laptop installation for the team |

## Known limitations

See the Technical Specifications. Main points: the application time zone is fixed to `Australia/Brisbane` in `config/app.php` (change it to `Asia/Manila` for DOST-PES), no audit trail yet, no self-service MFA reset, asset categories and locations cannot be edited in the application, S3 storage needs a code change, and the system has not been load-tested or independently security-tested.

## Security

Never commit `.env` or real credentials. Set `APP_ENV=production` and `APP_DEBUG=false` on any server.

## Contact

Dev Logic (Group 41), Queensland University of Technology.
