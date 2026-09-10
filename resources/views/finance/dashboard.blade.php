@extends('layouts.finance')

@section('title', 'Finance dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Billing dashboard</h1>
            <p class="text-muted mb-0">Issue invoices and record school fee payments.</p>
        </div>
        <a href="{{ route('finance.invoices.create') }}" class="btn btn-primary">New invoice</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Open balance</div>
                <div class="display-6 fw-semibold">{{ format_money($openBalance) }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Collected this month</div>
                <div class="display-6 fw-semibold text-success">{{ format_money($paidThisMonth) }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Overdue invoices</div>
                <div class="display-6 fw-semibold text-danger">{{ $overdueCount }}</div>
                <div class="small text-muted">{{ $studentCount }} students on roll</div>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Recent invoices</strong>
            <a href="{{ route('finance.invoices.index') }}">View all</a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                <tr><th>Number</th><th>Student</th><th>Title</th><th>Amount</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($recentInvoices as $invoice)
                    <tr>
                        <td><code>{{ $invoice->number }}</code></td>
                        <td>{{ $invoice->student?->name }}</td>
                        <td>{{ $invoice->title }}</td>
                        <td>{{ format_money($invoice->amount, $invoice->currency) }}</td>
                        <td>{{ $invoice->statusLabel() }}</td>
                        <td class="text-end"><a href="{{ route('finance.invoices.show', $invoice) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No invoices yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
