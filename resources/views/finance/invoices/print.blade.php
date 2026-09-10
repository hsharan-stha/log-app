<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->number }}</title>
    <link href="{{ asset('vendor/bootstrap/5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { background: #eef1f5; color: #1a1d24; }
        .print-toolbar {
            position: sticky; top: 0; z-index: 10;
            background: #fff; border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }
        .invoice-sheet {
            max-width: 800px;
            margin: 1.5rem auto;
            background: #fff;
            padding: 2.5rem;
            box-shadow: 0 8px 28px rgba(0,0,0,.08);
        }
        .invoice-brand { font-size: 1.35rem; font-weight: 700; letter-spacing: 0.02em; }
        .invoice-meta td { padding: 0.2rem 0; }
        .invoice-table th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #5c6578; }
        .totals td { padding: 0.35rem 0; }
        .totals .grand { font-size: 1.15rem; font-weight: 700; }
        .status-pill {
            display: inline-block; padding: 0.2rem 0.65rem; border-radius: 999px;
            font-size: 0.8rem; font-weight: 600; background: #f1f3f7;
        }
        @media print {
            body { background: #fff; }
            .print-toolbar { display: none !important; }
            .invoice-sheet { margin: 0; box-shadow: none; max-width: none; padding: 0; }
        }
    </style>
</head>
<body>
<div class="print-toolbar d-flex justify-content-between align-items-center gap-2">
    <a href="{{ route('finance.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary">Back to invoice</a>
    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">Print</button>
</div>

<div class="invoice-sheet">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="invoice-brand">{{ $schoolName }}</div>
            <div class="text-muted small">School fee invoice</div>
        </div>
        <div class="text-end">
            <div class="fw-semibold">{{ $invoice->number }}</div>
            <span class="status-pill">{{ $invoice->statusLabel() }}</span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-6">
            <div class="text-muted small text-uppercase mb-1">Bill to</div>
            <div class="fw-semibold">{{ $invoice->student?->name }}</div>
            <div>{{ $invoice->student?->schoolClass?->name ?? '—' }}</div>
            @if($invoice->student?->email)
                <div class="small text-muted">{{ $invoice->student->email }}</div>
            @endif
        </div>
        <div class="col-6">
            <table class="invoice-meta ms-auto">
                <tr><td class="text-muted pe-3">Issue date</td><td class="fw-medium">{{ $invoice->issue_date?->toFormattedDateString() }}</td></tr>
                <tr><td class="text-muted pe-3">Due date</td><td class="fw-medium">{{ $invoice->due_date?->toFormattedDateString() }}</td></tr>
                @if($invoice->issuer)
                    <tr><td class="text-muted pe-3">Issued by</td><td class="fw-medium">{{ $invoice->issuer->name }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <table class="table invoice-table align-middle">
        <thead>
        <tr>
            <th>Description</th>
            <th class="text-end">Amount</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <div class="fw-medium">{{ $invoice->title }}</div>
                @if($invoice->feeType)
                    <div class="small text-muted">{{ $invoice->feeType->name }} ({{ $invoice->feeType->code }})</div>
                @endif
                @if($invoice->notes)
                    <div class="small text-muted mt-1">{{ $invoice->notes }}</div>
                @endif
            </td>
            <td class="text-end text-nowrap">{{ format_money($invoice->amount, $invoice->currency) }}</td>
        </tr>
        </tbody>
    </table>

    <div class="row justify-content-end">
        <div class="col-sm-6 col-md-5">
            <table class="totals w-100">
                <tr>
                    <td class="text-muted">Subtotal</td>
                    <td class="text-end">{{ format_money($invoice->amount, $invoice->currency) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Paid</td>
                    <td class="text-end">{{ format_money($invoice->amountPaid(), $invoice->currency) }}</td>
                </tr>
                <tr class="grand">
                    <td>Balance due</td>
                    <td class="text-end">{{ format_money($invoice->balanceDue(), $invoice->currency) }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($invoice->payments->isNotEmpty())
        <hr class="my-4">
        <div class="text-muted small text-uppercase mb-2">Payments received</div>
        <table class="table table-sm align-middle mb-0">
            <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="text-end">Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($invoice->payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at?->toFormattedDateString() }}</td>
                    <td>{{ $payment->methodLabel() }}</td>
                    <td>{{ $payment->reference ?: '—' }}</td>
                    <td class="text-end">{{ format_money($payment->amount, $invoice->currency) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <p class="small text-muted mt-4 mb-0">Please pay at the school office by the due date. Thank you.</p>
</div>

<script>
    window.addEventListener('load', () => {
        const params = new URLSearchParams(window.location.search);
        if (params.get('autoprint') === '1') {
            window.print();
        }
    });
</script>
</body>
</html>
