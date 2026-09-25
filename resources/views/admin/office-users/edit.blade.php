@extends('layouts.admin')

@section('title', 'Edit staff user')

@section('content')
    <a href="{{ route('admin.office-users.index') }}" class="people-back">← Staff users</a>
    <div class="people-head">
        <div>
            <h1>Edit {{ $officeUser->name }}</h1>
            <p>Leave the password blank to keep the current one.</p>
        </div>
    </div>

    <div class="card people-form-card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.office-users.update', $officeUser) }}">
                @csrf
                @method('PUT')
                @include('admin.office-users._form', ['officeUser' => $officeUser])
                <div class="people-form__footer">
                    <button class="btn btn-primary" type="submit">Save changes</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.office-users.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
