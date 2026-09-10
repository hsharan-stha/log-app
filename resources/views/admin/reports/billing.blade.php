@extends('layouts.admin')

@section('title', 'Billing report')

@section('content')
    @include('admin.partials.reports-flow', ['current' => 'billing'])

    <div class="mb-4">
        <h1 class="h3 mb-1">Billing report</h1>
        <p class="text-muted mb-0">Invoices and collection for {{ $monthLabel }}.</p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.billing') }}" class="row g-3 align-items-end">
                <div class="col-sm-auto">
                    <label class="form-label" for="month">Month</label>
                    <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
                </div>
                <div class="col-sm-auto">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        @foreach(['all' => 'All', 'issued' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid', 'draft' => 'Draft', 'void' => 'Void'] as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-auto">
                    <label class="form-label" for="class_id">Class</label>
                    <select id="class_id" name="class_id" class="form-select">
                        <option value="all" @selected($classId === 'all')>All classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected((string) $classId === (string) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-auto">
                    <button class="btn btn-primary" type="submit">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Invoices</div>
                <div class="fs-4 fw-semibold">{{ $summary['invoice_count'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Billed</div>
                <div class="fs-4 fw-semibold">{{ format_money($summary['billed']) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Paid on invoices</div>
                <div class="fs-4 fw-semibold text-success">{{ format_money($summary['paid']) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Outstanding</div>
                <div class="fs-4 fw-semibold text-danger">{{ format_money($summary['outstanding']) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Payments this month</div>
                <div class="fs-4 fw-semibold">{{ format_money($summary['payments_in_month']) }}</div>
                <div class="small text-muted">By payment date</div>
            </div></div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Overdue</div>
                <div class="fs-4 fw-semibold">{{ $summary['overdue'] }}</div>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>Number</th>
                    <th>Student</th>
                    <th>Title</th>
                    <th>Due</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($invoices as $invoice)
                    @php
                        $paidAmount = (int) ($invoice->paid_total ?? 0);
                        $balance = $invoice->status === 'void' ? 0 : max(0, $invoice->amount - $paidAmount);
                    @endphp
                    <tr>
                        <td><code>{{ $invoice->number }}</code></td>
                        <td>
                            <div class="fw-medium">{{ $invoice->student?->name }}</div>
                            <div class="small text-muted">
                                {{ $invoice->student?->roll_number ? $invoice->student->roll_number.' · ' : '' }}
                                {{ $invoice->student?->schoolClass?->name }}
                            </div>
                        </td>
                        <td>{{ $invoice->title }}</td>
                        <td>{{ $invoice->due_date?->toFormattedDateString() }}</td>
                        <td class="text-end">{{ format_money($invoice->amount, $invoice->currency) }}</td>
                        <td class="text-end">{{ format_money($paidAmount, $invoice->currency) }}</td>
                        <td class="text-end">{{ format_money($balance, $invoice->currency) }}</td>
                        <td>{{ $invoice->statusLabel() }}</td>
                        <td class="text-end">
                            <a href="{{ route('finance.invoices.show', $invoice) }}">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No invoices for this filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer">{{ $invoices->links() }}</div>
        @endif
    </div>
@endsection
