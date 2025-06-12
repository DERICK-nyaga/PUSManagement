@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Station Details: {{ $station->name }}</h5>
                    <div>
                        <a href="{{ route('stations.edit', $station->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('stations.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Station ID:</span>
                                    <strong>PP-{{ str_pad($station->id, 3, '0', STR_PAD_LEFT) }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Location:</span>
                                    <strong>{{ $station->location }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Status:</span>
                                    <strong>
                                        <span class="badge badge-{{ $station->monthly_loss >= 0 ? 'success' : 'danger' }}">
                                            {{ $station->monthly_loss >= 0 ? 'Profitable' : 'Loss Making' }}
                                        </span>
                                    </strong>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h6>Financial Information</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Monthly Profit/Loss:</span>
                                    <strong class="{{ $station->monthly_loss >= 0 ? 'text-success' : 'text-danger' }}">
                                        KES {{ number_format($station->monthly_loss, 2) }}
                                    </strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Deductions:</span>
                                    <strong>KES {{ number_format($station->deductions, 2) }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Employees:</span>
                                    <strong>{{ $station->employees_count }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Recent Activity</h5>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Station Updated</h6>
                                    <small>3 days ago</small>
                                </div>
                                <p class="mb-1">Monthly profit/loss was adjusted</p>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">New Employee Added</h6>
                                    <small>1 week ago</small>
                                </div>
                                <p class="mb-1">Derick Nyaga joined as a clerk</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection