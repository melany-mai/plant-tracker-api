# Plant Tracker API 🌱

A RESTful JSON-LD API built with **Symfony 8** and **API Platform v4** for tracking houseplants. 

- Users manage their own plant collection
- Species are maintained by administrators

## Stack

- **Symfony 8** · **API Platform v4** · **Doctrine ORM**
- **JWT** (`lexik`) + **Refresh tokens** (`gesdinet`) · **Rate limiting** (Symfony RateLimiter)
- **PHPStan** (max) · **PHP-CS-Fixer** · **GrumPHP** · **PHPUnit 13**
- **CI**: GitHub Actions (Docker Compose)

## Getting Started

```bash
git clone <repo-url> && cd plant-tracker-api
make start
make migrate
make fixtures
docker compose exec php bin/console lexik:jwt:generate-keypair
```

> The keypair command generates `config/jwt/private.pem` and `public.pem`, and writes the paths + passphrase into `.env.local` automatically. No manual `.env` editing needed.

API: `http://localhost` — Swagger UI: `http://localhost/api`

| Command | Description |
|---|---|
| `make start` | Start Docker containers |
| `make stop` | Stop containers |
| `make migrate` | Run migrations |
| `make fixtures` | Load seed data |
| `make run-tests` | Run PHPUnit |
| `make phpstan` | Static analysis |
| `make cs-fix` | Auto-format code |

## API

All endpoints use `application/ld+json`. Authenticate via `POST /auth`, then pass `Authorization: Bearer <token>`.

| Endpoint | Method | Access |
|---|---|---|
| `/api/register` | `POST` | Public |
| `/auth` | `POST` | Public |
| `/api/token/refresh` | `POST` | Public |
| `/api/logout` | `POST` | Authenticated |
| `/api/plants` | `GET` `POST` | Authenticated (own plants only) |
| `/api/plants/{id}` | `GET` `PUT` `DELETE` | Authenticated (own plants only) |
| `/api/species` | `GET` | Authenticated |
| `/api/species` `/{id}` | `POST` `PUT` `DELETE` | Admin |
| `/api/users` | `GET` `DELETE` | Admin |

## Notable Design Choices

- **DTOs** — separate Input/Output classes keep the API contract decoupled from the database schema.
- **Custom State Providers** — return typed DTOs with server-side pagination (10 items/page).
- **Double ownership enforcement** — plants are filtered by owner both at the SQL level (`PlantOwnerExtension`) and in `PlantProvider`.
- **Rate limiting** — brute-force protection on `POST /auth` and `POST /api/register`.

## Tests

```bash
make run-tests
```

Functional tests cover: 401/403 access control, ownership isolation, response structure, and input validation (400/422). JWT tokens are generated directly via the service manager to bypass the rate limiter.

## Fixture Accounts

| Email | Password | Role |
|---|---|---|
| `admin@plant.dev` | `password` | Admin |
| `user1@plant.dev` | `password` | User |
| `user2@plant.dev` | `password` | User |
