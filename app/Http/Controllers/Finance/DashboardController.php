<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $openBalance = Invoice::query()
            ->whereIn('status', ['issued', 'partial'])
            ->get()
            ->sum(fn (Invoice $invoice) => $invoice->balanceDue());

        $paidThisMonth = Payment::query()
            ->where('paid_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $overdueCount = Invoice::query()
            ->whereIn('status', ['issued', 'partial'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        $recentInvoices = Invoice::query()
            ->with(['student', 'feeType'])
            ->latest()
            ->limit(8)
            ->get();

        return view('finance.dashboard', [
            'openBalance' => $openBalance,
            'paidThisMonth' => (int) $paidThisMonth,
            'overdueCount' => $overdueCount,
            'studentCount' => User::query()->where('role', 'student')->count(),
            'recentInvoices' => $recentInvoices,
        ]);
    }
}
