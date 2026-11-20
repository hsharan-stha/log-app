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
    public const STAFF_ROLES = ['hr', 'finance', 'staff', 'other'];

    /** @var list<string> */
    public const ROLES = ['hr', 'finance', 'staff', 'other', 'attendance'];

    public function index(Request $request): View
    {
        $name = trim($request->string('name')->toString());
        $email = trim($request->string('email')->toString());
        $roles = $this->screenRoles();
        $role = $request->string('role')->toString();
        if (! in_array($role, $roles, true)) {
            $role = '';
        }

        $users = User::query()
            ->whereIn('role', $roles)
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
            'roles' => $roles,
            ...$this->screen(),
        ]);
    }

    public function create(): View
    {
        return view('admin.office-users.create', [
            'roles' => $this->screenRoles(),
            ...$this->screen(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->isDeviceLogin()) {
            $request->merge(['role' => 'attendance']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['required', Rule::in($this->screenRoles())],
        ]);

        User::query()->create([
            ...$data,
            'password' => Hash::make(config('staff.default_password')),
        ]);

        $message = $this->isDeviceLogin() ? 'Device login created.' : 'Staff user created.';

        return redirect()->route($this->routePrefix().'.index')->with('success', $message);
    }

    public function edit(User $officeUser): View
    {
        abort_unless(in_array($officeUser->role, $this->screenRoles(), true), 404);

        return view('admin.office-users.edit', [
            'officeUser' => $officeUser,
            'roles' => $this->screenRoles(),
            ...$this->screen(),
        ]);
    }

    public function update(Request $request, User $officeUser): RedirectResponse
    {
        abort_unless(in_array($officeUser->role, $this->screenRoles(), true), 404);

        if ($this->isDeviceLogin()) {
            $request->merge(['role' => 'attendance']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($officeUser->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['required', Rule::in($this->screenRoles())],
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

        $message = $this->isDeviceLogin() ? 'Device login updated.' : 'Staff user updated.';

        return redirect()->route($this->routePrefix().'.index')->with('success', $message);
    }

    public function destroy(User $officeUser): RedirectResponse
    {
        abort_unless(in_array($officeUser->role, $this->screenRoles(), true), 404);
        abort_if($officeUser->is(auth()->user()), 403);

        $officeUser->delete();

        $message = $this->isDeviceLogin() ? 'Device login deleted.' : 'Staff user deleted.';

        return redirect()->route($this->routePrefix().'.index')->with('success', $message);
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
        abort_unless(in_array($user->role, self::STAFF_ROLES, true), 404);
    }

    private function isDeviceLogin(): bool
    {
        return request()->routeIs('admin.device-logins.*');
    }

    /**
     * @return list<string>
     */
    private function screenRoles(): array
    {
        return $this->isDeviceLogin() ? ['attendance'] : self::STAFF_ROLES;
    }

    private function routePrefix(): string
    {
        return $this->isDeviceLogin() ? 'admin.device-logins' : 'admin.office-users';
    }

    /**
     * @return array{deviceLogin: bool, routePrefix: string, pageTitle: string, pageBlurb: string}
     */
    private function screen(): array
    {
        $deviceLogin = $this->isDeviceLogin();

        return [
            'deviceLogin' => $deviceLogin,
            'routePrefix' => $this->routePrefix(),
            'pageTitle' => $deviceLogin ? 'Device login' : 'Staff users',
            'pageBlurb' => $deviceLogin
                ? 'Kiosk sign-in accounts. New accounts are attendance logins.'
                : 'HR, finance, staff, and other non-teaching accounts.',
        ];
    }
}
