@php($feeType = $feeType ?? null)
<div class="mb-3">
    <label class="form-label" for="name">Name</label>
    <input id="name" name="name" class="form-control" value="{{ old('name', $feeType?->name) }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="code">Code</label>
    <input id="code" name="code" class="form-control" value="{{ old('code', $feeType?->code) }}" required>
</div>
<div class="row g-3">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="default_amount">Default amount (JPY)</label>
        <input id="default_amount" name="default_amount" type="number" min="0" class="form-control" value="{{ old('default_amount', $feeType?->default_amount ?? 0) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="currency">Currency</label>
        <input id="currency" name="currency" class="form-control" value="{{ old('currency', $feeType?->currency ?? 'JPY') }}" required maxlength="3">
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="frequency">Frequency</label>
    <select id="frequency" name="frequency" class="form-select" required>
        @foreach(['one_time' => 'One time', 'monthly' => 'Monthly', 'term' => 'Term'] as $value => $label)
            <option value="{{ $value }}" @selected(old('frequency', $feeType?->frequency ?? 'one_time') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="description">Description</label>
    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $feeType?->description) }}</textarea>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $feeType?->is_active ?? true))>
    <label class="form-check-label" for="is_active">Active</label>
</div>
