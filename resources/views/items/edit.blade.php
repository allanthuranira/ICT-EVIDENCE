{{-- resources/views/items/edit.blade.php --}}
@extends('layout')
@section('content')
<div class="container py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('items.index') }}">PC Targets &amp; Workplan KRAs</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit {{ $item->code }}</li>
        </ol>
    </nav>

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit PC Target / Workplan KRA</h4>
            <div class="text-muted small">
                {{ $item->code }} — {{ $item->name }}
            </div>
        </div>
        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Back to list
        </a>
    </div>

    {{-- Flash --}}
    @if(session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    {{-- The shared form --}}
    @include('items.form', [
        'item' => $item,
        'campuses' => $campuses,
    ])

</div>
@endsection