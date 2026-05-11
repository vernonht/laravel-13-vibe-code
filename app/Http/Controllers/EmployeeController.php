<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function index(): JsonResponse
    {
        $employees = Employee::query()
            ->orderBy('id')
            ->get()
            ->map(fn (Employee $employee) => $this->toPayload($employee));

        return response()->json([
            'employees' => $employees,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('isActive', $validated)) {
            $validated['is_active'] = $validated['isActive'];
            unset($validated['isActive']);
        }

        $employee->update($validated);
        $employee->refresh();

        return response()->json([
            'employee' => $this->toPayload($employee),
        ]);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $employee = Employee::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['isActive'],
        ]);

        return response()->json([
            'employee' => $this->toPayload($employee),
        ], 201);
    }

    public function destroy(String $id): JsonResponse
    {
        $employee = Employee::query()->find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->deleteOrFail();

        return response()->json([], 204);
    }

    private function toPayload(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'email' => $employee->email,
            'isActive' => $employee->is_active,
        ];
    }
}
