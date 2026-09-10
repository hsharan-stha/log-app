@extends('layouts.finance')

@section('title', 'Edit fee type')

@section('content')
    <h1 class="h3 mb-4">Edit fee type</h1>
    <div class="card border-0 shadow-sm" style="max-width: 560px;">
        <div class="card-body">
            <form method="POST" action="{{ route('finance.fee-types.update', $feeType) }}">
                @csrf
                @method('PUT')
                @include('finance.fee-types._form')
                <button class="btn btn-primary" type="submit">Update</button>
            </form>
        </div>
    </div>
@endsection
