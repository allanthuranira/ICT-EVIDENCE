@extends('layout')
@section('content')
<h4>Quarterly Status Reports</h4>
<table class="table table-sm bg-white shadow-sm">
    <thead><tr><th>Item</th><th>Campus</th><th>Q</th><th>Status</th><th>Q Actual</th><th>Submitted By</th><th>Date</th></tr></thead>
    <tbody>@foreach($reports as $r)
        <tr>
            <td>{{ $r->item->code }}</td><td>{{ $r->campus->name }}</td><td>Q{{ $r->quarter }}</td>
            <td><span class="badge bg-info text-dark">{{ str_replace('_', ' ', ucfirst($r->status)) }}</span></td>
            <td>{{ $r->quarter_actual ?? '-' }}</td>
            <td>{{ $r->submitted_by_email }}</td><td>{{ $r->submitted_at->format('d M Y H:i') }}</td>
        </tr>
    @endforeach</tbody>
</table>
{{ $reports->links() }}
@endsection
