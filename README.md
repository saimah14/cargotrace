# CargoTrace

CargoTrace is a cargo shipment tracking web application. Operators manage shipments, tracking events and delivery exceptions, while customers follow the progress of their own shipments on a live map.

## Features

- Authentication with Laravel Sanctum (token based) and three roles: `admin`, `operator`, `customer`
- Shipment lifecycle: `CREATED` > `BOOKED` > `PICKED_UP` > `IN_TRANSIT` > `DELIVERED`
- Tracking events with timeline and live map (Leaflet + OpenStreetMap)
- Automatic ETA recalculation and delay detection when an exception is reported
- Exception management: `OPEN` > `INVESTIGATING` > `RESOLVED`
- Dashboard with summary cards and charts (Chart.js)
- Search, filter and pagination for shipments
- In-app notifications
- External APIs: exchange rates, shipping news and weather at the cargo location

## Tech Stack

- PHP 8.1+, Laravel 10, MySQL (XAMPP)
- Laravel Sanctum for API authentication
- Blade, vanilla JavaScript and the Fetch API for the front end
- Leaflet and Chart.js loaded from cdnjs
- PHPUnit feature tests

## Setup

```bash
git clone https://github.com/saimah14/cargotrace.git
cd cargotrace
composer install
cp .env.example .env
php artisan key:generate
```

1. Start Apache and MySQL in XAMPP and create an empty database named `cargotrace`.
2. Set `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` in `.env`.
3. Create the tables and demo data:

```bash
php artisan migrate --seed
php artisan serve
```

If the demo data is not created, run `php artisan db:seed --class=DemoShipmentSeeder`.

Open http://127.0.0.1:8000 and log in with a demo account (`admin@cargotrace.test`, `operator@cargotrace.test` or `customer@cargotrace.test`). The demo passwords are defined in the seeders.

## Roles

| Role | Access |
| --- | --- |
| admin | Full access, dashboard, master data (carriers, locations) |
| operator | Manage shipments, tracking events and exceptions, dashboard |
| customer | View only their own shipments and notifications |

Permissions are enforced on the server side, not only in the interface.

## Business Rules

- A shipment can only move to the next status in the lifecycle order.
- Reporting an exception with delay hours shifts the estimated arrival time.
- Exception severity from the delay: 0-6 hours is `LOW`, over 6 up to 24 hours is `MEDIUM`, over 24 hours is `HIGH`.
- A shipment is marked as delayed when its delay passes the configured threshold (`DELAY_THRESHOLD_HOURS` in `ExceptionController`).
- Tracking events cannot be dated in the future.

## API Overview

All endpoints are under `/api` and use a Bearer token, except register and login.

| Area | Endpoints |
| --- | --- |
| Auth | `POST /register`, `POST /login`, `POST /logout`, `GET /user` |
| Shipments | `GET/POST /shipments`, `GET/PUT/DELETE /shipments/{id}` |
| Tracking | `GET/POST /shipments/{id}/events`, `POST /shipments/{id}/status` |
| Exceptions | `GET/POST /shipments/{id}/exceptions`, `PATCH /exceptions/{id}/status` |
| Master data | `/carriers`, `/locations` (full CRUD) |
| Dashboard | `GET /dashboard` |
| Notifications | `GET /notifications`, `POST /notifications/read-all`, `PATCH /notifications/{id}/read` |
| External | `GET /rates`, `GET /news`, `GET /weather?lat=&lon=` |

## External APIs

| Service | Used for | Notes |
| --- | --- | --- |
| Frankfurter (European Central Bank) | USD, EUR and IDR exchange rates | cached for 1 hour |
| Google News RSS | Container shipping and port congestion news | cached for 15 minutes |
| Open-Meteo | Current weather at a shipment's last known location | cached for 30 minutes, informational only |

Every call has a timeout and retry. If a service fails, the last successful response is returned and marked as `stale`. If nothing is cached, the API returns HTTP 503 with a clear message instead of crashing the page.

## Testing

Tests use a separate database. The test class refuses to run when the database name does not end with `_test`, so the main data is never wiped.

1. Create an empty database named `cargotrace_test`.
2. Set it in `phpunit.xml` (`DB_DATABASE`).
3. Run:

```bash
php artisan test
```

## Project Structure

- `app/Http/Controllers/Api` - API controllers
- `app/Models` - Eloquent models
- `database/migrations`, `database/seeders` - schema and demo data
- `resources/views/app.blade.php` - single page interface
- `public/js/app.js` - front end logic
- `tests/Feature` - feature tests

## Author

Saimah