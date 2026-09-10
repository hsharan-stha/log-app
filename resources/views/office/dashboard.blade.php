@extends('layouts.portal')

@section('title', $roleLabel.' portal')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ $roleLabel }} portal</h1>
        <p class="text-muted mb-0">Welcome, {{ $user->name }}. Use messages to reach the school office.</p>
    </div>
    <div class="card border-0 shadow-sm" style="max-width: 480px;">
        <div class="card-body">
            <p class="mb-3">Your account role is <strong>{{ $user->roleLabel() }}</strong>.</p>
            <a href="{{ route('messages.index') }}" class="btn btn-primary">Open messages</a>
        </div>
    </div>
@endsection
