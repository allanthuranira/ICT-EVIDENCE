{{-- resources/views/items/create.blade.php --}}
@extends('layout')
@section('content')
    @include('items._form', [
        'item' => new \App\Models\PerformanceItem(),
        'campuses' => $campuses,
    ])
@endsection