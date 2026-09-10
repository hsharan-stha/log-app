@extends('layouts.portal')

@section('title', 'Billing')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Billing</h1>
        <p class="text-muted mb-0">
            @if($context === 'guardian')
                Invoices for your children. Pay at the school office; finance will mark payments received.
            @else
                Your school fee invoices.
            @endif
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>Number</th>
                    @if($context === 'guardian')<th>Student</th>@endif
                    <th>Title</th>
                    <th>Due</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td><code>{{ $invoice->number }}</code></td>
                        @if($context === 'guardian')
                            <td>{{ $invoice->student?->name }}</td>
                        @endif
                        <td>{{ $invoice->title }}</td>
                        <td>{{ $invoice->due_date?->toFormattedDateString() }}</td>
                        <td>{{ format_money($invoice->amount, $invoice->currency) }}</td>
                        <td>{{ format_money($invoice->balanceDue(), $invoice->currency) }}</td>
                        <td>{{ $invoice->statusLabel() }}</td>
                        <td class="text-end">
                            <a href="{{ route($context.'.billing.show', $invoice) }}">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No invoices yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="mt-3">{{ $invoices->links() }}</div>
    @endif
@endsection
