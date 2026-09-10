<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function billing(Request $request): View
    {
        $defaultMonth = Carbon::now()->format('Y-m');
        $monthInput = $request->query('month');
        $month = $monthInput
            ? $request->validate(['month' => ['date_format:Y-m']])['month']
            : $defaultMonth;

        $status = $request->string('status')->toString();
        if (! in_array($status, ['all', 'issued', 'partial', 'paid', 'void', 'draft'], true)) {
            $status = 'all';
        }

        $classId = $request->string('class_id')->toString();

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $base = Invoice::query()
            ->with(['student.schoolClass', 'feeType'])
            ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when(
                $classId !== '' && $classId !== 'all',
                fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId))
            );

        $invoicesForSummary = (clone $base)
            ->where('status', '!=', 'void')
            ->withSum('payments as paid_total', 'amount')
            ->get();

        $billed = (int) $invoicesForSummary->sum('amount');
        $paid = (int) $invoicesForSummary->sum(fn (Invoice $invoice) => (int) ($invoice->paid_total ?? 0));
        $outstanding = max(0, $billed - $paid);
        $overdue = $invoicesForSummary
            ->filter(fn (Invoice $invoice) => in_array($invoice->status, ['issued', 'partial'], true)
                && $invoice->due_date
                && $invoice->due_date->lt(now()->startOfDay()))
            ->count();

        $paymentsInMonth = (int) Payment::query()
            ->whereBetween('paid_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->sum('amount');

        $invoices = (clone $base)
            ->withSum('payments as paid_total', 'amount')
            ->latest('issue_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.billing', [
            'month' => $month,
            'monthLabel' => $start->translatedFormat('F Y'),
            'status' => $status,
            'classId' => $classId !== '' ? $classId : 'all',
            'classes' => SchoolClass::query()->orderBy('sort_order')->orderBy('name')->get(),
            'invoices' => $invoices,
            'summary' => [
                'billed' => $billed,
                'paid' => $paid,
                'outstanding' => $outstanding,
                'overdue' => $overdue,
                'payments_in_month' => $paymentsInMonth,
                'invoice_count' => $invoicesForSummary->count(),
            ],
        ]);
    }
}
