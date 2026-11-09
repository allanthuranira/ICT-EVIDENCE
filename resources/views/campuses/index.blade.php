@extends('layout')
@section('content')
<h4>Campuses</h4>
<div class="card p-3 mb-3 shadow-sm">
    <form method="post" action="{{ route('campuses.store') }}" class="row g-2">
        @csrf
        <div class="col-md-5"><input name="name" class="form-control" placeholder="Campus name (e.g. Matuga)" required></div>
        <div class="col-md-3 form-check ms-2 mt-2">
            <input class="form-check-input" type="checkbox" name="is_hq" value="1">
            <label class="form-check-label">Is HQ</label></div>
        <div class="col-md-3"><button class="btn btn-dark">Add Campus</button></div>
    </form>
</div>
<table class="table table-sm bg-white shadow-sm">
    <thead><tr><th>Name</th><th>Type</th><th>Recipients</th><th>Status</th></tr></thead>
    <tbody>@foreach($campuses as $c)
        <tr>
            <td>{{ $c->name }}</td>
            <td><span class="badge {{ $c->is_hq ? 'bg-dark' : 'bg-secondary' }}">{{ $c->is_hq ? 'HQ' : 'Campus' }}</span></td>
            <td>{{ $c->recipients_count }}</td>
            <td><span class="badge {{ $c->active ? 'bg-success' : 'bg-danger' }}">{{ $c->active ? 'Active' : 'Inactive' }}</span></td>
        </tr>
    @endforeach</tbody>
</table>
@endsection
