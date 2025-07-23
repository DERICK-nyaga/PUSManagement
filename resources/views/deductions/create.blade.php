@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Deduction Transaction</h1>

    <form action="{{ route('deductions.store') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label for="employee_id">Employee</label>
            <select class="form-control @error('employee_id') is-invalid @enderror"
                    id="employee_id" name="employee_id" required>
                <option value="">Select Employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }} (ID: {{ $employee->employee_id }})
                    </option>
                @endforeach
            </select>
            @error('employee_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="type">Transaction Type</label>
            <select class="form-control" id="type" name="type" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="amount">Amount</label>
            <input type="number" step="0.01" class="form-control"
                   id="amount" name="amount" value="{{ old('amount') }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="reason">Reason</label>
            <input type="text" class="form-control"
                   id="reason" name="reason" value="{{ old('reason') }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label for="order_number">Order_number</label>
            <input type="text" class="form-control"
                   id="order_number" name="order_number" value="{{ old('order_number') }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="transaction_date">Transaction Date</label>
            <input type="date" class="form-control" id="transaction_date" name="transaction_date" required
                   value="{{ old('transaction_date', now()->format('Y-m-d')) }}">
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
