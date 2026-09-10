<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalBillingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            $invoices = Invoice::query()
                ->where('student_id', $user->id)
                ->where('status', '!=', 'draft')
                ->with('feeType')
                ->latest()
                ->paginate(20);

            return view('billing.index', [
                'invoices' => $invoices,
                'context' => 'student',
            ]);
        }

        abort_unless($user->isGuardian(), 403);

        $wardIds = $user->wards()->pluck('users.id');

        $invoices = Invoice::query()
            ->whereIn('student_id', $wardIds)
            ->where('status', '!=', 'draft')
            ->with(['student', 'feeType'])
            ->latest()
            ->paginate(20);

        return view('billing.index', [
            'invoices' => $invoices,
            'context' => 'guardian',
        ]);
    }

    public function show(Request $request, Invoice $invoice): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            abort_unless($invoice->student_id === $user->id, 403);
        } elseif ($user->isGuardian()) {
            abort_unless($user->wards()->where('users.id', $invoice->student_id)->exists(), 403);
        } else {
            abort(403);
        }

        abort_if($invoice->status === 'draft', 404);

        $invoice->load(['student.schoolClass', 'feeType', 'payments']);

        return view('billing.show', [
            'invoice' => $invoice,
            'context' => $user->isStudent() ? 'student' : 'guardian',
        ]);
    }
}
