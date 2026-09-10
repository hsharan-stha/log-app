@extends('layouts.admin')

@section('title', 'Edit office user')

@section('content')
    <h1 class="h3 mb-4">Edit office user</h1>
    <div class="card border-0 shadow-sm" style="max-width: 520px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.office-users.update', $officeUser) }}">
                @csrf
                @method('PUT')
                @include('admin.office-users._form', ['officeUser' => $officeUser])
                <button class="btn btn-primary" type="submit">Update</button>
            </form>
        </div>
    </div>
@endsection
