@extends('layouts.admin')

@section('title', 'Add office user')

@section('content')
    <h1 class="h3 mb-4">Add office user</h1>
    <div class="card border-0 shadow-sm" style="max-width: 520px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.office-users.store') }}">
                @csrf
                @include('admin.office-users._form')
                <button class="btn btn-primary" type="submit">Create</button>
            </form>
        </div>
    </div>
@endsection
