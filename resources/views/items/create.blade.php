{{-- resources/views/items/create.blade.php --}}
@extends('layout')
@section('content')
    @include('items.form', [
        'item' => new \App\Models\PerformanceItem(),
        'campuses' => $campuses,
    ])
@endsection