@extends('layouts.admin')

@section('title', 'Add subject')

@section('content')
    <h1 class="h3 mb-4">Add subject</h1>
    <form method="POST" action="{{ route('admin.subjects.store') }}" class="card border-0 shadow-sm" style="max-width:520px">
        @csrf
        <div class="card-body">
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
            <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code') }}" placeholder="english" required></div>
            <div class="mb-3"><label class="form-label">Sort order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}"></div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.subjects.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
