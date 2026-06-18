<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    private function apiHeaders(): array
    {
        $token = env('SECRET_TOKEN');

        return $token ? ['auth_token' => $token] : [];
    }

    public function test_it_returns_the_employee_list(): void
    {
        Employee::query()->create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        Employee::query()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/employees', $this->apiHeaders());

        $response->assertOk()->assertJson([
            'employees' => [
                [
                    'id' => 1,
                    'name' => 'John Smith',
                    'email' => 'john@example.com',
                    'isActive' => true,
                ],
                [
                    'id' => 2,
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                    'isActive' => false,
                ],
            ],
        ]);
    }

    public function test_it_updates_an_employee(): void
    {
        $employee = Employee::query()->create([
            'name' => 'Tom Smith',
            'email' => 'tom@example.com',
            'is_active' => true,
        ]);

        $response = $this->patchJson(
            '/api/employees/'.$employee->id,
            [
                'name' => 'Tommy Smith',
                'isActive' => false,
            ],
            $this->apiHeaders(),
        );

        $response->assertOk()->assertJson([
            'employee' => [
                'id' => $employee->id,
                'name' => 'Tommy Smith',
                'email' => 'tom@example.com',
                'isActive' => false,
            ],
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Tommy Smith',
            'is_active' => false,
        ]);
    }

    public function test_it_updates_an_employee_without_proper_name(): void
    {
        $employee = Employee::query()->create([
            'name' => 'Tom Smith',
            'email' => 'tom@example.com',
            'is_active' => true,
        ]);

        $response = $this->patchJson(
            '/api/employees/'.$employee->id,
            [
                'name' => '',
                'email' => 'tom@example.com',
                'isActive' => false,
            ],
            $this->apiHeaders(),
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_it_updates_an_employee_without_proper_email(): void
    {
        $employee = Employee::query()->create([
            'name' => 'Tom Smith',
            'email' => 'tom@example.com',
            'is_active' => true,
        ]);

        $response = $this->patchJson(
            '/api/employees/'.$employee->id,
            [
                'name' => 'Tommy Smith',
                'email' => 'example.com',
                'isActive' => false,
            ],
            $this->apiHeaders(),
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_it_creates_an_employee(): void
    {
        $response = $this->postJson(
            '/api/employees',
            [
                'name' => 'Mary Smith',
                'email' => 'mary@example.com',
                'isActive' => true,
            ],
            $this->apiHeaders(),
        );

        $response->assertCreated()->assertJson([
            'employee' => [
                'id' => 1,
                'name' => 'Mary Smith',
                'email' => 'mary@example.com',
                'isActive' => true,
            ],
        ]);

        $this->assertDatabaseHas('employees', [
            'name' => 'Mary Smith',
            'email' => 'mary@example.com',
            'is_active' => true,
        ]);
    }

    public function test_it_rejects_employee_creation_with_empty_name(): void
    {
        $response = $this->postJson(
            '/api/employees',
            [
                'name' => '',
                'email' => 'mary@example.com',
                'isActive' => true,
            ],
            $this->apiHeaders(),
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_it_rejects_employee_creation_with_invalid_email(): void
    {
        $response = $this->postJson(
            '/api/employees',
            [
                'name' => 'Mary Smith',
                'email' => 'mary-at-example.com',
                'isActive' => true,
            ],
            $this->apiHeaders(),
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_employee_creation_with_unexpected_email_field(): void
    {
        $response = $this->postJson(
            '/api/employees',
            [
                'name' => 'Mary Smith',
                'Email' => 'not-an-email',
                'isActive' => true,
            ],
            $this->apiHeaders(),
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['payload']);
    }

    public function test_it_rejects_employee_update_with_unexpected_email_field(): void
    {
        $employee = Employee::query()->create([
            'name' => 'Tom Smith',
            'email' => 'tom@example.com',
            'is_active' => true,
        ]);

        $response = $this->patchJson(
            '/api/employees/'.$employee->id,
            [
                'Email' => 'not-an-email',
            ],
            $this->apiHeaders(),
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['payload']);
    }

    public function test_it_deletes_an_employee(): void
    {
        $employee = Employee::query()->create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $response = $this->deleteJson('/api/employees/'.$employee->id, [], $this->apiHeaders());

        $response->assertNoContent();

        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
        ]);
    }
}
