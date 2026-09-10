<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeeTypeController extends Controller
{
    public function index(): View
    {
        $feeTypes = FeeType::query()->orderBy('name')->paginate(20);

        return view('finance.fee-types.index', compact('feeTypes'));
    }

    public function create(): View
    {
        return view('finance.fee-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        FeeType::query()->create($data);

        return redirect()->route('finance.fee-types.index')->with('success', 'Fee type created.');
    }

    public function edit(FeeType $feeType): View
    {
        return view('finance.fee-types.edit', compact('feeType'));
    }

    public function update(Request $request, FeeType $feeType): RedirectResponse
    {
        $feeType->update($this->validated($request, $feeType));

        return redirect()->route('finance.fee-types.index')->with('success', 'Fee type updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?FeeType $feeType = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:40', Rule::unique('fee_types', 'code')->ignore($feeType?->id)],
            'default_amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'frequency' => ['required', Rule::in(['one_time', 'monthly', 'term'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['currency'] = strtoupper($data['currency']);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
