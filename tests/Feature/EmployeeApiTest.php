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
        $john = Employee::factory()->active()->create([
            'name'  => 'John Smith',
            'email' => 'john@example.com',
        ]);

        $jane = Employee::factory()->inactive()->create([
            'name'  => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $response = $this->getJson('/api/employees', $this->apiHeaders());

        $response->assertOk()->assertJson([
            'employees' => [
                [
                    'id'       => $john->id,
                    'name'     => 'John Smith',
                    'email'    => 'john@example.com',
                    'isActive' => true,
                ],
                [
                    'id'       => $jane->id,
                    'name'     => 'Jane Smith',
                    'email'    => 'jane@example.com',
                    'isActive' => false,
                ],
            ],
        ]);
    }

    public function test_it_updates_an_employee(): void
    {
        $employee = Employee::factory()->active()->create([
            'name'  => 'Tom Smith',
            'email' => 'tom@example.com',
        ]);

        $response = $this->patchJson(
            '/api/employees/'.$employee->id,
            [
                'name'     => 'Tommy Smith',
                'isActive' => false,
            ],
            $this->apiHeaders(),
        );

        $response->assertOk()->assertJson([
            'employee' => [
                'id'       => $employee->id,
                'name'     => 'Tommy Smith',
                'email'    => 'tom@example.com',
                'isActive' => false,
            ],
        ]);

        $this->assertDatabaseHas('employees', [
            'id'        => $employee->id,
            'name'      => 'Tommy Smith',
            'is_active' => false,
        ]);
    }

    public function test_it_updates_an_employee_without_proper_name(): void
    {
        $employee = Employee::factory()->active()->create([
            'name'  => 'Tom Smith',
            'email' => 'tom@example.com',
        ]);

        $response = $this->patchJson(
            '/api/employees/'.$employee->id,
            [
                'name'     => '',
                'email'    => 'tom@example.com',
                'isActive' => false,
            ],
            $this->apiHeaders(),
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_it_updates_an_employee_without_proper_email(): void
    {
        $employee = Employee::factory()->active()->create([
            'name'  => 'Tom Smith',
            'email' => 'tom@example.com',
        ]);

        $response = $this->patchJson(
            '/api/employees/'.$employee->id,
            [
                'name'     => 'Tommy Smith',
                'email'    => 'example.com',
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
                'name'     => 'Mary Smith',
                'email'    => 'mary@example.com',
                'isActive' => true,
            ],
            $this->apiHeaders(),
        );

        $response->assertCreated()->assertJson([
            'employee' => [
                'name'     => 'Mary Smith',
                'email'    => 'mary@example.com',
                'isActive' => true,
            ],
        ]);

        $this->assertDatabaseHas('employees', [
            'name'      => 'Mary Smith',
            'email'     => 'mary@example.com',
            'is_active' => true,
        ]);
    }

    public function test_it_rejects_employee_creation_with_empty_name(): void
    {
        $response = $this->postJson(
            '/api/employees',
            [
                'name'     => '',
                'email'    => 'mary@example.com',
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
                'name'     => 'Mary Smith',
                'email'    => 'mary-at-example.com',
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
                'name'     => 'Mary Smith',
                'Email'    => 'not-an-email',
                'isActive' => true,
            ],
            $this->apiHeaders(),
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['payload']);
    }

    public function test_it_rejects_employee_update_with_unexpected_email_field(): void
    {
        $employee = Employee::factory()->active()->create([
            'name'  => 'Tom Smith',
            'email' => 'tom@example.com',
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

    public function test_it_rejects_duplicate_email_on_create(): void
    {
        Employee::factory()->create(['email' => 'john@example.com']);

        $this->postJson(
            '/api/employees',
            ['name' => 'Other', 'email' => 'john@example.com', 'isActive' => true],
            $this->apiHeaders(),
        )->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_duplicate_email_on_update(): void
    {
        Employee::factory()->create(['email' => 'john@example.com']);
        $employee2 = Employee::factory()->create(['email' => 'jane@example.com']);

        $this->patchJson(
            '/api/employees/'.$employee2->id,
            ['email' => 'john@example.com'],
            $this->apiHeaders(),
        )->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_it_sorts_employees_by_name_ascending(): void
    {
        Employee::factory()->create(['name' => 'Charlie', 'email' => 'c@example.com']);
        Employee::factory()->create(['name' => 'Alice',   'email' => 'a@example.com']);
        Employee::factory()->create(['name' => 'Bob',     'email' => 'b@example.com']);

        $response = $this->getJson('/api/employees?sort=name&order=asc', $this->apiHeaders());

        $names = collect($response->json('employees'))->pluck('name')->all();
        $this->assertSame(['Alice', 'Bob', 'Charlie'], $names);
    }

    public function test_it_sorts_employees_by_name_descending(): void
    {
        Employee::factory()->create(['name' => 'Charlie', 'email' => 'c@example.com']);
        Employee::factory()->create(['name' => 'Alice',   'email' => 'a@example.com']);

        $response = $this->getJson('/api/employees?sort=name&order=desc', $this->apiHeaders());

        $names = collect($response->json('employees'))->pluck('name')->all();
        $this->assertSame(['Charlie', 'Alice'], $names);
    }

    public function test_it_falls_back_to_id_sort_for_invalid_sort_field(): void
    {
        Employee::factory()->count(2)->create();

        $this->getJson('/api/employees?sort=password', $this->apiHeaders())->assertOk();
    }

    public function test_it_returns_404_for_missing_employee_on_update(): void
    {
        $this->patchJson('/api/employees/99999', ['name' => 'X'], $this->apiHeaders())
             ->assertStatus(404);
    }

    public function test_it_returns_404_for_missing_employee_on_delete(): void
    {
        $this->deleteJson('/api/employees/99999', [], $this->apiHeaders())
             ->assertStatus(404);
    }

    public function test_it_rejects_request_without_token(): void
    {
        $this->getJson('/api/employees')->assertStatus(401);
    }

    public function test_it_rejects_request_with_wrong_token(): void
    {
        $this->getJson('/api/employees', ['auth_token' => 'wrong-token'])->assertStatus(401);
    }

    public function test_it_deletes_an_employee(): void
    {
        $employee = Employee::factory()->active()->create([
            'name'  => 'John Smith',
            'email' => 'john@example.com',
        ]);

        $response = $this->deleteJson('/api/employees/'.$employee->id, [], $this->apiHeaders());

        $response->assertNoContent();

        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
    }
}
