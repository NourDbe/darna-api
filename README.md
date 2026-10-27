# Darna — Real Estate API

Darna is an API-first real-estate backend built with Laravel. It provides versioned REST API endpoints for browsing and managing property listings, with office ownership checks, filtering, cursor pagination, and Redis-backed caching.

## Features

- Versioned API endpoints: V1 and V2.
- Different price response formats between API versions.
- Property listing filters:
  - Governorate
  - Listing type
  - Minimum and maximum price
- Cursor pagination for public property listings.
- Offset pagination support for office property listings.
- Laravel Sanctum authentication for protected operations.
- Office ownership verification for property updates and deletion.
- Form Request validation with Arabic validation messages.
- API Resources for controlled JSON responses.
- Eloquent relationships and query scopes.
- Polymorphic property image relationships.
- Redis-backed property detail and listing caches.
- Automated feature tests.

## Requirements

- PHP 8.3 or later, compatible with the project's Composer constraints.
- Composer.
- SQLite.
- Redis-compatible server.
- Node.js and npm only if you need to build the included frontend assets.

## Installation

Clone the repository and enter the project directory:

```bash
git clone https://github.com/NourDbe/darna-api.git
cd darna-api
```

Install PHP dependencies:

```bash
composer install
```

Create the environment file if it does not exist:

```bash
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Ensure SQLite is enabled in PHP and configure the database path in `.env` if required. Then run the migrations and seeders:

```bash
php artisan migrate --seed
```

Start the development server:

```bash
php artisan serve
```

The API will normally be available at `http://127.0.0.1:8000`.

> Run migrations and seeders only against the intended development database. Back up any database containing data you need to preserve.

## Environment configuration

The example environment uses SQLite:

```dotenv
DB_CONNECTION=sqlite
```

Darna's property cache uses the Redis cache store. Configure the local environment to use Redis and Predis:

```dotenv
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

These settings assume that a Redis-compatible server is running locally on port `6379`. If your server uses another host, port, or password, update the values accordingly.

The committed `.env.example` may contain Laravel defaults. Ensure your local `.env` is configured for Redis before testing Redis-backed endpoints. Do not commit `.env` or other files containing secrets.

After changing environment settings, clear cached Laravel configuration:

```bash
php artisan config:clear
```

### Redis on Windows

Install and start a Redis-compatible server, such as Memurai, using its supported Windows installation instructions. Confirm that the service is running and listening on the configured host and port before testing the application.

## API endpoints

The API is versioned under `/api/v1` and `/api/v2`.

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/v1/properties` | List and filter available properties |
| GET | `/api/v1/properties/{property}` | Retrieve property details |
| PATCH | `/api/v1/properties/{property}` | Update a property owned by the authenticated office |
| DELETE | `/api/v1/properties/{property}` | Delete a property owned by the authenticated office |
| GET | `/api/v2/properties` | List and filter available properties using V2 response formatting |
| GET | `/api/v2/properties/{property}` | Retrieve property details using V2 response formatting |
| PATCH | `/api/v2/properties/{property}` | Update a property owned by the authenticated office |
| DELETE | `/api/v2/properties/{property}` | Delete a property owned by the authenticated office |

The protected update and delete endpoints require a valid Laravel Sanctum authentication context. The project does not provide a full login flow as part of this API setup.

## API version differences

The versions share the property service and business logic, but format the price differently.

### V1 response

```json
{
  "data": {
    "id": 1,
    "title": "Example property",
    "price": 50000,
    "currency": "USD"
  }
}
```

### V2 response

```json
{
  "data": {
    "id": 1,
    "title": "Example property",
    "price": {
      "amount": 50000,
      "currency": "USD"
    }
  }
}
```

These examples illustrate the price structure; actual responses include the other fields defined by the relevant API Resource.

## Filtering and pagination

The public listing endpoint accepts the following query parameters:

| Parameter | Description |
|---|---|
| `per_page` | Number of items per page; allowed range is 1–50 |
| `governorate_id` | Filter by governorate ID |
| `listing_type` | Filter by listing type |
| `min_price` | Minimum property price |
| `max_price` | Maximum property price; must be greater than or equal to `min_price` |
| `cursor` | Cursor used to retrieve the next or previous cursor page |

Example:

```http
GET /api/v1/properties?per_page=10&governorate_id=1&min_price=20000&max_price=100000
```

Cursor pagination example:

```http
GET /api/v1/properties?per_page=10
```

Use the cursor returned by the pagination response when requesting the next page. Public listings use cursor pagination; office dashboard queries use offset pagination in the service layer.

## Authentication and authorization

Protected endpoints require authentication through Laravel Sanctum.

- An office can update or delete properties associated with that office.
- An office cannot update or delete another office's properties.
- Unauthenticated requests to protected endpoints should be rejected.
- Validation errors and authorization failures should be handled separately.

The property ownership check is implemented through the `PropertyOwnershipChecker` contract and its default service implementation.

## Property visibility

The default property query scope returns properties that have an approval timestamp and an `available` status. Office queries may explicitly bypass the global scope to manage properties that are not publicly available.

## Caching

Property detail and listing data are cached using the Redis cache store. The current cache service defines a TTL of 300 seconds (5 minutes).

The service includes invalidation methods for property details and property lists. Property updates and deletions call the invalidation service so stale cached data does not remain available after these operations.

The cache stores underlying property data rather than version-specific final JSON, allowing V1 and V2 to format the same data independently.

Redis cache tags are used by the cache service to group related keys. Ensure the configured cache driver supports the tag operations used by the project.

## Running tests

Run the automated test suite:

```bash
php artisan test
```

The feature tests cover property approval visibility, ownership checks, API authorization, V1/V2 price formatting, caching behavior, and cursor pagination.

## Project structure

```text
app/
  Contracts/
  Enums/
  Http/
    Controllers/Api/V1/
    Controllers/Api/V2/
    Requests/
    Resources/Api/V1/
    Resources/Api/V2/
  Models/
    Scopes/
  Providers/
  Services/
database/
  factories/
  migrations/
  seeders/
routes/
  api.php
tests/
  Feature/
```

## Development notes

- Keep controllers thin and put shared property business logic in services.
- Use API Resources to control the fields returned by endpoints.
- Do not expose private owner information, internal notes, or office commission data in public responses.
- Add tests when changing authorization, pagination, caching, or response formats.
- Do not commit `.env`, credentials, tokens, or other secrets.

## License

This project is built with Laravel. Confirm the repository's intended project license before distributing it.
