<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * Show the change-password form for the logged-in user.
     */
    public function edit(Request $request): View
    {
        return view('auth.change-password', [
            'user' => $request->user(),
            'layout' => $this->layoutFor($request->user()),
        ]);
    }

    /**
     * Update the logged-in user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('password.edit')
            ->with('success', 'Your password has been updated.')
            ->with('status', 'password-updated');
    }

    private function layoutFor(User $user): string
    {
        return match (true) {
            $user->isAdmin(), $user->isHr() => 'layouts.admin',
            $user->isFinance() => 'layouts.finance',
            default => 'layouts.portal',
        };
    }
}
