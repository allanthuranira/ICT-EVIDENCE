@extends('layout')
@section('content')
<h4>{{ $item->exists ? 'Edit' : 'Add' }} PC Target / Workplan KRA</h4>
<form method="post" action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}" class="card p-4 shadow-sm">
    @csrf @if($item->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="pc" @selected($item->type === 'pc')>Performance Contract Target</option>
                <option value="workplan" @selected($item->type === 'workplan')>Workplan KRA</option>
            </select></div>
        <div class="col-md-3"><label class="form-label">Code</label>
            <input name="code" class="form-control" value="{{ old('code', $item->code) }}" placeholder="B1.1 or KRA 1.1" required></div>
        <div class="col-md-3"><label class="form-label">FY Start Year</label>
            <input name="fy_start_year" type="number" class="form-control" value="{{ old('fy_start_year', $item->fy_start_year ?? 2026) }}" required></div>
        <div class="col-md-3"><label class="form-label">Unit of Measure</label>
            <input name="unit_of_measure" class="form-control" value="{{ old('unit_of_measure', $item->unit_of_measure) }}"></div>
        <div class="col-12"><label class="form-label">Name / Title</label>
            <input name="name" class="form-control" value="{{ old('name', $item->name) }}" required></div>
        <div class="col-12"><label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $item->description) }}</textarea></div>
        <div class="col-md-3"><label class="form-label">Annual Target</label>
            <input name="annual_target" type="number" step="0.01" class="form-control" value="{{ old('annual_target', $item->annual_target) }}"></div>
        <div class="col-md-9"><label class="form-label">Reporting Quarters</label><br>
            @foreach([1,2,3,4] as $q)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="q{{ $q }}" value="1" @checked(old('q'.$q, $item->{'q'.$q}))>
                    <label class="form-check-label">Q{{ $q }}</label>
                </div>
            @endforeach</div>
        <div class="col-12"><label class="form-label">Mapped Campuses / HQ</label><br>
            @foreach($campuses as $campus)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="campuses[]" value="{{ $campus->id }}"
                           @checked(in_array($campus->id, old('campuses', $item->campuses->pluck('id')->toArray())))>
                    <label class="form-check-label">{{ $campus->name }}</label>
                </div>
            @endforeach</div>
        <div class="col-12 form-check ms-2">
            <input class="form-check-input" type="checkbox" name="active" value="1" @checked(old('active', $item->active ?? true))>
            <label class="form-check-label">Active</label>
        </div>
    </div>
    <button class="btn btn-dark mt-3">Save</button>
</form>
@endsection
