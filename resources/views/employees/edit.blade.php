@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Edit Employee: {{ $employee->full_name }}</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('employees.update', $employee->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
@error ('record')

@enderror                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control"
                                       id="first_name" name="first_name"
                                       value="{{ old('first_name', $employee->first_name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control"
                                       id="lasst_name" name="last_name"
                                       value="{{ old('last_name', $employee->last_name) }}" required>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Update Employee
                                </button>
                                <a href="{{ route('employees.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Employee</h1>

    <form action="{{ route('employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name"
                           value="{{ old('first_name', $employee->first_name) }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name"
                           value="{{ old('last_name', $employee->last_name) }}" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="{{ old('email', $employee->email) }}" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone"
                   value="{{ old('phone', $employee->phone) }}" required>
        </div>

        <div class="form-group">
            <label for="station_id">Station</label>
            <select class="form-control" id="station_id" name="station_id" required>
                @foreach($stations as $station)
                    <option value="{{ $station->id }}"
                        {{ $employee->station_id == $station->id ? 'selected' : '' }}>
                        {{ $station->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="position">Position</label>
            <input type="text" class="form-control" id="position" name="position"
                   value="{{ old('position', $employee->position) }}" required>
        </div>

        <div class="form-group">
            <label for="salary">Salary</label>
            <input type="number" step="0.01" class="form-control" id="salary" name="salary"
                   value="{{ old('salary', $employee->salary) }}" required>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="on_leave" {{ $employee->status == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                <option value="terminated" {{ $employee->status == 'terminated' ? 'selected' : '' }}>Terminated</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Employee</button>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection --}}
