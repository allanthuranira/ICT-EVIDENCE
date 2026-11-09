@extends('layout')
@section('content')
<h4>Email Recipients (Campus Representatives & ICT Heads)</h4>
<div class="card p-3 mb-3 shadow-sm">
    <form method="post" action="{{ route('recipients.store') }}" class="row g-2">
        @csrf
        <div class="col-md-3"><input name="name" class="form-control" placeholder="Full name" required></div>
        <div class="col-md-3"><input name="email" type="email" class="form-control" placeholder="Email" required></div>
        <div class="col-md-2"><select name="campus_id" class="form-select">
            @foreach(App\Models\Campus::where('active', true)->get() as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select></div>
        <div class="col-md-2"><select name="role" class="form-select">
            <option value="representative">Representative (receives link)</option>
            <option value="ict_head">ICT Head (CC)</option>
        </select></div>
        <div class="col-md-2"><button class="btn btn-dark w-100">Add / Update</button></div>
    </form>
</div>
<table class="table table-sm bg-white shadow-sm">
    <thead><tr><th>Name</th><th>Email</th><th>Campus</th><th>Role</th><th>Status</th><th></th></tr></thead>
    <tbody>@foreach($recipients as $r)
        <tr>
            <td>{{ $r->name }}</td><td>{{ $r->email }}</td><td>{{ $r->campus->name }}</td>
            <td>{{ $r->role === 'ict_head' ? 'ICT Head (CC)' : 'Representative' }}</td>
            <td><span class="badge {{ $r->active ? 'bg-success' : 'bg-danger' }}">{{ $r->active ? 'Active' : 'Inactive' }}</span></td>
            <td>
                <form method="post" action="{{ route('recipients.destroy', $r) }}" class="d-inline"
                      onsubmit="return confirm('Deactivate this recipient?')">@csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">Deactivate</button>
                </form>
            </td>
        </tr>
    @endforeach</tbody>
</table>
@endsection
