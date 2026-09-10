<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $invoices = Invoice::query()
            ->with(['student.schoolClass', 'feeType'])
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('finance.invoices.index', [
            'invoices' => $invoices,
            'status' => $status !== '' ? $status : 'all',
        ]);
    }

    public function create(): View
    {
        return view('finance.invoices.create', [
            'students' => User::query()->where('role', 'student')->with('schoolClass')->orderBy('name')->get(),
            'feeTypes' => FeeType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'fee_type_id' => ['nullable', 'exists:fee_types,id'],
            'title' => ['required', 'string', 'max:160'],
            'amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'issued'])],
        ]);

        $student = User::query()->findOrFail($data['student_id']);
        abort_unless($student->isStudent(), 422, 'Invoice must belong to a student.');

        $invoice = Invoice::query()->create([
            ...$data,
            'currency' => strtoupper($data['currency']),
            'number' => Invoice::nextNumber(),
            'issued_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('finance.invoices.show', $invoice)
            ->with('success', 'Invoice '.$invoice->number.' created.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['student.schoolClass', 'feeType', 'issuer', 'payments.recorder']);

        return view('finance.invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice): View
    {
        abort_if($invoice->status === 'draft', 404);

        $invoice->load(['student.schoolClass', 'feeType', 'issuer', 'payments.recorder']);

        return view('finance.invoices.print', [
            'invoice' => $invoice,
            'schoolName' => config('app.name', 'ABIS'),
        ]);
    }

    public function void(Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->status === 'void', 422, 'Invoice already void.');
        abort_if($invoice->payments()->exists(), 422, 'Void is blocked after payments. Adjust payments first.');

        $invoice->void();

        return redirect()
            ->route('finance.invoices.show', $invoice)
            ->with('success', 'Invoice voided.');
    }

    public function storePayment(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if(in_array($invoice->status, ['void', 'draft'], true), 422, 'Cannot record payment on this invoice.');

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', Rule::in(['cash', 'bank_transfer', 'card', 'other'])],
            'reference' => ['nullable', 'string', 'max:120'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $balance = $invoice->balanceDue();
        abort_if($data['amount'] > $balance, 422, 'Payment exceeds balance due ('.format_money($balance, $invoice->currency).').');

        Payment::query()->create([
            ...$data,
            'invoice_id' => $invoice->id,
            'recorded_by' => $request->user()->id,
            'paid_at' => $data['paid_at'],
        ]);

        $invoice->refreshPaymentStatus();

        return redirect()
            ->route('finance.invoices.show', $invoice)
            ->with('success', 'Payment recorded.');
    }
}
