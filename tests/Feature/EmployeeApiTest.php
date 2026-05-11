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
            'email' => 'john@ayp-group.com',
            'is_active' => true,
        ]);

        Employee::query()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@ayp-group.com',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/employees', $this->apiHeaders());

        $response->assertOk()->assertJson([
            'employees' => [
                [
                    'id' => 1,
                    'name' => 'John Smith',
                    'email' => 'john@ayp-group.com',
                    'isActive' => true,
                ],
                [
                    'id' => 2,
                    'name' => 'Jane Smith',
                    'email' => 'jane@ayp-group.com',
                    'isActive' => false,
                ],
            ],
        ]);
    }

    public function test_it_updates_an_employee(): void
    {
        $employee = Employee::query()->create([
            'name' => 'Tom Smith',
            'email' => 'tom@ayp-group.com',
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
                'email' => 'tom@ayp-group.com',
                'isActive' => false,
            ],
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Tommy Smith',
            'is_active' => false,
        ]);
    }

    public function test_it_creates_an_employee(): void
    {
        $response = $this->postJson(
            '/api/employees',
            [
                'name' => 'Mary Smith',
                'email' => 'mary@ayp-group.com',
                'isActive' => true,
            ],
            $this->apiHeaders(),
        );

        $response->assertCreated()->assertJson([
            'employee' => [
                'id' => 1,
                'name' => 'Mary Smith',
                'email' => 'mary@ayp-group.com',
                'isActive' => true,
            ],
        ]);

        $this->assertDatabaseHas('employees', [
            'name' => 'Mary Smith',
            'email' => 'mary@ayp-group.com',
            'is_active' => true,
        ]);
    }

    public function test_it_deletes_an_employee(): void
    {
        $employee = Employee::query()->create([
            'name' => 'John Smith',
            'email' => 'john@ayp-group.com',
            'is_active' => true,
        ]);

        $response = $this->deleteJson('/api/employees/'.$employee->id, [], $this->apiHeaders());

        $response->assertNoContent();

        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
        ]);
    }
}
