@extends('layouts.admin')

@section('title', 'Add class')

@section('content')
    <h1 class="h3 mb-4">Add class</h1>
    <form method="POST" action="{{ route('admin.classes.store') }}" class="card border-0 shadow-sm" style="max-width:560px">
        @csrf
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Code</label>
                <input name="code" class="form-control" value="{{ old('code') }}" placeholder="nursery" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Sort order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Homeroom teacher</label>
                <select name="homeroom_teacher_id" class="form-select">
                    <option value="">—</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" @selected(old('homeroom_teacher_id') == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.classes.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
