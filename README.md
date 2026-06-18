# Employee Management API

A Laravel REST API for managing employee data with full CRUD support.

## Endpoints

All endpoints require an `auth_token` header. Responses are JSON.

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/employees` | List employees |
| `POST` | `/api/employees` | Create an employee |
| `PATCH` | `/api/employees/{id}` | Update an employee |
| `DELETE` | `/api/employees/{id}` | Delete an employee (soft) |

### Query Parameters — GET /api/employees

| Param | Values | Default | Description |
|-------|--------|---------|-------------|
| `sort` | `id`, `name`, `email` | `id` | Sort field |
| `order` | `asc`, `desc` | `asc` | Sort direction |
| `search` | string | — | Filter by name or email (LIKE) |
| `is_active` | `true`, `false` | — | Filter by active status |
| `per_page` | integer | `15` | Page size |

### Request Body

```json
{
  "name": "John Smith",
  "email": "john@example.com",
  "isActive": true
}
```

All three fields are required on `POST`. All are optional on `PATCH`.

### Response — list

```json
{
  "employees": [
    { "id": 1, "name": "John Smith", "email": "john@example.com", "isActive": true }
  ],
  "meta": {
    "total": 1,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

### Response — single employee

```json
{
  "employee": { "id": 1, "name": "John Smith", "email": "john@example.com", "isActive": true }
}
```

### Error responses

| Status | When |
|--------|------|
| `401` | Missing or wrong `auth_token` |
| `404` | Employee not found |
| `422` | Validation failure — body contains `errors` object |

---

## Setup

### Requirements

- PHP 8.1+
- Composer
- PostgreSQL (or change `DB_CONNECTION` to `sqlite` in `.env` for local dev)

### Installation

```bash
git clone <repository-url>
cd employee-management-api
composer install
cp .env.example .env
# Edit .env — set DB_* and SECRET_TOKEN
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

The API is available at `http://localhost:8000/api/`.

### Environment variables

| Variable | Description |
|----------|-------------|
| `SECRET_TOKEN` | Token required in the `auth_token` request header |
| `DB_CONNECTION` | Database driver (`pgsql`, `sqlite`, etc.) |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database user |
| `DB_PASSWORD` | Database password |

### Running tests

```bash
php artisan test
```

---

## Code Structure

```
app/
  Http/
    Controllers/EmployeeController.php   — index, store, update, destroy
    Middleware/EnsureTokenIsValid.php     — token auth
    Requests/
      StoreEmployeeRequest.php           — create validation + field mapping
      UpdateEmployeeRequest.php          — update validation + field mapping
  Models/Employee.php                    — fillable, casts, SoftDeletes

database/
  factories/EmployeeFactory.php          — test data factory
  migrations/                            — schema + indexes + soft deletes
  seeders/EmployeeSeeder.php             — sample data

routes/api.php                           — route definitions
tests/Feature/EmployeeApiTest.php        — feature tests (21 cases)
```

### Key design decisions

- **Token auth via middleware** — `EnsureTokenIsValid` uses `hash_equals()` for constant-time comparison and reads the token from `config('auth.secret_token')` so it survives `config:cache`.
- **Form requests handle field mapping** — `isActive` (camelCase from client) is mapped to `is_active` (snake_case for DB) inside `validated()`, keeping controllers clean.
- **Unknown fields rejected** — requests containing keys outside `[name, email, isActive]` receive a `422` with a `payload` error.
- **Soft deletes** — employees are not permanently removed; `deleted_at` is set instead.
- **Pagination** — index always paginates (default 15 per page) to prevent unbounded queries.

---

## AI Tool Disclosure

This codebase was developed with Claude Code (Anthropic), an AI-powered coding assistant, used for generating boilerplate, suggesting validation rules, implementing improvements, and writing tests.
