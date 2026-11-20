@extends('layouts.admin')

@section('title', $deviceLogin ? 'Add device login' : 'Add staff user')

@section('content')
    <a href="{{ route($routePrefix.'.index') }}" class="people-back">← {{ $pageTitle }}</a>
    <div class="people-head">
        <div>
            <h1>{{ $deviceLogin ? 'Add device login' : 'Add staff user' }}</h1>
            <p>{{ $deviceLogin ? 'This account can sign in to the attendance kiosk. Role is attendance.' : 'The account uses the school default password until you change it.' }}</p>
        </div>
    </div>

    <div class="card people-form-card">
        <div class="card-body">
            <form method="POST" action="{{ route($routePrefix.'.store') }}">
                @csrf
                @include('admin.office-users._form')
                <div class="people-form__footer">
                    <button class="btn btn-primary" type="submit">{{ $deviceLogin ? 'Save device login' : 'Save staff user' }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route($routePrefix.'.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
