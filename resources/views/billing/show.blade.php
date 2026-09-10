@extends('layouts.portal')

@section('title', $invoice->number)

@section('content')
    <div class="mb-4">
        <a href="{{ route($context.'.billing.index') }}" class="small">&larr; Back to billing</a>
        <h1 class="h3 mb-1 mt-2">{{ $invoice->number }}</h1>
        <p class="text-muted mb-0">{{ $invoice->title }} · {{ $invoice->statusLabel() }}</p>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            @if($context === 'guardian')
                <div class="mb-2"><span class="text-muted">Student:</span> {{ $invoice->student?->name }}</div>
            @endif
            <div class="d-flex justify-content-between"><span>Amount</span><strong>{{ format_money($invoice->amount, $invoice->currency) }}</strong></div>
            <div class="d-flex justify-content-between"><span>Paid</span><strong>{{ format_money($invoice->amountPaid(), $invoice->currency) }}</strong></div>
            <div class="d-flex justify-content-between"><span>Balance due</span><strong>{{ format_money($invoice->balanceDue(), $invoice->currency) }}</strong></div>
            <div class="small text-muted mt-2">Issued {{ $invoice->issue_date?->toFormattedDateString() }} · Due {{ $invoice->due_date?->toFormattedDateString() }}</div>
            @if($invoice->notes)
                <hr>
                <div class="small">{{ $invoice->notes }}</div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><strong>Payments received</strong></div>
        <ul class="list-group list-group-flush">
            @forelse($invoice->payments as $payment)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $payment->paid_at?->toFormattedDateString() }} · {{ $payment->methodLabel() }}</span>
                    <strong>{{ format_money($payment->amount, $invoice->currency) }}</strong>
                </li>
            @empty
                <li class="list-group-item text-muted">No payments recorded yet. Please pay at the school office.</li>
            @endforelse
        </ul>
    </div>
@endsection
