@extends('layouts.admin')

@section('title', 'Add staff user')

@section('content')
    <a href="{{ route('admin.office-users.index') }}" class="people-back">← Staff users</a>
    <div class="people-head">
        <div>
            <h1>Add staff user</h1>
            <p>The account uses the school default password until you change it.</p>
        </div>
    </div>

    <div class="card people-form-card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.office-users.store') }}">
                @csrf
                @include('admin.office-users._form')
                <div class="people-form__footer">
                    <button class="btn btn-primary" type="submit">Save staff user</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.office-users.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
