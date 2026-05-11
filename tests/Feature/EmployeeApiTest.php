<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->getJson('/api/employees');

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

        $response = $this->patchJson('/api/employees/'.$employee->id, [
            'name' => 'Tommy Smith',
            'isActive' => false,
        ]);

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
}
