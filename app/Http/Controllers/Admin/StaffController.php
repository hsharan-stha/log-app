<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBulkStaffRequest;
use App\Http\Requests\Admin\StoreFaceDescriptorRequest;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Models\User;
use App\Support\BulkStaffParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = User::query()
            ->where('role', 'staff')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.staff.index', compact('staff'));
    }

    public function create(): View
    {
        return view('admin.staff.create');
    }

    public function bulkCreate(): View
    {
        return view('admin.staff.bulk-create');
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make(config('staff.default_password')),
            'role' => 'staff',
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member created.');
    }

    public function bulkStore(StoreBulkStaffRequest $request): RedirectResponse
    {
        $rows = BulkStaffParser::parse($request->validated('list'));
        $password = Hash::make(config('staff.default_password'));

        $count = DB::transaction(function () use ($rows, $password): int {
            $n = 0;
            foreach ($rows as $row) {
                User::query()->create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => $password,
                    'role' => 'staff',
                ]);
                $n++;
            }

            return $n;
        });

        return redirect()->route('admin.staff.index')->with('success', "Created {$count} staff member(s).");
    }

    public function edit(User $staff): View
    {
        $this->ensureStaff($staff);

        return view('admin.staff.edit', ['staff' => $staff]);
    }

    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
        $this->ensureStaff($staff);

        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $staff->update($data);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member updated.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $this->ensureStaff($staff);

        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff member deleted.');
    }

    public function registerFace(User $staff): View
    {
        $this->ensureStaff($staff);

        return view('admin.staff.register-face', ['staff' => $staff]);
    }

    public function storeFaceDescriptor(StoreFaceDescriptorRequest $request, User $staff): RedirectResponse|JsonResponse
    {
        $this->ensureStaff($staff);

        $staff->update([
            'face_descriptor' => $request->validated('descriptor'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Face registered for '.$staff->name.'.',
                'redirect' => route('admin.staff.index'),
            ]);
        }

        return redirect()->route('admin.staff.index')->with('success', 'Face registered for '.$staff->name.'.');
    }

    private function ensureStaff(User $user): void
    {
        abort_unless($user->role === 'staff', 404);
    }
}
