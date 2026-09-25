<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaceDescriptorRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfficeUserController extends Controller
{
    /** @var list<string> */
    public const ROLES = ['hr', 'finance', 'staff', 'other', 'attendance'];

    public function index(Request $request): View
    {
        $name = trim($request->string('name')->toString());
        $email = trim($request->string('email')->toString());
        $role = $request->string('role')->toString();
        if (! in_array($role, self::ROLES, true)) {
            $role = '';
        }

        $users = User::query()
            ->whereIn('role', self::ROLES)
            ->when($name !== '', fn ($query) => $query->where('name', 'like', '%'.$name.'%'))
            ->when($email !== '', fn ($query) => $query->where('email', 'like', '%'.$email.'%'))
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.office-users.index', [
            'users' => $users,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'roles' => self::ROLES,
        ]);
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

    public function destroy(User $officeUser): RedirectResponse
    {
        abort_unless(in_array($officeUser->role, self::ROLES, true), 404);
        abort_if($officeUser->is(auth()->user()), 403);

        $officeUser->delete();

        return redirect()->route('admin.office-users.index')->with('success', 'Staff user deleted.');
    }

    public function registerFace(User $officeUser): View
    {
        $this->ensureFaceStaff($officeUser);

        return view('admin.staff.register-face', [
            'staff' => $officeUser,
            'facePostUrl' => route('admin.office-users.register-face.store', $officeUser),
            'faceRedirectUrl' => route('admin.office-users.index'),
            'faceBackUrl' => route('admin.office-users.index'),
            'faceBackLabel' => 'Back to office users',
        ]);
    }

    public function storeFaceDescriptor(StoreFaceDescriptorRequest $request, User $officeUser): RedirectResponse|JsonResponse
    {
        $this->ensureFaceStaff($officeUser);

        $officeUser->update([
            'face_descriptor' => $request->validated('descriptor'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Face registered for '.$officeUser->name.'.',
                'redirect' => route('admin.office-users.index'),
            ]);
        }

        return redirect()->route('admin.office-users.index')->with('success', 'Face registered for '.$officeUser->name.'.');
    }

    private function ensureFaceStaff(User $user): void
    {
        abort_unless(in_array($user->role, self::ROLES, true), 404);
    }
}
