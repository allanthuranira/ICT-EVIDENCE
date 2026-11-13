@extends('layout')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>PC Targets & Workplan KRAs</h4>
    <a href="{{ route('items.create') }}" class="btn btn-dark btn-sm">+ Add Item</a>
</div>
<table class="table table-sm bg-white shadow-sm">
    <thead><tr><th>Code</th><th>Type</th><th>Name</th><th>Template Grouping</th><th>Quarters</th><th>Annual Target</th><th>Campuses & Assigned To</th><th></th></tr></thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            <td>{{ $item->code }}</td>
            <td><span class="badge {{ $item->type === 'pc' ? 'bg-dark' : 'bg-secondary' }}">{{ strtoupper($item->type) }}</span></td>
            <td>{{ $item->name }}</td>
            <td class="small text-muted">
                @if($item->type === 'pc')
                    {{ $item->section ?: '—' }}
                @else
                    {{ collect([$item->strategic_issue, $item->strategic_goal, $item->kra_group])->filter()->implode(' → ') ?: '—' }}
                @endif
            </td>
            <td>@foreach([1,2,3,4] as $q) <span class="badge {{ $item->{'q'.$q} ? 'bg-success' : 'bg-light text-muted' }}">Q{{ $q }}</span> @endforeach</td>
            <td>{{ $item->annual_target ?? '-' }} {{ $item->unit_of_measure }}</td>
            <td>
                @foreach($item->campuses as $campus)
                    <div class="small">
                        <strong>{{ $campus->name }}</strong>
                        @php $assigned = $item->assignedRecipientForCampus($campus->id) ?? $campus->activeRepresentative(); @endphp
                        — {{ $assigned?->name ?? 'no representative!' }}
                        @unless($item->assignedRecipientForCampus($campus->id)) <span class="badge bg-light text-muted">default</span> @endunless
                    </div>
                @endforeach
            </td>
            <td><a href="{{ route('items.edit', $item) }}" class="btn btn-outline-dark btn-sm">Edit</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $items->links() }}
@endsection
