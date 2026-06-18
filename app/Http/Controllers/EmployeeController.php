<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    private const SORTABLE_FIELDS = ['id', 'name', 'email'];

    public function index(Request $request): JsonResponse
    {
        $query = Employee::query();
        $sort  = in_array($request->get('sort'), self::SORTABLE_FIELDS) ? $request->get('sort') : 'id';
        $order = $request->get('order') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sort, $order);

        $employees = $query->get()
            ->map(fn (Employee $employee) => $this->toPayload($employee));

        return response()->json([
            'employees' => $employees,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $validated = $request->validated();

        $employee->update($validated);
        $employee->refresh();

        return response()->json([
            'employee' => $this->toPayload($employee),
        ]);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $employee = Employee::create($validated);

        return response()->json([
            'employee' => $this->toPayload($employee),
        ], 201);
    }

    public function destroy(Employee $employee): JsonResponse
    {
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
