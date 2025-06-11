<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    // Display all employees
    public function index()
    {
        $employees = Employee::with('station')
            ->latest()
            ->paginate(10);

        return view('employees.index', compact('employees'));
    }

    // Show create form
    public function create()
    {
        $stations = Station::all();
        $statuses = ['active', 'on_leave', 'terminated'];
        return view('employees.create', compact('stations', 'statuses'));
    }

    // Store new employee
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'phone' => 'required|string|max:20',
            'station_id' => 'required|exists:stations,id',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => ['required', Rule::in(['active', 'on_leave', 'terminated'])]
        ]);

        Employee::create($formFields);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully!');
    }

    // Show edit form
    public function edit(Employee $employee)
    {
        $stations = Station::all();
        $statuses = ['active', 'on_leave', 'terminated'];
        return view('employees.edit', compact('employee', 'stations', 'statuses'));
    }

    // Update employee
    public function update(Request $request, Employee $employee)
    {
        $formFields = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employee->id)],
            'phone' => 'required|string|max:20',
            'station_id' => 'required|exists:stations,id',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => ['required', Rule::in(['active', 'on_leave', 'terminated'])]
        ]);

        $employee->update($formFields);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully!');
    }

    // Delete employee
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully!');
    }

    // Show employee details
    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }
}
