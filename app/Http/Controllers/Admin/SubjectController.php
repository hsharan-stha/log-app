<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::query()->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:32', 'unique:subjects,code'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Subject::query()->create([
            'name' => $data['name'],
            'code' => strtolower($data['code']),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject created.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:32', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $subject->update([
            'name' => $data['name'],
            'code' => strtolower($data['code']),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted.');
    }
}
