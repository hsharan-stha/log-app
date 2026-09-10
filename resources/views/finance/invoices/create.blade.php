@extends('layouts.finance')

@section('title', 'New invoice')

@section('content')
    <h1 class="h3 mb-4">New invoice</h1>
    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body">
            <form method="POST" action="{{ route('finance.invoices.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="student_id">Student</label>
                    <select id="student_id" name="student_id" class="form-select" required>
                        <option value="">Select…</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                {{ $student->name }} @if($student->schoolClass) ({{ $student->schoolClass->name }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="fee_type_id">Fee type (optional)</label>
                    <select id="fee_type_id" name="fee_type_id" class="form-select">
                        <option value="">Custom</option>
                        @foreach($feeTypes as $feeType)
                            <option
                                value="{{ $feeType->id }}"
                                data-amount="{{ $feeType->default_amount }}"
                                data-title="{{ $feeType->name }}"
                                @selected(old('fee_type_id') == $feeType->id)
                            >{{ $feeType->name }} — {{ format_money($feeType->default_amount, $feeType->currency) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="title">Title</label>
                    <input id="title" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="amount">Amount (JPY)</label>
                        <input id="amount" name="amount" type="number" min="0" class="form-control" value="{{ old('amount', 0) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="currency">Currency</label>
                        <input id="currency" name="currency" class="form-control" value="{{ old('currency', 'JPY') }}" required maxlength="3">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="issue_date">Issue date</label>
                        <input id="issue_date" name="issue_date" type="date" class="form-control" value="{{ old('issue_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="due_date">Due date</label>
                        <input id="due_date" name="due_date" type="date" class="form-control" value="{{ old('due_date', now()->addDays(14)->toDateString()) }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="issued" @selected(old('status', 'issued') === 'issued')>Issued (send to family)</option>
                        <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
                <button class="btn btn-primary" type="submit">Create invoice</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('fee_type_id')?.addEventListener('change', (e) => {
        const opt = e.target.selectedOptions[0];
        if (!opt || !opt.value) return;
        const title = document.getElementById('title');
        const amount = document.getElementById('amount');
        if (title && !title.value) title.value = opt.dataset.title || '';
        if (amount) amount.value = opt.dataset.amount || amount.value;
    });
</script>
@endpush
