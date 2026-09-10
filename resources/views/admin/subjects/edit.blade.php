@extends('layouts.admin')

@section('title', 'Edit subject')

@section('content')
    <h1 class="h3 mb-4">Edit {{ $subject->name }}</h1>
    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="card border-0 shadow-sm" style="max-width:520px">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $subject->name) }}" required></div>
            <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $subject->code) }}" required></div>
            <div class="mb-3"><label class="form-label">Sort order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $subject->sort_order) }}"></div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.subjects.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
