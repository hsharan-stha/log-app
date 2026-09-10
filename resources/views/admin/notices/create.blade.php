@extends('layouts.admin')

@section('title', 'Post notice')

@section('content')
    <h1 class="h3 mb-4">Post notice</h1>
    <form method="POST" action="{{ route('admin.notices.store') }}" class="card border-0 shadow-sm" style="max-width:640px">
        @csrf
        <div class="card-body">
            <div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" value="{{ old('title') }}" required></div>
            <div class="mb-3"><label class="form-label">Body</label><textarea name="body" class="form-control" rows="6" required>{{ old('body') }}</textarea></div>
            <div class="mb-3">
                <label class="form-label">Audience</label>
                <select name="audience" id="audience" class="form-select" required>
                    @foreach(['all','class','teachers','students','guardians'] as $a)
                        <option value="{{ $a }}" @selected(old('audience')===$a)>{{ ucfirst($a) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3" id="class-wrap">
                <label class="form-label">Class (if audience = class)</label>
                <select name="class_id" class="form-select">
                    <option value="">—</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="publish_now" value="1" id="publish_now" checked>
                <label class="form-check-label" for="publish_now">Publish now</label>
            </div>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
@endsection
