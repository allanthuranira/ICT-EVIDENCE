@extends('layout')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Quarterly Status Reports</h4>
</div>

<div class="card p-3 mb-3 shadow-sm bg-light">
    <div class="fw-bold mb-2">Download Quarterly Report (official Word templates, PC &amp; Workplan separate)</div>
    <form class="row g-2 align-items-end" method="get" action="{{ route('reports.export') }}">
        <div class="col-auto">
            <label class="form-label small mb-0">Quarter</label>
            <select name="quarter" class="form-select form-select-sm">
                @foreach([1,2,3,4] as $q)
                    <option value="{{ $q }}" @selected($q == $currentQuarter)>Q{{ $q }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">Campus / HQ</label>
            <select name="campus" class="form-select form-select-sm">
                <option value="all">All campuses + ICT HQ (combined)</option>
                @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">FY Start Year</label>
            <input type="number" name="fy" class="form-control form-control-sm" value="{{ $currentFy }}" style="width:100px">
        </div>
        <div class="col-auto">
            <button name="type" value="pc" class="btn btn-dark btn-sm">PC Report (.docx)</button>
        </div>
        <div class="col-auto">
            <button name="type" value="workplan" class="btn btn-outline-dark btn-sm">Workplan Report (.docx)</button>
        </div>
    </form>
    <div class="text-muted small mt-2">
        Combined downloads produce the document for final compilation / submission to M&amp;E and DFA.
        Items not yet reported show <em>Not submitted</em>.
    </div>
</div>

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
