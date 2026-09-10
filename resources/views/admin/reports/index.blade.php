@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Reports</h1>
        <p class="text-muted mb-0">Billing and attendance summaries for the school office.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Billing report</h2>
                    <p class="small text-muted flex-grow-1">Invoices issued, payments collected, and outstanding balances by month.</p>
                    <a href="{{ route('admin.reports.billing') }}" class="btn btn-primary">Open billing report</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Student attendance</h2>
                    <p class="small text-muted flex-grow-1">Monthly weekday attendance for students, filterable by class.</p>
                    <a href="{{ route('admin.reports.attendance.students') }}" class="btn btn-primary">Open student report</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Teacher attendance</h2>
                    <p class="small text-muted flex-grow-1">Monthly weekday attendance summary for teachers.</p>
                    <a href="{{ route('admin.reports.attendance.teachers') }}" class="btn btn-primary">Open teacher report</a>
                </div>
            </div>
        </div>
    </div>
@endsection
