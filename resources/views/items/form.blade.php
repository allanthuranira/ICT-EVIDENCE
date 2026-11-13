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

        <div class="col-12">
            <hr>
            <label class="form-label fw-bold">Report Template Grouping</label>
<div class="text-muted small">
    <p class="mb-2">All optional. These appear in the quarterly Word reports:</p>

    <ul class="mb-2 ms-3 ps-3">
        <li class="mb-1 ps-1">
            for <strong>PC</strong> items the "PC report section" groups rows
            (e.g. <em>A. Financial Stewardship and Fiscal Discipline</em>);
        </li>
        <li class="mb-0 ps-1">
            for <strong>Workplan</strong> items the five fields below create the heading rows
            (Strategic Issue / Strategic goal / Key Result Area / Outcomes / Strategic Objective).
        </li>
    </ul>

    <p class="mb-0">
        Leave any blank where the workplan does not define it.
    </p>
</div>
        </div>
        <div class="col-md-4"><label class="form-label">PC Report Section <span class="text-muted">(PC items)</span></label>
            <input name="section" class="form-control" value="{{ old('section', $item->section) }}" placeholder="A. Financial Stewardship and Fiscal Discipline"></div>
        <div class="col-md-4"><label class="form-label">Strategic Issue</label>
            <input name="strategic_issue" class="form-control" value="{{ old('strategic_issue', $item->strategic_issue) }}"></div>
        <div class="col-md-4"><label class="form-label">Strategic Goal</label>
            <input name="strategic_goal" class="form-control" value="{{ old('strategic_goal', $item->strategic_goal) }}"></div>
        <div class="col-md-4"><label class="form-label">Key Result Area (grouping)</label>
            <input name="kra_group" class="form-control" value="{{ old('kra_group', $item->kra_group) }}"></div>
        <div class="col-md-8"><label class="form-label">Outcomes</label>
            <input name="outcomes" class="form-control" value="{{ old('outcomes', $item->outcomes) }}"></div>
        <div class="col-md-6"><label class="form-label">Strategic Objective</label>
            <input name="strategic_objective" class="form-control" value="{{ old('strategic_objective', $item->strategic_objective) }}"></div>

        <div class="col-12">
            <hr>
            <label class="form-label fw-bold">Mapped Campuses / HQ &mdash; and the person responsible for each</label>
            <p class="text-muted small mb-2">The person selected here receives the evidence and quarterly progress report emails for this item. Leave as "Campus default" to use the campus's active representative.</p>
            @foreach($campuses as $campus)
                @php
                    $assignedId = old('recipients.'.$campus->id, $item->campuses->firstWhere('id', $campus->id)?->pivot?->recipient_id);
                @endphp
                <div class="border rounded p-2 mb-2 campus-row" data-campus="{{ $campus->id }}">
                    <div class="form-check">
                        <input class="form-check-input campus-check" type="checkbox" name="campuses[]" value="{{ $campus->id }}"
                               id="campus_{{ $campus->id }}"
                               @checked(in_array($campus->id, old('campuses', $item->campuses->pluck('id')->toArray())))>
                        <label class="form-check-label fw-semibold" for="campus_{{ $campus->id }}">{{ $campus->name }}{{ $campus->is_hq ? ' (HQ)' : '' }}</label>
                    </div>
                    <div class="ms-4 mt-1 recipient-picker" style="{{ in_array($campus->id, old('campuses', $item->campuses->pluck('id')->toArray())) ? '' : 'display:none' }}">
                        <select name="recipients[{{ $campus->id }}]" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="">— Campus default representative —</option>
                            @foreach($campus->recipients as $recipient)
                                <option value="{{ $recipient->id }}" @selected((string)$assignedId === (string)$recipient->id)>
                                    {{ $recipient->name }} ({{ $recipient->email }}){{ $recipient->role === 'ict_head' ? ' — ICT Head' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('recipients.'.$campus->id) <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-12 form-check ms-2">
            <input class="form-check-input" type="checkbox" name="active" value="1" @checked(old('active', $item->active ?? true))>
            <label class="form-check-label">Active</label>
        </div>
    </div>
    <button class="btn btn-dark mt-3">Save</button>
</form>

<script>
// Show the recipient dropdown only when the campus is ticked
document.querySelectorAll('.campus-check').forEach(function (cb) {
    cb.addEventListener('change', function () {
        const picker = cb.closest('.campus-row').querySelector('.recipient-picker');
        picker.style.display = cb.checked ? '' : 'none';
    });
});
</script>
@endsection
