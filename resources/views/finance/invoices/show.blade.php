@extends('layouts.finance')

@section('title', $invoice->number)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $invoice->number }}</h1>
            <p class="text-muted mb-0">{{ $invoice->title }} · {{ $invoice->statusLabel() }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($invoice->status !== 'draft')
                <a href="{{ route('finance.invoices.print', $invoice) }}" class="btn btn-primary" target="_blank">Print</a>
            @endif
            <a href="{{ route('finance.invoices.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase mb-2">Student</div>
                <div class="fw-semibold">{{ $invoice->student?->name }}</div>
                <div class="small text-muted">{{ $invoice->student?->schoolClass?->name }}</div>
                <hr>
                <div class="d-flex justify-content-between"><span>Amount</span><strong>{{ format_money($invoice->amount, $invoice->currency) }}</strong></div>
                <div class="d-flex justify-content-between"><span>Paid</span><strong>{{ format_money($invoice->amountPaid(), $invoice->currency) }}</strong></div>
                <div class="d-flex justify-content-between"><span>Balance</span><strong>{{ format_money($invoice->balanceDue(), $invoice->currency) }}</strong></div>
                <div class="small text-muted mt-2">Due {{ $invoice->due_date?->toFormattedDateString() }}</div>
            </div></div>
        </div>
        <div class="col-md-6">
            @if(in_array($invoice->status, ['issued', 'partial'], true))
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <h2 class="h5">Record payment</h2>
                    <form method="POST" action="{{ route('finance.invoices.payments.store', $invoice) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="amount">Amount (JPY)</label>
                            <input id="amount" name="amount" type="number" min="1" max="{{ $invoice->balanceDue() }}" class="form-control" value="{{ old('amount', $invoice->balanceDue()) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="method">Method</label>
                            <select id="method" name="method" class="form-select" required>
                                @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'card' => 'Card', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('method', 'cash') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paid_at">Paid at</label>
                            <input id="paid_at" name="paid_at" type="datetime-local" class="form-control" value="{{ old('paid_at', now()->format('Y-m-d\\TH:i')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="reference">Reference</label>
                            <input id="reference" name="reference" class="form-control" value="{{ old('reference') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Save payment</button>
                    </form>
                </div></div>
            @elseif($invoice->status === 'draft')
                <div class="alert alert-secondary mb-0">Draft invoices are not visible to families until issued.</div>
            @elseif($invoice->status === 'paid')
                <div class="alert alert-success mb-0">This invoice is fully paid.</div>
            @else
                <div class="alert alert-secondary mb-0">This invoice is void.</div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white"><strong>Payments</strong></div>
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>When</th><th>Amount</th><th>Method</th><th>Reference</th><th>By</th></tr></thead>
            <tbody>
            @forelse($invoice->payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at?->toDayDateTimeString() }}</td>
                    <td>{{ format_money($payment->amount, $invoice->currency) }}</td>
                    <td>{{ $payment->methodLabel() }}</td>
                    <td>{{ $payment->reference ?: '—' }}</td>
                    <td>{{ $payment->recorder?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No payments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($invoice->status !== 'void' && $invoice->payments->isEmpty())
        <form method="POST" action="{{ route('finance.invoices.void', $invoice) }}" onsubmit="return confirm('Void this invoice?')">
            @csrf
            <button class="btn btn-outline-danger btn-sm" type="submit">Void invoice</button>
        </form>
    @endif
@endsection
