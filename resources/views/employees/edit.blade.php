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
