@extends('layouts.finance')

@section('title', 'Fee types')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Fee types</h1>
            <p class="text-muted mb-0">Templates for tuition and other school charges.</p>
        </div>
        <a href="{{ route('finance.fee-types.create') }}" class="btn btn-primary">Add fee type</a>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
            <tr><th>Name</th><th>Code</th><th>Default</th><th>Frequency</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($feeTypes as $feeType)
                <tr>
                    <td class="fw-medium">{{ $feeType->name }}</td>
                    <td><code>{{ $feeType->code }}</code></td>
                    <td>{{ format_money($feeType->default_amount, $feeType->currency) }}</td>
                    <td>{{ str_replace('_', ' ', $feeType->frequency) }}</td>
                    <td>
                        @if($feeType->is_active)
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end"><a href="{{ route('finance.fee-types.edit', $feeType) }}">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No fee types yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($feeTypes->hasPages())
            <div class="card-footer">{{ $feeTypes->links() }}</div>
        @endif
    </div>
@endsection
