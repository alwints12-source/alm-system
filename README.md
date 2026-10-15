# DOST-PES Asset Lifecycle Management System

A web application for managing the full lifecycle of organisational assets: registration, assignment to custodians, maintenance and work orders, transfers, disposal, depreciation and reporting. Built with Laravel and PostgreSQL for DOST-PES.

## Requirements

- PHP 8.3 or newer, with the pdo_pgsql, mbstring, xml, gd and zip extensions
- Composer 2
- PostgreSQL 14 or newer (developed on PostgreSQL 18)
- Node.js 20 or newer, with npm

## Installation

~~~bash
git clone https://github.com/alwints12-source/alm-system.git
cd alm-system
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
~~~

Create an empty PostgreSQL database named `alm_system`, then open `.env` and set `DB_USERNAME` and `DB_PASSWORD` to match your PostgreSQL account.

~~~bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
~~~

Open http://127.0.0.1:8000 in a browser. On Windows, use the same commands in PowerShell, and copy `.env.example` to `.env` with `copy .env.example .env`.

## Starting accounts

The seeder creates one account for each role. All four use the password `password`. **Change these passwords, or deactivate the accounts, before any real use.**

| Role | Email |
|------|-------|
| Administrative Admin | admin@test.com |
| Technical Admin | techadmin@test.com |
| Asset Holder | holder@test.com |
| Technician | technician@test.com |

Self-registration is disabled. The Technical Admin creates all other accounts from the Users page.

## Roles

- **Administrative Admin**: registers and assigns assets, approves requests, transfers and disposals, manages SLA policies and maintenance schedules, and views reports and depreciation.
- **Technical Admin**: creates and manages user accounts and views roles. Has no access to asset data.
- **Technician**: works on assigned work orders and the maintenance schedule.
- **Asset Holder**: views the assets in their custody, reports issues and requests transfers.

Every role can hold assets, acknowledge assignments and report issues on assets in their own custody.

## Notes for deployment

- `php artisan serve` is for development only. In production use nginx or Apache with PHP-FPM and HTTPS.
- Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
- Email is set to `MAIL_MAILER=log`, so password reset links are written to `storage/logs/laravel.log` instead of being sent. Configure an SMTP service in `.env` so reset emails are delivered.
- Uploaded asset photos are stored in `storage/app/public`. Include this folder, and the database, in backups.
