# Employee Management API

This is a Laravel-based backend API for managing employee data, providing RESTful endpoints for CRUD operations on employees.

## Extra Works and Optimizations

Beyond the basic requirements, the following enhancements were implemented:

- **Request Validation**: Added custom request classes (`UpdateEmployeeRequest`, `StoreEmployeeRequest`) with comprehensive validation rules, including unique email constraints and proper data type checks. This ensures data integrity and prevents invalid inputs from corrupting the database.
- **JSON Response Formatting**: Standardized API responses to match the specified JSON structure, including proper field naming (e.g., `isActive` instead of `is_active` in responses). This improves API consistency and client-side integration.
- **Database Seeding**: Included a seeder with sample employee data to facilitate testing and development. This allows immediate API testing without manual data entry.
- **Automated Testing**: Added feature tests covering all CRUD operations, ensuring reliability and preventing regressions. Tests use Laravel's built-in testing framework for database isolation and assertion accuracy.
- **Error Handling**: Implemented proper HTTP status codes and error messages (e.g., 404 for not found, 201 for created). This enhances API usability and debugging.

These additions are important for production readiness, as they provide security, maintainability, and a better developer experience.

## Setup Instructions

### System Requirements

- **PHP**: Version 8.1 or higher
- **Composer**: Latest version for dependency management
- **Node.js and npm**: For frontend assets (if applicable), but not required for API-only setup
- **Database**: PostgreSQL local (configured in `.env`)
- **Operating System**: macOS, Linux, or Windows with WSL

### Installation Steps

1. **Clone the Repository**:
   ```bash
   git clone <repository-url>
   cd employee-management-api
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   ```

3. **Environment Configuration**:
   - Copy `.env.example` to `.env`
   - Configure database settings in `.env` (e.g., DB_CONNECTION, DB_HOST, etc.)

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations and Seeders**:
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Start the Development Server**:
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api/`.

### Testing

Run the test suite to verify functionality:
```bash
php artisan test
```

## Code Structure Overview

The codebase follows Laravel's standard MVC structure with additional API-specific components:

- **Models** (`app/Models/`):
  - `Employee.php`: Represents the employee entity with fillable attributes and casting for `is_active` to boolean. Designed this way for automatic attribute handling and type safety.

- **Controllers** (`app/Http/Controllers/`):
  - `EmployeeController.php`: Handles API logic for CRUD operations. Methods include `index`, `store`, `update`, and `destroy`. Designed with separation of concerns: validation via request classes, business logic in controller, and response formatting in private methods for reusability.

- **Requests** (`app/Http/Requests/`):
  - `StoreEmployeeRequest.php` and `UpdateEmployeeRequest.php`: Custom request classes for validation. This design centralizes validation rules, making them reusable and testable.

- **Routes** (`routes/api.php`):
  - RESTful routes for employees: `GET /employees`, `POST /employees`, `PATCH /employees/{id}`, `DELETE /employees/{id}`. Grouped under API prefix for clarity.

- **Migrations** (`database/migrations/`):
  - `create_employees_table.php`: Defines the employees table schema with id, name, email, is_active, and timestamps. Designed with unique email constraint for data integrity.

- **Seeders** (`database/seeders/`):
  - `EmployeeSeeder.php`: Populates sample data. Called from `DatabaseSeeder.php` for easy setup.

- **Tests** (`tests/Feature/`):
  - `EmployeeApiTest.php`: Feature tests for API endpoints. Designed to cover happy paths and edge cases, ensuring API reliability.

This structure promotes maintainability, testability, and adherence to Laravel conventions.

## AI Tool Disclosure

This codebase was developed using GitHub Copilot (powered by Grok Code Fast 1), an AI-powered coding assistant. Copilot was used for:

- Generating boilerplate code (e.g., model classes, migrations, controllers)
- Suggesting validation rules and API response structures
- Assisting with test case implementations
- Providing code snippets for Laravel-specific patterns

