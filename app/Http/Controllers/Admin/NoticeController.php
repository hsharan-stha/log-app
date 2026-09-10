<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        $notices = Notice::query()
            ->with(['author', 'schoolClass'])
            ->latest()
            ->paginate(20);

        return view('admin.notices.index', compact('notices'));
    }

    public function create(): View
    {
        return view('admin.notices.create', [
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'in:all,class,teachers,students,guardians'],
            'class_id' => ['nullable', 'required_if:audience,class', 'exists:school_classes,id'],
            'publish_now' => ['nullable', 'boolean'],
        ]);

        Notice::query()->create([
            'author_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => $data['audience'],
            'class_id' => $data['audience'] === 'class' ? ($data['class_id'] ?? null) : null,
            'published_at' => $request->boolean('publish_now') ? now() : null,
        ]);

        return redirect()->route('admin.notices.index')->with('success', 'Notice saved.');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return redirect()->route('admin.notices.index')->with('success', 'Notice deleted.');
    }
}
