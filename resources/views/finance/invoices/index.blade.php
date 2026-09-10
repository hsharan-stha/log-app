@extends('layouts.finance')

@section('title', 'Invoices')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Invoices</h1>
            <p class="text-muted mb-0">Student billing ledger.</p>
        </div>
        <a href="{{ route('finance.invoices.create') }}" class="btn btn-primary">New invoice</a>
    </div>

    <form method="GET" class="mb-3">
        <select name="status" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
            @foreach(['all' => 'All', 'issued' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid', 'draft' => 'Draft', 'void' => 'Void'] as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                <tr><th>Number</th><th>Student</th><th>Title</th><th>Due</th><th>Amount</th><th>Balance</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td><code>{{ $invoice->number }}</code></td>
                        <td>
                            <div class="fw-medium">{{ $invoice->student?->name }}</div>
                            <div class="small text-muted">{{ $invoice->student?->schoolClass?->name }}</div>
                        </td>
                        <td>{{ $invoice->title }}</td>
                        <td>{{ $invoice->due_date?->toFormattedDateString() }}</td>
                        <td>{{ format_money($invoice->amount, $invoice->currency) }}</td>
                        <td>{{ format_money($invoice->balanceDue(), $invoice->currency) }}</td>
                        <td>{{ $invoice->statusLabel() }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('finance.invoices.show', $invoice) }}">Open</a>
                            @if($invoice->status !== 'draft')
                                · <a href="{{ route('finance.invoices.print', $invoice) }}" target="_blank">Print</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No invoices found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer">{{ $invoices->links() }}</div>
        @endif
    </div>
@endsection
