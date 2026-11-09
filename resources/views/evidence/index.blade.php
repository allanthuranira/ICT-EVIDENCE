@extends('layout')
@section('content')
<h4>Submitted Evidence</h4>
<table class="table table-sm bg-white shadow-sm">
    <thead><tr><th>Item</th><th>Campus</th><th>Q</th><th>File</th><th>Drive</th><th>Submitted By</th><th>Date</th><th></th></tr></thead>
    <tbody>@foreach($submissions as $s)
        <tr>
            <td>{{ $s->item->code }}</td><td>{{ $s->campus->name }}</td><td>Q{{ $s->quarter }}</td>
            <td>{{ $s->original_filename }}</td>
            <td>@if($s->drive_link)<a href="{{ $s->drive_link }}" target="_blank">Drive</a>@endif</td>
            <td>{{ $s->submitted_by_email }}</td><td>{{ $s->submitted_at->format('d M Y H:i') }}</td>
            <td><a class="btn btn-outline-dark btn-sm" href="{{ route('evidence.show', $s) }}">Download</a></td>
        </tr>
    @endforeach</tbody>
</table>
{{ $submissions->links() }}
@endsection
