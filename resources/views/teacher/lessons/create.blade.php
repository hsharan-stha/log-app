@extends('layouts.portal')

@section('title', 'New lesson')

@section('content')
    <a href="{{ route('teacher.courses.show', $course) }}" class="small text-decoration-none">← {{ $course->displayName() }}</a>
    <div class="mt-2 mb-4">
        <h1 class="h3 mb-0">New lesson</h1>
        <p class="text-muted mb-0">{{ $course->displayName() }} — add photos and a short story for today.</p>
    </div>

    <form method="POST" action="{{ route('teacher.lessons.store', $course) }}" enctype="multipart/form-data">
        @csrf
        @if($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Could not save. Please check:</div>
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @include('teacher.lessons._form', ['course' => $course])
        <button class="btn btn-primary btn-lg px-4" type="submit">Save lesson</button>
        <a href="{{ route('teacher.courses.show', $course) }}" class="btn btn-link">Cancel</a>
    </form>
@endsection
