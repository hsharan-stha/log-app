<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfficeUserController extends Controller
{
    /** @var list<string> */
    public const ROLES = ['hr', 'finance', 'staff', 'other', 'attendance'];

    public function index(): View
    {
        $users = User::query()
            ->whereIn('role', self::ROLES)
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.office-users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.office-users.create', ['roles' => self::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        User::query()->create([
            ...$data,
            'password' => Hash::make(config('staff.default_password')),
        ]);

        return redirect()->route('admin.office-users.index')->with('success', 'Office user created.');
    }

    public function edit(User $officeUser): View
    {
        abort_unless(in_array($officeUser->role, self::ROLES, true), 404);

        return view('admin.office-users.edit', [
            'officeUser' => $officeUser,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $officeUser): RedirectResponse
    {
        abort_unless(in_array($officeUser->role, self::ROLES, true), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($officeUser->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $officeUser->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
        ]);

        if (! empty($data['password'])) {
            $officeUser->password = Hash::make($data['password']);
        }

        $officeUser->save();

        return redirect()->route('admin.office-users.index')->with('success', 'Office user updated.');
    }
}
