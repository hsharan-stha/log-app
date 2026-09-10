<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuardianController extends Controller
{
    public function index(): View
    {
        $guardians = User::query()
            ->where('role', 'guardian')
            ->withCount('wards')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.guardians.index', compact('guardians'));
    }

    public function create(): View
    {
        return view('admin.guardians.create', $this->linkerData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $guardian = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make(config('staff.default_password')),
            'role' => 'guardian',
        ]);

        $this->syncWards($guardian, $data['student_ids'] ?? []);

        return redirect()->route('admin.guardians.index')->with('success', 'Guardian created.');
    }

    public function edit(User $guardian): View
    {
        abort_unless($guardian->isGuardian(), 404);

        return view('admin.guardians.edit', [
            'guardian' => $guardian->load('wards'),
            ...$this->linkerData(),
        ]);
    }

    public function update(Request $request, User $guardian): RedirectResponse
    {
        abort_unless($guardian->isGuardian(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($guardian->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'string', 'min:8'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $guardian->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $guardian->password = Hash::make($data['password']);
        }

        $guardian->save();
        $this->syncWards($guardian, $data['student_ids'] ?? []);

        return redirect()->route('admin.guardians.index')->with('success', 'Guardian updated.');
    }

    public function destroy(User $guardian): RedirectResponse
    {
        abort_unless($guardian->isGuardian(), 404);
        $guardian->delete();

        return redirect()->route('admin.guardians.index')->with('success', 'Guardian deleted.');
    }

    /**
     * @return array{students: \Illuminate\Database\Eloquent\Collection<int, User>, classes: \Illuminate\Database\Eloquent\Collection<int, SchoolClass>}
     */
    private function linkerData(): array
    {
        return [
            'classes' => SchoolClass::query()->orderBy('sort_order')->orderBy('name')->get(),
            'students' => User::query()
                ->where('role', 'student')
                ->with('schoolClass')
                ->orderBy('class_id')
                ->orderBy('roll_number')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * @param  list<int|string>  $studentIds
     */
    private function syncWards(User $guardian, array $studentIds): void
    {
        $sync = [];
        foreach ($studentIds as $sid) {
            $student = User::query()->find($sid);
            if ($student?->isStudent()) {
                $sync[$sid] = ['relationship' => 'guardian'];
            }
        }
        $guardian->wards()->sync($sync);
    }
}
