<?php

namespace App\Http\Controllers\StaffPortal;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return view('office.dashboard', [
            'user' => $user,
            'roleLabel' => match ($user->role) {
                'staff' => 'Staff',
                'other' => 'Other staff',
                'hr' => 'HR',
                default => ucfirst((string) $user->role),
            },
        ]);
    }
}
